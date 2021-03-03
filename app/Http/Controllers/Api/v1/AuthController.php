<?php

namespace App\Http\Controllers\Api\v1;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Mail\ForgotPassword;
use Illuminate\Support\Str;
use App\UserAppToken;
use App\AppVersion;
use App\Jobs\DoctorProfileVerificationJob;
use App\Jobs\EmailVerificationJob;
use App\UserApp;
use App\User;
use App\UserDetail;
use Image;
use App\Notification;
use Exception;

class AuthController extends Controller
{
	protected $random;
	public $success_code = 200;
	public $success_name = "success";
	public $error_code = 400;
	public $error_name = "fail";
	public $num_per_page = 40;
	private $exception_message = 'Something went wrong, please try again.';

	public function __construct()
	{
		$this->random = Str::random(12);
	}

	//get OTP
	public function getOTP(Request $request)
	{
		$rules = [
			'phone' => 'required|numeric|min:10|regex:/^[0-9]{10}+$/',
			'dialcode' => 'required'
		];

		if ($request->get('referrer')) {
			$rules += [
				'referrer' => 'required|exists:users,referral_code'
			];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$input = $request->all();
				DB::beginTransaction();
				//Check if the number is registered other than a roll doctor, patient
				$user = new User();
				if ($input['phone']) {
					$user = $user->where('phone', $input['phone'])->whereHas('role', function ($q) {
						$q->whereNotIn('keyword', ['patient', 'doctor']);
					})->first();
				}
				if (!$user) {
					$randomid = mt_rand(100000, 999999);
					$input['token'] = Str::random(60);
					$app_token = UserAppToken::updateOrCreate(['phone' => $input['phone']], $input);
					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'OTP genereted.',
						'data' => [
							'dialcode' => $app_token->dialcode,
							'phone' => $app_token->phone,
							'otp' => $randomid,
							'auth_token' => $app_token->token
						]
					];
				} else {

					$result = ['status' => $this->error_name, 'code' => 201, 'message' => "This number already register as a " . $user->role->keyword . ". So the mobile app not allowed this login. Do you want to web login..",];
				}
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
			}
		}
		return Response::json($result);
	}
	//register user

	public function authentication(Request $request)
	{
		$rules = [
			'phone' => 'required|numeric|min:10|regex:/^[0-9]{10}+$/',
			'dialcode' => 'required',
			'auth_token' => 'required'
		];
		if ($request->get('referrer')) {
			$rules += [
				'referrer' => 'required|exists:users,referral_code'
			];
		}
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			$input = $request->all();
			$device = $request->get('device');
			try {
				DB::beginTransaction();
				$user_token = UserAppToken::where('phone', $input['phone'])->where('token', $input['auth_token'])->exists();
				if ($user_token) {

					if ($request->get('referrer')) {
						$referrer = User::Where('referral_code', $request->get('referrer'))->first();
						$input['referrer_id'] = $referrer ? $referrer->id : null;
					}

					$user = User::whereHas('role', function ($q) {
						$q->whereIn('keyword', ['doctor', 'patient']);
					})->where('phone', $input['phone'])->first();

					if ($user) {
						unset($input['referrer_id']);
						$user->update($input);
					} else {
						$input['referral_code'] = passwordGenerate(6);
						$user = User::create($input);
					}

					$user->is_password_set = empty($user->password) ? 'N' : 'Y';

					UserDetail::updateOrCreate(['user_id' => $user->id], $input); //create details record with user id when user create

					$device['user_id'] = $user->id;
					UserApp::updateOrCreate(['device_unique_id' => $device["device_unique_id"]], $device); //user device record

					$isForceUpdate = ["forceupdate" => "N", "updateavailable" => "N", "message" => "", "newversion" => ""];
					$isNewVersion = AppVersion::where(["type" => $device["device_type"], "status" => 'Y'])->orderBy('id', 'desc')->first();
					if (!empty($isNewVersion) && $device['app_version'] < $isNewVersion->version) {
						$isForceUpdate = ["forceupdate" => $isNewVersion->forceupdate, "updateavailable" => "Yes", "message" => "New version " . $isNewVersion->version . " is available for App.", "newversion" => $isNewVersion->version];
					}

					//multiple device login not allowed
					$tokens = $user->tokens;
					if ($tokens) {
						foreach ($tokens as $token) {
							$token->delete();
						}
					}

					unset($user['tokens']);
					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'Authentication successfully.',
						'data' => [
							'user' => $user,
							'token' => 'Bearer ' . $user->createToken($user->phone)->accessToken,
							'isForceUpdate' => $isForceUpdate
						]
					];
				} else {
					DB::commit();
					$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => 'OTP authentication fail, please try again..'];
				}
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
			}
		}
		return Response::json($result);
	}

	//change password
	public function changePassword(Request $request)
	{
		$rules = [
			'current_password' => "required|min:8",
			'new_password' => "required|min:8|regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/"
		];

		$message = ['new_password.regex' => 'Password must contain minimum 8 characters with at least 1 lowercase, 1 uppercase, 1 number and 1 special character.'];

		$validator = Validator::make($request->all(), $rules, $message);

		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$user = Auth::user();
				if (Hash::check($request->get("current_password"), $user->password)) {
					$user->password = Hash::make($request->get('new_password'));
					$user->save();
					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						"message" => "Password update successfully."
					];
				} else {
					DB::rollBack();
					$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => "Current password is invalid."];
				}
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
			}
		}
		return Response::json($result);
	}

	//set password
	public function setPassword(Request $request)
	{
		$input = $request->all();
		$rules = [
			'new_password' => "required|same:confirm_password|min:8|regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/",
		];
		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$userObj = Auth::user();
				if (!empty($userObj)) {
					$userObj->password = Hash::make($request->input('new_password'));
					$userObj->save();
					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						"message" => "Password set successfully."
					];
				} else {
					DB::rollBack();
					$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => "User doesn't exist."];
				}
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
			}
		}
		return Response::json($result);
	}

	//User profile update
	public function updateProfile(Request $request)
	{
		$input = $request->all();
		$rules = [
			'name' => "required",
			'email' => "email",
			'dob' => "date",
			'gender' => "in:Male,Female,Other|string",
			'pincode' => "required|integer",
			'profile_picture' => "mimes:jpeg,jpg,png|max:5000",
			'address' => 'required',
			'locality' => 'required',
			'city' => 'required',
			'state' => 'required',
			'country' => 'required',
		];
		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$user = Auth::user();
				if ($request->hasFile('profile_picture')) {
					$avatar = $request->file('profile_picture');
					if ($avatar->getClientOriginalExtension() == 'jfif') {
						$filename = time() . $this->random . '.jpg';
						Image::make($avatar)->encode('jpg', 75)->resize(500, 500)->save(storage_path('app/user/' . $filename));
						$input['profile_picture'] = $filename;
					} else {
						$filename = time() . $this->random . '.' . $avatar->getClientOriginalExtension();
						Image::make($avatar)->fit(500, 500, function ($constraint) {
							$constraint->upsize();
						})->save(storage_path('app/user/' . $filename));
						$input['profile_picture'] = $filename;
					}
					/*remove the existing profile picture*/
					$image_path = storage_path('app/user/' . $user->image_name);
					if ($user->image_name != "default.png") {
						@unlink($image_path);
					}
				}
				unset($input['email']);
				$user->update($input);
				$data['user'] = $user;
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => "Profile updated successfully.",
					'data' => $data

				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
			}
		}
		return Response::json($result);
	}

	//doctor profile register/update
	public function profiles(Request $request)
	{
		$input = $request->all();
		$rules = [
			'name' => "required",
			'detail.specialty_ids' => 'required',
			'detail.registration_number' => "required",
			'detail.registration_year' => "required|integer",
			'detail.liecence_number' => "required",
			'detail.degree' => "required",
			'detail.collage_or_institute' => "required",
			'detail.year_of_completion' => "required|digits:4|integer|min:1900",
			'detail.experience' => "required",
			'identity_document' => "required|mimes:jpeg,jpg,png|max:5000",
			'medical_document' => "required|mimes:jpeg,jpg,png|max:5000",
		];
		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$user = Auth::user();

				if ($request->get('detail')) {
					$detail = $request->get('detail');

					if ($request->has('identity_document') && !empty($request->file('identity_document'))) {

						$identity_document = $request->file('identity_document');
						$filename = time() . uniqId() . '.' . $identity_document->getClientOriginalExtension();
						Image::make($identity_document)->fit(500, 500, function ($constraint) {
							$constraint->upsize();
						})->save(storage_path('app/document/' . $filename));
						$detail['identity_proof'] = $filename;

						/*remove the existing profile picture*/
						$identity_path = storage_path('app/document/' . $user->detail->identity_proof_name);
						if ($user->detail->identity_proof_name != "no_image.png") {
							@unlink($identity_path);
						}
					}

					if ($request->has('medical_document') && !empty($request->file('medical_document'))) {
						$medical_proof_document = $request->file('medical_document');
						$medical_filename = time() . uniqId() . '.' . $medical_proof_document->getClientOriginalExtension();
						Image::make($medical_proof_document)->fit(500, 500, function ($constraint) {
							$constraint->upsize();
						})->save(storage_path('app/document/' . $medical_filename));
						$detail['medical_registration_proof'] = $medical_filename;

						/*remove the existing profile picture*/
						$medical_proof_path = storage_path('app/document/' . $user->detail->medical_registration_proof_name);
						if ($user->detail->medical_registration_proof_name != "no_image.png") {
							@unlink($medical_proof_path);
						}
					}

					UserDetail::where(['user_id' => $user->id])->update($detail);
				}
				$user->update($input);
				$user->update(['as_doctor_verified' => '1']);

				//get super admin id
				$is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
					$q->where('keyword', 'admin');
				})->first();

				$data = [
					'sender_id' => Auth::id(),
					'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
					'title' => 'User Request For Doctor Profile Approval',
					'type' => 'doctor_profile_verification',
					'message' => Auth::user()->name . ' has requested for approval profile as a doctor. please verify the details.',
				];


				Notification::create($data);

				if ($is_admin->email) {
					$mailInfo = ([
						'receiver_email' => $is_admin->email,
						'receiver_name' => 'NC Health HUB',
						'title' => '',
						'subject' => 'Apply Profile As Doctor ' . $user->name,
						'content' => 'I would like to request approval for my profile as a doctor. I have uploaded all the details and documents.<br>
						Please click on the button to see all details.<br>
						Please, let me know if any concerns about the same.<br>
						<br>
						<br>
						Thanks.',

					]);
					dispatch(new DoctorProfileVerificationJob($mailInfo)); //add mail to queue
				}

				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => "Profile updated successfully."

				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
			}
		}
		return Response::json($result);
	}

	//User profile update
	public function updateLocation(Request $request)
	{
		$input = $request->all();
		try {
			DB::beginTransaction();
			$user = Auth::user();
			$user->update($input);
			$data['user'] = $user;
			DB::commit();
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => "User data updated successfully.",
				'data' => $data

			];
		} catch (Exception $e) {
			DB::rollBack();
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
		}
		return Response::json($result);
	}

	public function logout(Request $request)
	{
		$token = $request->user()->token();
		$token->revoke();

		UserApp::where('user_id', Auth::id())->delete();
		$result = [
			'status' => $this->success_name,
			'code' => $this->success_code,
			'message' => "You have been successfully logged out"
		];

		return Response::json($result);
	}

	//Email verify
	public function verifyEmail(Request $request)
	{
		$input = $request->all();
		$rules = [
			'type' => "required|in:getOTP,updateEmail",
			'email' => "required|email|unique:users",
		];
		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$user = Auth::user();

				if ($request->get('type') === 'getOTP') {
					$randomid = mt_rand(100000, 999999);

					if ($request->get('email')) {
						$mailInfo = ([
							'receiver_email' => $request->get('email'),
							'receiver_name' => $user->name,
							'title' => 'Email Verification Code',
							'subject' => 'Email Verification From NC Health Hub',
							'content' => 'You registered an account on NC Health Hub, before being able to use your email you need to verify that this is your email verification code.<br>
							<strong><h2>Verification Code : ' . $randomid . '</h2></strong><br>
							<br>
							Thanks',

						]);
						dispatch(new EmailVerificationJob($mailInfo)); //add mail to queue
					}
					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'A verification code has been send to your email address. Please check',
						'data' => [
							'email' => $request->get('email'),
							'otp' => $randomid,
						]
					];
				}

				if ($request->get('type') === 'updateEmail') {
					$user->update(['email' => $request->get('email')]);
					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'Email verification Successfully.',
					];
				}
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
			}
		}
		return Response::json($result);
	}

	public function deleteAccount()
	{
		try {
			DB::beginTransaction();
			User::where('user_id', [Auth::id()])->delete();
			DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'Account Deleted Successfully.',
					];
		} catch (Exception $e) {
			DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message,];
		}
		return Response::json($result);
	}
}
