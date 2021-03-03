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
use Image;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Edujugon\PushNotification\PushNotification;
use App\Mail\StaffApprovalMail;
use App\UserRole;
use App\Specialty;
use App\User;
use App\Offer;
use App\HealthFeed;
use App\Card;
use App\Wishlist;
use App\StaffManager;
use App\UserDetail;
use App\HealthFeedCategory;
use App\Setting;
use App\Drug;
use App\Feedback;
use App\Rating;
use App\PracticeManager;
use App\Country;
use App\Appointment;
use App\Payment;
use App\Notification;
use App\MedicalRecord;
use App\MedicalRecordFile;
use App\AppointmentPrescription;
use App\AppointmentFile;
use App\UserApp;
use DateTimeZone;
use Timezone;
use App\Chat;
use App\DiagnosticsService;
use App\Invoice;
use App\SharePrescription;
use App\Jobs\BookAppointmentJob;
use App\Jobs\CancelAppointmentJob;
use App\Jobs\DoctorProfileVerificationJob;
use App\Jobs\AgentProfileVerificationJob;
use App\Jobs\BankAccountVerificationJob;
use App\Jobs\BookDiagnosticsAppointmentJob;
use App\Jobs\HealthFeedVerificationJob;
use App\Jobs\SendAppointmentToDiagnosticsJob;
use App\Jobs\SendAppointmentToDoctorJob;
use App\Jobs\SendInquiryJob;
use App\Jobs\StaffApprovalMailJob;
use App\Jobs\StaffRegisterJob;
use App\Jobs\SendRefferalInvite;
use App\UserBankAccount;
use App\UserWallet;
use App\UserWithdrawHistory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\AppVersion;

class GlobalController extends Controller
{
	protected $random;
	public $success_code = 200;
	public $success_name = "success";
	public $error_code = 400;
	public $error_name = "fail";
	public $num_per_page = 20;
	public $null_result = [];
	private $exception_message = 'Something went wrong, please try again.';

	public function __construct()
	{
		$this->subject = "NC Health Club";
		$this->random = Str::random(10);
		$this->api = new Api(config('razorpay.razor_key'), config('razorpay.razor_secret'));
	}


	//get role lists
	public function getRoleList(Request $request)
	{
		try {
			$roles = UserRole::select('id', 'name', 'keyword')->get();
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Data loaded..',
				'data' => ['roles' => $roles]
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}

	//get all speciality list
	public function getSpeciality(Request $request)
	{
		try {
			$speciality = Specialty::select('id', 'title', 'image', 'color_code')->get()->map(function ($object) {
				$object->total_doctor = $object->totalDoctor($object->id);
				return $object;
			});
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Data loaded..',
				'data' => ['specialities' => $speciality]
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}

	//get all speciality list
	public function loadApp(Request $request)
	{
		try {
			$data = [];
			$loadData = [];
			$offers = Offer::select('id', 'title', 'image', 'type')->get();
			if (!$offers->isEmpty()) {
				$loadData[] = [
					'section_id' => 1,
					'type' => 'offer',
					'title' => 'Offers For You',
					'record' => $offers,
				];
			}
			$user = Auth::user();
			$doctors = User::select('id', 'name', 'profile_picture')->whereHas('role', function ($q) {
				$q->where('keyword', 'doctor');
			})->with(['detail' => function ($obj) {
				$obj->select(['id', 'user_id', 'specialty_ids', 'experience']);
			}]);
			/* $doctors          =       $doctors->select("*", DB::raw("6371 * acos(cos(radians(" . $user->latitude . "))
                                * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $user->longitude . "))
                                + sin(radians(" . $user->latitude . ")) * sin(radians(latitude))) AS distance"));
			$doctors          =       $doctors->having('distance', '<', 20); */
			if ($request->get('current_city')) {
				$city = $request->get('current_city');
				$doctors = $doctors->whereHas('practice', function ($q) use ($city) {
					$q->where('city', $city);
				});
			}
			$doctors = $doctors->where('as_doctor_verified', 2)->whereNotIn('id', [Auth::id()])->inRandomOrder()->limit(10)->get()->map(function ($object) {
				$object->speciality = $object->detail->specialty_name;
				$object->average_rating = isset($object->average_rating) ? $object->average_rating : 0;
				$object->total_review = isset($object->total_rating) ? $object->total_rating : 0;
				return $object;
			});
			if (!$doctors->isEmpty()) {
				$loadData[] = [
					'section_id' => 2,
					'type' => 'doctor',
					'title' => 'Top Doctors Near You',
					'record' => $doctors,
				];
			}

			$specialities = Specialty::select('id', 'title', 'image', 'color_code')->inRandomOrder()->limit(10)->get()->map(function ($object) {
				$object->total_doctor = $object->totalDoctor($object->id);
				return $object;
			});
			if (!$specialities->isEmpty()) {
				$loadData[] = [
					'section_id' => 3,
					'type' => 'speciality',
					'title' => 'Specialties',
					'record' => $specialities,
				];
			}
			$hospital = User::select('id', 'name', 'profile_picture', 'locality', 'city')->whereHas('role', function ($q) {
				$q->where('keyword', 'hospital');
			});
			if ($request->get('current_city')) {
				$hospital = $hospital->where('city', $request->get('current_city'));
			}
			$hospital = $hospital->inRandomOrder()->limit(10)->get();
			if (!$hospital->isEmpty()) {
				$loadData[] = [
					'section_id' => 4,
					'type' => 'hospital',
					'title' => 'Top Hospitals Near You',
					'record' => $hospital,
				];
			}
			$clinic = User::select('id', 'name', 'profile_picture', 'locality', 'city')->whereHas('role', function ($q) {
				$q->where('keyword', 'clinic');
			});
			if ($request->get('current_city')) {
				$clinic = $clinic->where('city', $request->get('current_city'));
			}
			$clinic = $clinic->inRandomOrder()->limit(10)->get();
			if (!$clinic->isEmpty()) {
				$loadData[] = [
					'section_id' => 5,
					'type' => 'clinic',
					'title' => 'Top Clinics Near You',
					'record' => $clinic,
				];
			}
			$diagnostics = User::select('id', 'name', 'profile_picture', 'locality', 'city')->whereHas('role', function ($q) {
				$q->where('keyword', 'diagnostics');
			})->where('as_diagnostics_verified', 2);
			if ($request->get('current_city')) {
				$diagnostics = $diagnostics->where('city', $request->get('current_city'));
			}
			$diagnostics = $diagnostics->inRandomOrder()->limit(10)->get();
			if (!$diagnostics->isEmpty()) {
				$loadData[] = [
					'section_id' => 6,
					'type' => 'diagnostics',
					'title' => 'Top Diagnostics Near You',
					'record' => $diagnostics,
				];
			}
			$healthfeeds = HealthFeed::select('id', 'title', 'cover_photo')->where('status', 1)->inRandomOrder()->limit(4)->get();
			if (!$healthfeeds->isEmpty()) {
				$loadData[] = [
					'section_id' => 7,
					'type' => 'healthfeed',
					'title' => 'Read About Health',
					'record' => $healthfeeds,
				];
			}
			
                   $isForceUpdate = ["forceupdate" => "N", "updateavailable" => "N", "message" => "", "newversion" => ""];
					/*$isNewVersion = AppVersion::where(["type" => $device["device_type"], "status" => 'Y'])->orderBy('id', 'desc')->first();
					if (!empty($isNewVersion) && $device['app_version'] < $isNewVersion->version) {
						$isForceUpdate = ["forceupdate" => $isNewVersion->forceupdate, "updateavailable" => "Yes", "message" => "New version " . $isNewVersion->version . " is available for App.", "newversion" => $isNewVersion->version];
					}*/
			
			
			

			$data['load_data'] = $loadData;
			$data['isForceUpdate'] = $isForceUpdate; 
			$data['extra'] = ['unread_notification_count' => Auth::user()->notification_count ];
			
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Data loaded..',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}

	//search using keyword and specialty id
	public function autoSearch(Request $request)
	{

		try {
			$data = [];
			$result = [];
			$speciality = [];
			$clinics = [];
			$hospitals = [];
			$doctor = [];
			$diagnostic = [];
			$limit_count = 5;

			if ($request->get('keyword')) {
				$specialities = Specialty::Where('title', 'LIKE', '%' . $request->get('keyword') . '%')
					->limit($limit_count)->orderBy('title', 'ASC')->get();

				//get hoapital data
				$hospital = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['hospital']);
				});
				if ($request->get('current_city')) {
					$hospital = $hospital->where('city', $request->get('current_city'));
				}
				$hospital = $hospital->with('role')->Where('name', 'LIKE', '%' . $request->get('keyword') . '%')->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());

				//get clinic data
				$clinic = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['clinic']);
				});
				if ($request->get('current_city')) {
					$clinic = $clinic->where('city', $request->get('current_city'));
				}
				$clinic = $clinic->with('role')->Where('name', 'LIKE', '%' . $request->get('keyword') . '%')->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());

				//get doctor data
				$doctors = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['doctor']);
				});
				if ($request->get('current_city')) {
					$city = $request->get('current_city');
					$doctors = $doctors->whereHas('practice', function ($q) use ($city) {
						$q->where('city', $city);
					});
				}
				$doctors = $doctors->with('role')->where('as_doctor_verified', 2)->Where('name', 'LIKE', '%' . $request->get('keyword') . '%')
					->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());

				//get diagnostics data
				$diagnostics = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['diagnostics']);
				});
				if ($request->get('current_city')) {
					$diagnostics = $diagnostics->where('city', $request->get('current_city'));
				}
				$diagnostics = $diagnostics->with('role')->where('as_diagnostics_verified', 2)->Where('name', 'LIKE', '%' . $request->get('keyword') . '%')
					->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());
			} else {
				$specialities = Specialty::inRandomOrder()->limit($limit_count)->orderBy('title', 'ASC')->get();

				//get hospital data
				$hospital = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['hospital']);
				});
				if ($request->get('current_city')) {
					$hospital = $hospital->where('city', $request->get('current_city'));
				}
				$hospital = $hospital->with('role')->inRandomOrder()->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());

				//get clinic data
				$clinic = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['clinic']);
				});
				if ($request->get('current_city')) {
					$clinic = $clinic->where('city', $request->get('current_city'));
				}
				$clinic = $clinic->with('role')->inRandomOrder()->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());

				//get doctor data
				$doctors = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['doctor']);
				});
				if ($request->get('current_city')) {
					$city = $request->get('current_city');
					$doctors = $doctors->whereHas('practice', function ($q) use ($city) {
						$q->where('city', $city);
					});
				}
				$doctors = $doctors->with('role')->where('as_doctor_verified', 2)->inRandomOrder()->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());

				//get diagnostics data
				$diagnostics = User::select('id', 'name', 'profile_picture', 'role_id', 'locality', 'city')->whereHas('role', function ($q) {
					$q->whereIn('keyword', ['diagnostics']);
				});
				if ($request->get('current_city')) {
					$diagnostics = $diagnostics->where('city', $request->get('current_city'));
				}
				$diagnostics = $diagnostics->with('role')->where('as_diagnostics_verified', 2)->inRandomOrder()->limit($limit_count)->orderBy('name', 'ASC')->get()->except(Auth::id());
			}

			if (!$specialities->isEmpty()) {
				foreach ($specialities as $s) {
					$speciality[] = [
						'id' => $s->id,
						'name' => $s->title,
						'profile_picture' => $s->image,
						'keyword' => 'speciality',
						'locality' => null,
						'city' => null
					];
				}
				$result[] = [
					'title' => 'Speciality',
					'keyword' => 'speciality',
					'record' => $speciality,
				];
			}

			if (!$clinic->isEmpty()) {
				foreach ($clinic as $u) {
					$clinics[] = [
						'id' => $u->id,
						'name' => $u->name,
						'profile_picture' => $u->profile_picture,
						'keyword' => $u->role->keyword,
						'locality' => $u->locality,
						'city' => $u->city
					];
				}
				$result[] = [
					'title' => 'Clinic',
					'keyword' => 'clinic',
					'record' => $clinics,
				];
			}

			if (!$hospital->isEmpty()) {
				foreach ($hospital as $u) {
					$hospitals[] = [
						'id' => $u->id,
						'name' => $u->name,
						'profile_picture' => $u->profile_picture,
						'keyword' => $u->role->keyword,
						'locality' => $u->locality,
						'city' => $u->city
					];
				}
				$result[] = [
					'title' => 'Hospital',
					'keyword' => 'hospital',
					'record' => $hospitals,
				];
			}
			if (!$doctors->isEmpty()) {
				foreach ($doctors as $d) {
					$doctor[] = [
						'id' => $d->id,
						'name' => $d->name,
						'profile_picture' => $d->profile_picture,
						'keyword' => $d->role->keyword,
						'locality' => $d->locality,
						'city' => $d->city
					];
				}
				$result[] = [
					'title' => 'Doctor',
					'keyword' => 'doctor',
					'record' => $doctor,
				];
			}

			if (!$diagnostics->isEmpty()) {
				foreach ($diagnostics as $d) {
					$diagnostic[] = [
						'id' => $d->id,
						'name' => $d->name,
						'profile_picture' => $d->profile_picture,
						'keyword' => $d->role->keyword,
						'locality' => $d->locality,
						'city' => $d->city
					];
				}
				$result[] = [
					'title' => 'Diagnostics',
					'keyword' => 'diagnostics',
					'record' => $diagnostic,
				];
			}


			$data['auto_search_data'] = $result;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Search result.',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}

	//search using keyword and specialty id with filter
	public function search(Request $request)
	{

		$rules = ['type' => 'required|in:speciality,doctor,clinic,hospital,healthfeed,all,diagnostics'];
		if ($request->get('type') == 'all') {
			$rules += ['speciality_id' => 'required|integer|exists:specialties,id'];
		}
		if ($request->get('gender')) {
			$rules += ['gender' => 'required|in:Male,Female,Other'];
		}
		if ($request->get('consultant_as')) {
			$rules += ['consultant_as' => 'required|in:ONLINE,INPERSON,BOTH'];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$data = [];
				$users = User::select('id', 'name', 'profile_picture', 'locality', 'city', 'role_id', 'gender')->with(['detail' => function ($obj) {
					$obj->select(['id', 'user_id', 'specialty_ids', 'experience']);
				}, 'role' => function ($u) {
					$u->select(['id', 'name', 'keyword']);
				}]);


				//search record using speciality
				if ($request->get('type') == 'all') {
					$id  = $request->get('speciality_id');

					$users = $users->whereHas('role', function ($q) {
						$q->whereIn('keyword', ['doctor', 'hospital', 'clinic', 'diagnostics']);
					})->whereHas('detail', function ($q) use ($id) {
						$q->whereRaw("find_in_set($id,specialty_ids)");
					});
					if ($request->get('current_city')) {
						$city = $request->get('current_city');
						$users = $users->whereHas('practice', function ($q) use ($city) {
							$q->where('city', $city);
						});
					}
				}

				//search record using user role like Doctor, Hospital, Clinic
				if ($request->get('type') == 'hospital' || $request->get('type') == 'clinic') {
					$type = $request->get('type');
					$users = $users->whereHas('role', function ($q) use ($type) {
						$q->where('keyword', $type);
					});
					if ($request->get('current_city')) {
						$users = $users->where('city', $request->get('current_city'));
					}
				}

				if ($request->get('type') == 'doctor') {
					$type = $request->get('type');
					$users = $users->whereHas('role', function ($q) use ($type) {
						$q->where('keyword', $type);
					})->where('as_doctor_verified', 2);

					if ($request->get('current_city')) {
						$city = $request->get('current_city');
						$users = $users->whereHas('practice', function ($q) use ($city) {
							$q->where('city', $city);
						});
					}
				}

				if ($request->get('type') == 'diagnostics') {
					$type = $request->get('type');
					$users = $users->whereHas('role', function ($q) use ($type) {
						$q->where('keyword', $type);
					})->where('as_diagnostics_verified', 2);

					if ($request->get('current_city')) {
						$users = $users->where('city', $request->get('current_city'));
					}
				}

				//filter with gender
				if ($request->get('gender')) {
					$users = $users->where('gender', $request->get('gender'));
				}

				//filter with consultant_as
				if ($request->get('consultant_as')) {
					if ($request->get('consultant_as') == 'ONLINE') {
						$users = $users->whereHas('setting', function ($q) {
							$q->whereIn('consultant_as', ['ONLINE', 'BOTH']);
						});
					}
					if ($request->get('consultant_as') == 'INPERSON') {
						$users = $users->whereHas('setting', function ($q) {
							$q->whereIn('consultant_as', ['INPERSON', 'BOTH']);
						});
					}
				}

				//filter with charge
				if ($request->get('min_charge') || $request->get('max_charge')) {
					$charge = [$request->get('min_charge'), $request->get('max_charge')];

					$users = $users->where(function ($query) use ($charge) {
						$query->whereHas('practice', function ($object) use ($charge) {
							$object->whereBetween('fees', $charge);
						});
						$query->orWhereHas('staff.practice', function ($object) use ($charge) {
							$object->whereBetween('fees', $charge);
							$object->whereHas('doctor.role', function ($role) {
								$role->whereIn('keyword', ['doctor']);
							});
						});
					});
				}

				if ($request->get('type') == 'healthfeed') {

					$health_feed = HealthFeed::select('id', 'title', 'cover_photo', 'category_ids', 'likes', 'views', 'user_id')->with(['category' => function ($obj) {
						$obj->select(['id', 'title', 'image']);
					}, 'user' => function ($u) {
						$u->select(['id', 'name']);
					}])->where('status', 1)->paginate($this->num_per_page);
					$data['total'] = $health_feed->total();
					$data['search_data'] = $health_feed->items();
				} elseif ($request->get('type') == 'speciality') {

					$speciality = Specialty::select('id', 'title', 'image', 'color_code');

					if ($request->get('search_keyword')) {
						$speciality = $speciality->Where('title', 'LIKE', '%' . $request->get('search_keyword') . '%')->orderBy('title', 'ASC');
					}

					$speciality =   $speciality->paginate($this->num_per_page);
					$data['total'] = $speciality->total();
					$speciality =  $speciality->items();
					if (!empty($speciality)) {
						foreach ($speciality as $key => $s) {
							$s->total_doctor = $s->totalDoctor($s->id);/*  */
						}
					}
					$data['search_data'] = $speciality;
				} else {
					if ($request->get('search_keyword')) {
						$users = $users->Where('name', 'LIKE', '%' . $request->get('search_keyword') . '%')->whereNotIn('id', [Auth::id()])->orderBy('name', 'ASC')->paginate($this->num_per_page);
					} else {
						$users = $users->whereNotIn('id', [Auth::id()])->paginate($this->num_per_page);
					}
					$data['total'] = $users->total();
					$users =  $users->items();
					if (!empty($users)) {
						foreach ($users as $key => $user) {
							$user->speciality = $user->detail->specialty_name;
							$user->total_doctor = $user->totalDoctor();
							$user->average_rating = isset($user->average_rating) ? $user->average_rating : 0;
							$user->total_review = isset($user->total_rating) ? $user->total_rating : 0;
						}
					}
					if ($users) {
						$data['search_data'] = $users;
					} else {
						$data['search_data'] = array();
					}
				}

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Search result.',
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
			}
		}
		return Response::json($result);
	}
	//get user profile
	public function getProfile(Request $request)
	{
		$rules = ['user_id' => 'required|exists:users,id'];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$data = [];
				$user = User::with('detail', 'limitedReview', 'setting', 'gallery', 'services', 'role', 'bankDetail')->find($request->get('user_id'));
				if ($user->detail->specialty_ids) {
					$user->detail->specialties = $user->detail->specialties;
					$user->speciality = $user->detail->specialty_name;
				}
				if ($user->detail->services) {
					$user->service = $user->detail->services_list_name;
				}
				if (!empty($user->limitedReview)) {
					foreach ($user->limitedReview as $rating) {
						$rating->user = $user->userDetail($rating->user_id);
					}
				}
				if (!empty($user->setting)) {
					$Consultant_Duration = array();
					foreach (config('view.Consultant_Duration') as $key => $value) {
						$Consultant_Duration[] = $value;
					}
					$user->setting->consultant_duration_timing = $Consultant_Duration;
				}
				$user->total_doctor = $user->totalDoctor();
				$user->doctors = $user->myDoctor();
				$user->average_rating = isset($user->average_rating) ? $user->average_rating : 0;
				$user->total_rating = isset($user->total_rating) ? $user->total_rating : 0;
				$user->is_wishlist  = $user->isWishlist($user->id);
				$user->isRateable = isRateable($user->id);
				$user->myReview = myReview($user->id);
				$user->symbol = "₹";
				$user->doctor_profile_verification_alert =  $user->doctorProfileVerificationAlert($user->as_doctor_verified);
				$user->agent_profile_verification_alert =  $user->agentProfileVerificationAlert($user->as_agent_verified);
				$user->bank_detail_verification_alert =  $user->bankDetailVerificationAlert($user->is_bank_verified);

				$data['profile'] = $user;
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'User Data.',
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get CMS
	public function getCms(Request $request)
	{

		$input = $request->all();
		$rules = [
			'page_name' => "required|in:about_us,terms_of_service,privacy_policy,doctor_tc,agent_tc"
		];
		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				switch ($input['page_name']) {
					case 'about_us':
						$template = 'pages.about_us';
						break;
					case 'terms_of_service':
						$template = 'pages.terms_of_service';
						break;
					case 'privacy_policy':
						$template = 'pages.privacy_policy';
						break;
					case 'doctor_tc':
						$template = 'pages.doctor_tc';
						break;
					case 'agent_tc':
						$template = 'pages.agent_tc';
						break;
						break;
				}
				$content['content'] = view($template)->render();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Legal information..',
					'data' => $content
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get My Doctor list 
	public function getMyDoctor(Request $request)
	{
		try {
			$doctor_ids = Auth::user()->wishlist->pluck('doctor_id'); //get doctors id 
			$doctors = User::select('id', 'name', 'profile_picture', 'address', 'locality')->with(['detail' => function ($obj) {
				$obj->select(['id', 'user_id', 'specialty_ids']);
			}])->whereIn('id', $doctor_ids)->paginate($this->num_per_page);
			$data['total'] = $doctors->total();
			$doctors =  $doctors->items();
			if (!empty($doctors)) {
				foreach ($doctors as $key => $doctor) {
					$doctor->speciality = $doctor->detail->specialty_name;
				}
			}
			$data['doctors'] = $doctors;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Wishlist data loaded',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}

	//add or remove doctor from wishlist
	public function manageMyDoctor(Request $request)
	{
		$rules = [
			'doctor_id' => 'required|exists:users,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				$input['user_id'] = Auth::id();
				$isUserExists = Wishlist::where('user_id', '=', Auth::id())->where('doctor_id', '=', $request->get('doctor_id'));
				if (!$isUserExists->exists()) {
					Wishlist::create($input);
					DB::commit();
					$message = 'Doctor add in wishlist';
				} else {
					$isUserExists->delete();
					DB::commit();
					$message = 'Doctor remove from wishlist';
				}
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message,
					'data' => $input
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get verified doctor list 
	public function getVerifiedDoctor(Request $request)
	{
		try {
			$doctors = User::with('detail')->whereHas('role', function ($q) {
				$q->where('keyword', 'doctor');
			})->where('as_doctor_verified', 2)->where('is_bank_verified', 2)->paginate($this->num_per_page);
			$data['total'] = $doctors->total();
			$doctors =  $doctors->items();
			if (!empty($doctors)) {
				foreach ($doctors as $key => $doctor) {
					$doctor->detail->specialties = $doctor->detail->specialties;
				}
			}
			$data['doctors'] = $doctors;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Verified doctor list',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}


	//manage staff
	public function manageStaff(Request $request)
	{
		$rules = [
			'action' => 'required|in:add,edit,delete',
		];
		if ($request->get('action') == 'add' || $request->get('action') == 'edit') {
			$rules += [
				'role_id' => 'required|exists:user_roles,id',
				'name' => 'required',
				'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
				'email' => 'required|email',
				'gender' => 'in:Male,Female,Other',
				'dob' => 'date',
				'pincode' => 'integer',
			];
		}
		if ($request->get('action') == 'edit' || $request->get('action') == 'delete') {
			$rules += [
				'id' => 'required|exists:staff_manager,id',
			];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$input = $request->all();
				DB::beginTransaction();
				if ($request->get('id')) {
					unset($input['role_id'], $input['action'], $input['id']);
					$staff = StaffManager::find($request->get('id'));
					if ($request->get('action') == 'edit') {
						$staff->user()->update($input);
						$message = "Staff update successfully";
					} else {
						$staff->user()->delete();
						$message = "Staff delete successfully";
					}
				} else {
					$password = passwordGenerate(12);
					$username = str_replace(' ', '', $input['name']) . mt_rand(111111, 999999);;
					$input['password'] = Hash::make($password);
					$input['username'] = $username;
					$user = User::create($input);
					UserDetail::updateOrCreate(['user_id' => $user->id], $input);
					$input['user_id'] = $user->id;
					$input['added_by'] = Auth::id();
					StaffManager::create($input);
					$message = $user->role->name . " add successfully";

					/* To share staff by email */
					if ($input['email']) {
						$user->password = $password;
						$url = URL::signedRoute('autologin', ['user' => $user]);
						if ($user->role->keyword == 'manager') {
							$work_role =  'manager';
						} else {
							$work_role =  'account';
						}
						$record = [
							'subject' => 'You have been invited to NC Health Club.',
							'recipient_name' => $input['name'],
							'recipient_email' => $input['email'],
							'content' => "This email is inform to you about invite as " . $user->role->keyword . " member. and " . Auth::user()->name . " has granted to access for manage " . $work_role . " related work. please complete your profile.<br>
							We are looking forward to working with you and seeing you achieve great things! ",
							'user' => $user,
							'soft_url' => $url,
							'sender_user' => Auth::user()->name,
						];
						dispatch(new StaffRegisterJob($record));
					}
				}
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get My Doctor list 
	public function getStaff(Request $request)
	{
		try {
			$staff = StaffManager::select('id', 'user_id', 'added_by')->with(['user' => function ($u) {
				$u->select('id', 'role_id', 'name', 'email', 'phone', 'gender', 'dob', 'blood_group', 'address', 'state', 'city', 'country', 'pincode', 'locality', 'latitude', 'longitude');
			}])->where('added_by', '=', Auth::id())->paginate($this->num_per_page);
			$data['total'] = $staff->total();
			$data['staffs'] =  $staff->items();
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Staff data loaded',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}

	//get Health feed categories
	public function getHealthCategory(Request $request)
	{
		try {
			$data['healthfeed_categories'] = HealthFeedCategory::select('id', 'title', 'image')->get();
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'List of Health Feed Categories',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
		}
		return Response::json($result);
	}

	//add Health feed 
	public function addHealthFeed(Request $request)
	{
		$rules = [
			'category_ids' => 'required|exists:health_categories,id',
			'title' => 'required',
			'content' => 'required',
			'cover_photo' => 'image|mimes:jfif,jpeg,png,jpg,gif,svg|max:2048',
			'video_url' => 'url',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				if ($request->hasFile('cover_photo')) {
					$avatar = $request->file('cover_photo');
					if ($avatar->getClientOriginalExtension() == 'jfif') {
						$filename = time() . $this->random . '.jpg';
						Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/healthfeed/' . $filename));
						$input['cover_photo'] = $filename;
					} else {
						$filename = time() . $this->random . '.' . $avatar->getClientOriginalExtension();
						Image::make($avatar)->save(storage_path('app/healthfeed/' . $filename));
						$input['cover_photo'] = $filename;
					}
				}
				$input['user_id'] = Auth::id();
				$healthfeed = HealthFeed::create($input);

				//get super admin id
				$is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
					$q->where('keyword', 'admin');
				})->first();

				$data = [
					'sender_id' => Auth::id(),
					'receiver_id' => isset($is_admin->id) ? $is_admin->id : '',
					'title' => 'Request For Health Feed Approval',
					'type' => 'health_feed',
					'message' => Auth::user()->name . ' has posted a health feed ' . $healthfeed->title . ' relevant to ' . $healthfeed->category->title . '. please review and approve it.'
				];

				Notification::create($data);

				if ($is_admin->email) {
					$mailInfo = ([
						'recipient_email' => $is_admin->email,
						'recipient_name' => 'NC Health HUB',
						'title' => 'Request For Health Feed Approval',
						'subject' => 'Request For The Approval Of The Health Feed',
						'content' => 'I’m writing to request approval for my new health feed.I have post health feed ' . $healthfeed->title . ' relevant to ' . $healthfeed->category->title . '.'
					]);
					dispatch(new HealthFeedVerificationJob($mailInfo)); //add mail to queue
				}

				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'HealthFeed Add successfully'
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//edit/delete Health feed 
	public function manageHealthFeed(Request $request)
	{
		$rules = [
			'id' => 'required|exists:health_feeds,id',
			'action' => 'required|in:edit,delete'
		];
		if ($request->get('action') == 'edit') {
			$rules += [
				'category_ids' => 'required|health_categories,id',
				'title' => 'required',
				'content' => 'required',
				'cover_photo' => 'image|mimes:jfif,jpeg,png,jpg,gif,svg|max:2048',
				'video_url' => 'url',
			];
		}
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$data = [];
				$input = $request->all();
				$healthfeed = HealthFeed::find($input['id']);
				if ($input['action'] == 'edit') {
					if ($request->hasFile('cover_photo')) {
						$avatar = $request->file('cover_photo');
						if ($avatar->getClientOriginalExtension() == 'jfif') {
							$filename = time() . $this->random . '.jpg';
							Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/healthfeed/' . $filename));
							$input['cover_photo'] = $filename;
						} else {
							$filename = time() . $this->random . '.' . $avatar->getClientOriginalExtension();
							Image::make($avatar)->save(storage_path('app/healthfeed/' . $filename));
							$input['cover_photo'] = $filename;
						}
						/*remove the existing profile picture*/
						$image_path = storage_path('app/healthfeed/' . $healthfeed->cover_photo_name);
						if ($healthfeed->cover_photo_name != "default.png") {
							@unlink($image_path);
						}
					}
					$input['status'] = 0;
					$input['feedback_message'] = null;

					$healthfeed->update($input);
					$message = 'HealthFeed updated successfully';

					//get super admin id
					$is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
						$q->where('keyword', 'admin');
					})->first();

					$data = [
						'sender_id' => Auth::id(),
						'receiver_id' => isset($is_admin->id) ? $is_admin->id : '',
						'title' => 'Request For Health Feed Approval',
						'type' => 'health_feed',
						'message' => Auth::user()->name . ' has been uploaded new Healthfeed. please check details and give response.'
					];


					Notification::create($data);

					if ($is_admin->email) {
						$mailInfo = ([
							'recipient_email' => $is_admin->email,
							'recipient_name' => 'NC Health HUB',
							'title' => 'Request For Health Feed Approval',
							'subject' => 'Request For The Approval Of The Health Feed',
							'content' => 'I’m writing to request approval for my updated health feed.I have post health feed ' . $healthfeed->title . ' relevant to ' . $healthfeed->category->title . '.'
						]);
						dispatch(new HealthFeedVerificationJob($mailInfo)); //add mail to queue
					}
				} else {
					$image_path = storage_path('app/healthfeed/' . $healthfeed->cover_photo_name);
					if ($healthfeed->cover_photo_name != "default.png") {
						@unlink($image_path);
					}
					$healthfeed->delete();
					$message = 'HealthFeed delete successfully';
				}
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message,
					'data' => $data
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage settings
	public function settings(Request $request)
	{

		$input = $request->all();
		$rules = [
			'consultant_as' => 'required|in:ONLINE,INPERSON,BOTH',
			'consultant_duration' => 'required|numeric',
			'availability' => 'required|in:0,1',
			'do_service_at_other_establishment' => 'required|in:0,1'
		];
		if ($request->get('availability') == 0) {
			$rules += ['unavailability_start_date' => 'required|before:unavailability_end_date', 'unavailability_end_date' => 'required'];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				Setting::updateOrCreate(['user_id' => Auth::id()], $input);
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Setting Changed'
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage drugs
	public function manageDrugs(Request $request)
	{

		$input = $request->all();
		$rules = [
			'action' => 'required|in:add,edit,delete'
		];
		if ($request->get('action') == 'delete' || $request->get('action') == 'edit') {
			$rules += [
				'id' => 'required|exists:drugs,id',
			];
		}
		if ($request->get('action') == 'add' || $request->get('action') == 'edit') {
			$rules += [
				'name' => 'required',
				'type' => 'required',
				'strength' => 'required',
				'unit' => 'required'
			];
		}
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				if ($request->get('action') == 'add') {
					$input['added_by'] = Auth::id();
					Drug::create($input);
					$message = 'Drug created successfully';
				}
				if ($request->get('id')) {
					$drug = Drug::find($request->get('id'));
					if ($request->get('action') == 'edit') {
						$drug->update($input);
						$message = 'Drug updated successfully';
					} else {
						$drug->delete();
						$message = 'Drug deleted successfully';
					}
				}

				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get drugs
	public function getDrugs(Request $request)
	{
		try {
			if ($request->get('id')) {
				$drugs = Drug::where('added_by', $request->get('id'))->orderBy('id', 'DESC')->paginate($this->num_per_page);
			} else {
				$drugs = Drug::where('name', 'LIKE', '%' . $request->get('keyword') . '%')->orderBy('id', 'DESC')->paginate($this->num_per_page);
			}
			$data['total'] = $drugs->total();
			$data['drugs'] =  $drugs->items();
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Drugs list Loaded',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//Add feedback
	public function addFeedback(Request $request)
	{
		$input = $request->all();
		$rules = [
			'text' => 'required'
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input['user_id'] = Auth::id();
				Feedback::updateOrCreate(['user_id' => Auth::id()], $input);
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Thanks for your valuable feedback'
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//Add review
	public function addReview(Request $request)
	{
		$input = $request->all();
		$rules = [
			'rating' => 'required|integer',
			'rateable_id' => 'required||exists:users,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input['user_id'] = Auth::id();
				$id = $input['rateable_id'];
				$appointment = Appointment::where('patient_id', Auth::id())->where('status', 'completed')->whereHas('practice', function ($p) use ($id) {
					$p->where('added_by', $id);
					$p->orWhere('doctor_id', $id);
				})->count();
				if ($appointment > 0) {
					Rating::updateOrCreate(['user_id' => Auth::id(), 'rateable_id' => $input['rateable_id']], $input);
					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'Thanks for your valuable Review'
					];
				} else {
					DB::commit();
					$result = [
						'status' => $this->error_name,
						'code' => $this->error_code,
						'message' => 'you can`t valid for give review & rating'
					];
				}
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage review
	public function manageReview(Request $request)
	{
		$input = $request->all();
		$rules = [
			'id' => 'required|exists:ratings,id',
			'action' => 'required|in:edit,delete',
		];
		if ($request->get('action') == 'edit') {
			$rules += [
				'rating' => 'required|integer',
			];
		}
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				$rating = Rating::find($input['id']);
				if ($input['action'] == 'edit') {
					$rating->update($input);
					$message = 'Rating updated successfully';
				} else {
					$rating->delete();
					$message = 'Rating delete successfully';
				}
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage review
	public function getAllReview(Request $request)
	{

		$rules = [
			'id' => 'required||exists:users,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$ratings = Rating::with(['user' => function ($data) {
					$data->select('id', 'name', 'profile_picture');
				}])->where('rateable_id', $request->get('id'))->orderBy('id', 'DESC')->paginate($this->num_per_page);
				$data['total'] = $ratings->total();
				$data['reviews'] =  $ratings->items();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'reviews list Loaded',
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//view healthfeed
	public function viewHealthFeed(Request $request)
	{
		$rules = [];
		if ($request->get('id')) {
			$rules += [
				'id' => 'required|exists:health_feeds,id',
			];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$input = $request->all();
				$healthfeed =  HealthFeed::with(['category' => function ($obj) {
					$obj->select(['id', 'title']);
				}, 'user' => function ($obj) {
					$obj->select(['id', 'name']);
				}]);

				if ($request->get('id')) {
					$data['healthfeeds'] = $healthfeed->where('id', $request->get('id'))->get()->map(function ($object) {
						$object->date = $object->health_feed_date;
						$object->video_frame = $object->html_video_url;
						return $object;
					});
				} elseif ($request->get('doctor_id') == Auth::id()) {
					$healthfeeds = $healthfeed->where('user_id', $request->get('doctor_id'))->paginate($this->num_per_page);
					$data['total'] = $healthfeeds->total();
					$data['healthfeeds'] =  $healthfeeds->items();
				} else {
					$data['healthfeeds'] = $this->null_result;
				}

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'healthfeed data',
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get existing practice timing
	public function getExistPractice(Request $request)
	{
		try {
			$practices = PracticeManager::where(['doctor_id' => Auth::id(), 'status' => 1])->get();
			foreach ($practices as $key => $p) {
				$array_big = [];
				//get timing of current record
				if (!empty($p->timing)) {
					$timing = json_decode($p->timing);
					foreach ($timing as $key => $time) {
						$new_timing = [];
						foreach ($time->periods as $t) {
							$StartTime    = strtotime($t->start);
							$EndTime      = strtotime($t->end);
							$AddMins  = 3600;
							while ($StartTime < $EndTime) //Run loop
							{
								$new_timing[] = [
									'start' => date("G:i", $StartTime),
									'end' => date("G:i", $StartTime + 3600),
									'title' => $p->name,
									'backgroundColor' => "rgba(82, 155, 255, 0.5)",
									'borderColor' => "rgb(42, 60, 255)",
									'textColor' => "rgb(0, 0, 0)",
								];
								$StartTime += $AddMins; //Endtime check
							}
						}
						$time->periods = $new_timing; //divided long slot in hourly
					}

					$array_big = array_merge($array_big, $timing);
				}
				$allocated_timing = PracticeManager::select('name', 'timing')->where(['doctor_id' => Auth::id(), 'status' => 1])->whereNotIn('id', [$p->id])->get();

				foreach ($allocated_timing as $key => $timing) {
					if (!empty($timing)) {
						$days = json_decode($timing->timing);
						foreach ($days as $d_key => $d) {
							if (!empty($d->periods)) {
								$new_timing = [];
								foreach ($d->periods as $t) {
									$StartTime    = strtotime($t->start);
									$EndTime      = strtotime($t->end);
									$AddMins  = 3600;
									while ($StartTime < $EndTime) //Run loop
									{
										$new_timing[] = [
											'start' => date("G:i", $StartTime),
											'end' => date("G:i", $StartTime + 3600),
											'title' => $timing->name,
											'backgroundColor' => "rgba(82, 155, 255, 0.5)",
											'borderColor' => "rgb(42, 60, 255)",
											'textColor' => "rgb(0, 0, 0)",
											'is_exist' => 1,
										];
										$StartTime += $AddMins; //Endtime check
									}
								}
								$d->periods = $new_timing; //divided long slot in hourly 
								$array_big[$d_key]->periods = array_merge($array_big[$d_key]->periods, $d->periods);
							}
						}
					}
				}
				$p->timing = $array_big;
			}
			$data['practices'] = $practices;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Timing slots',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//Add new practice
	public function addPractice(Request $request)
	{
		$input = $request->all();
		$input['doctor_id'] = Auth::id();
		$rules = [
			'name' => 'required',
			'email' => 'required|email|max:255',
			'phone' => 'required|regex:/^[0-9]{10}+$/',
			'address' => "required",
			'locality' => "required",
			'city' => "required",
			'country' => "required",
			'pincode' => "required|integer",
			'fees' => "required|numeric",
			'logo' => "mimes:jpeg,jpg,png",
			'timing' => "required",
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				if ($request->hasFile('logo')) {
					$avatar = $request->file('logo');
					if ($avatar->getClientOriginalExtension() == 'jfif') {
						$filename = time() . uniqid() . '.jpg';
						Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/practice/' . $filename));
						$input['logo'] = $filename;
					} else {
						$filename = time() . uniqid() . '.' . $avatar->getClientOriginalExtension();
						Image::make($avatar)->save(storage_path('app/practice/' . $filename));
						$input['logo'] = $filename;
					}
				}
				$input['added_by'] = Auth::id();
				PracticeManager::create($input);
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'practice added successfully'
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage practice
	public function managePractice(Request $request)
	{
		$input = $request->all();
		$input['doctor_id'] = Auth::id();
		$rules = [
			'id' => 'required|exists:practice_manager,id',
			'action' => 'required|in:edit,delete',
		];
		if ($input['action'] == 'edit') {
			$rules += [
				'name' => 'required',
				'email' => 'required|email|max:255',
				'phone' => 'required|regex:/^[0-9]{10}+$/',
				'address' => "required",
				'locality' => "required",
				'city' => "required",
				'country' => "required",
				'pincode' => "required|integer",
				'fees' => "required|numeric",
				'logo' => "mimes:jpeg,jpg,png",
				'timing' => "required",
			];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$practice = PracticeManager::find($input['id']);
				if ($input['action'] == 'edit') {
					if ($request->hasFile('logo')) {
						$avatar = $request->file('logo');
						if ($avatar->getClientOriginalExtension() == 'jfif') {
							$filename = time() . uniqid() . '.jpg';
							Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/practice/' . $filename));
							$input['logo'] = $filename;
						} else {
							$filename = time() . uniqid() . '.' . $avatar->getClientOriginalExtension();
							Image::make($avatar)->save(storage_path('app/practice/' . $filename));
							$input['logo'] = $filename;
						}
						$image_path = storage_path('app/practice/' . $practice->logo_filename);
						if ($practice->logo_filename != "practice_logo.png") {
							@unlink($image_path);
						}
					}

					$practice->update($input);
					$message = 'Practice updated successfully';
				} else {
					$image_path = storage_path('app/practice/' . $practice->logo_filename);
					if ($practice->logo_filename != "practice_logo.png") {
						@unlink($image_path);
					}
					$practice->delete();
					$message = 'Practice delete successfully';
				}
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get Practice
	public function getPractice(Request $request)
	{
		$rules = [
			'parent_id' => 'nullable|exists:users,id',
		];

		if ($request->get('id')) {
			$rules += [
				'id' => 'required|exists:users,id',
			];
		}
		if ($request->get('diagnostics_id')) {
			$rules += [
				'diagnostics_id' => 'required|exists:users,id',
			];
		}
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$data = [];
				$subArray = [];
				if ($request->get('id')) {
					$practices = PracticeManager::where(['doctor_id' => $request->get('id'), 'status' => 1]);
					if ($request->get('parent_id')) {
						if (User::where('id', $request->get('parent_id'))->whereHas('role', function ($role) {
							$role->whereIn('keyword', ['clinic', 'hospital']);
						})->exists()) {
							$practices = $practices->where('added_by', $request->get('parent_id'));
						}
					}
				} else {
					$practices = PracticeManager::where(['added_by' => $request->get('diagnostics_id'), 'status' => 1]);
				}
				$practices = $practices->get();
				if (!$practices->isEmpty()) {
					foreach ($practices as $practiceManager) {
						$startDate = Carbon::now();
						$endDate = Carbon::now()->addDays(15);
						$period = CarbonPeriod::create($startDate, $endDate);
						$schedule = [];

						$startTimeWithTimeZone = Carbon::now(Auth::user()->timezone)->addMinute(60)->format('H:i');

						$booked_appointment = Appointment::where('practice_id', $practiceManager->id)->whereNotIn('status', ['pending', 'cancelled'])->get(['id', 'date', 'start_time', 'end_time'])->map(function ($appointment) {
							$appointment->start_time = Carbon::parse($appointment->start_time)->format('g:i A');
							$appointment->end_time = Carbon::parse($appointment->end_time)->format('g:i A');
							return $appointment;
						})->toArray();

						$timing = json_decode($practiceManager->timing);

						if (isset($practiceManager->doctor_id)) {
							$consultant_duration = isset($practiceManager->doctor->setting->consultant_duration) ? $practiceManager->doctor->setting->consultant_duration : 30;
							$availability = isset($practiceManager->doctor->setting->availability) ? $practiceManager->doctor->setting->availability : 1;
						} else {
							$consultant_duration = isset($practiceManager->addedBy->setting->consultant_duration) ? $practiceManager->addedBy->setting->consultant_duration : 30;
							$availability = isset($practiceManager->addedBy->setting->availability) ? $practiceManager->addedBy->setting->availability : 1;
						}

						foreach ($period as $p_key => $date) {

							$subArray = array_filter($booked_appointment, function ($ar) use ($date) {
								return ($ar['date'] == $date->format('Y-m-d'));
							});

							$dayname = $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : date("D, d M ", strtotime($date)));
							$day_number = weekDayNumber(date("l", strtotime($date)));
							$temp_slot = [];
							if (!empty($timing[$day_number]->periods)) {
								foreach ($timing[$day_number]->periods as $key => $p) {
									// p($p, 0);
									$StartTime    = strtotime($p->start);
									$EndTime      = strtotime($p->end);
									$AddMins  = $consultant_duration * 60;
									$slotEndTime = $StartTime;
									while ($StartTime < $EndTime) //Run loop
									{
										$slotEndTime += $AddMins;
										if ($date->isToday()) {
											if ($StartTime >= strtotime($startTimeWithTimeZone)) {
												$temp_slot[] = [
													'practice_id' => $practiceManager->id,
													'date' => $date->format('Y-m-d'),
													'time' => [
														'start_time' => date("g:i A", $StartTime),
														'end_time' => date("g:i A", $slotEndTime),
													],
													'is_booked' => 0
												];
											}
										} else {
											$temp_slot[] = [
												'practice_id' => $practiceManager->id,
												'date' => $date->format('Y-m-d'),
												'time' => [
													'start_time' => date("g:i A", $StartTime),
													'end_time' => date("g:i A", $slotEndTime),
												],
												'is_booked' => 0
											];
										}

										$StartTime += $AddMins; //Endtime check
									}
								}
							}
							/* Sorting the time */
							usort($temp_slot, function ($a, $b) {
								return (strtotime($a['time']['start_time']) > strtotime($b['time']['start_time']));
							});

							$count = 0;
							foreach ($temp_slot as $key => $time) {
								$count = 0;
								foreach ($subArray as $sub_key => $sub_value) {
									if ($date->format('Y-m-d') == $sub_value['date'] && ($time['time']['start_time'] == $sub_value['start_time'] || $time['time']['end_time'] == $sub_value['end_time'])) {
										$temp_slot[$key] = array_merge($temp_slot[$key], ['is_booked' => 1]);
									}
								}
								//if(!array_count_values(array_column($temp_slot, 'is_booked')).isEmpty){
								
							   //  $count = array_count_values(array_column($temp_slot, 'is_booked'))[0];
								//}
								
								
								if(isset(array_count_values(array_column($temp_slot, 'is_booked'))[0])) {
									 $count = array_count_values(array_column($temp_slot, 'is_booked'))[0];
								}
									
							}

							//check unavailability date
							if (isset($practiceManager->doctor_id) && $availability == 0 && ($date->format('Y-m-d') >= $practiceManager->doctor->setting->unavailability_start_date) && ($date->format('Y-m-d') <= $practiceManager->doctor->setting->unavailability_end_date)) {
								$schedule[] = [
									'title' => $dayname,
									'date' => $date->format('Y-m-d'),
									'slot' => [],
									'slot_available' => 'No Slots Available1',
								];
							} else if ($availability == 0 && ($date->format('Y-m-d') >= $practiceManager->addedBy->setting->unavailability_start_date) && ($date->format('Y-m-d') <= $practiceManager->addedBy->setting->unavailability_end_date)) {
								$schedule[] = [
									'title' => $dayname,
									'date' => $date->format('Y-m-d'),
									'slot' => [],
									'slot_available' => 'No Slots Available2',
								];
							} else {
								$schedule[] = [
									'title' => $dayname,
									'date' => $date->format('Y-m-d'),
									'slot' => $temp_slot,
									'slot_available' => ($count > 0) ? $count .' Slots Available' : 'No Slots Available',
								];
							}
						}
						if ($practiceManager->doctor_id == $practiceManager->added_by) {
							$logo = $practiceManager->logo;
						} else {
							$logo = $practiceManager->addedBy->profile_picture;
						}
						$sub[] = [
							'id' => $practiceManager->id,
							'doctor_id' => $practiceManager->doctor_id,
							'added_by' => $practiceManager->added_by,
							'name' => $practiceManager->name,
							'email' => $practiceManager->email,
							'phone' => $practiceManager->phone,
							'logo' => isset($logo) ? $logo : $practiceManager->logo,
							'address' => $practiceManager->address,
							'locality' => $practiceManager->locality,
							'city' => $practiceManager->city,
							'country' => $practiceManager->country,
							'pincode' => $practiceManager->pincode,
							'symbol' =>  "₹",
							'fees' => $practiceManager->fees,
							'latitude' => $practiceManager->latitude,
							'longitude' => $practiceManager->longitude,
							'availability' => $schedule,
							'specialities' => $practiceManager->addedBy->detail->specialties,
						];
					}
					$data['practices'] = $sub;
				} else {
					$data['practices'] = array();
				}

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'User Data.',
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//book appointment
	public function bookAppointment(Request $request)
	{
		$rules = [
			'patient_id' => 'required|exists:users,id',
			'doctor_id' => 'required|exists:users,id',
			'appointment_type' => 'required|in:ONLINE,INPERSON',
			'patient_name' => 'required',
			'patient_phone' => 'required|regex:/^[0-9]{10}+$/|numeric',
			'patient_email' => 'email',
			'date' => 'required|date',
			'start_time' => 'required',
			'practice_id' => 'required|exists:practice_manager,id',
			'appointment_from' => 'required|in:android,ios',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				$practice = PracticeManager::find($input['practice_id']);
				$consultant_duration = isset($practice->doctor->setting->consultant_duration) ? $practice->doctor->setting->consultant_duration : 30;
				$input['end_time'] = Carbon::parse($input['start_time'])->addMinutes($consultant_duration);

				$orderParameters = [
					'receipt' => '#' . Auth::id() . strtotime(Carbon::now()),
					'amount' => $practice->fees * 100,
					'currency' => 'INR',
					'payment_capture' => 1, // auto capture
				];

				//check appointment already booked or not
				$appointment = Appointment::where('doctor_id', $practice->doctor_id)
					->where('practice_id', $practice->id)
					->where('start_time', $input['start_time'])
					->where('date', $input['date'])->first();

				if (!$appointment) {

					$razorpayOrder = $this->api->order->create($orderParameters);

					if ($razorpayOrder) {
						$paymentParameter = [
							'name'  => Auth::user()->name,
							'user_id' => Auth::id(),
							'receipt_id' => $orderParameters['receipt'],
							'order_id' => $razorpayOrder->id,
							'customer_id' => isset($razorpayOrder->customer_id) ? $razorpayOrder->customer_id : '',
							'txn_date' => Carbon::createFromTimestamp($razorpayOrder->created_at),
							'amount' => $practice->fees,
							'payable_amount' => $practice->fees,
							'status' => $razorpayOrder->status,
							'theme_color' => '#3399cc',
							'currency' => $orderParameters['currency'],
							'image' => url(asset('images/favicon/neucrad.png')),
							'email' => Auth::user()->email,
							'contact' => Auth::user()->phone,
							'api_key' => config('razorpay.razor_key')
						];
						//create payment here
						$payment = Payment::create($paymentParameter);
						$input['payment_id'] = $payment->id;
						$appointment = Appointment::create($input);
						$paymentParameter['appointment_id'] = $appointment->id;
						$paymentParameter['appointment_payment_id'] = $payment->id;

						//add doctor into wishlist
						Wishlist::updateOrCreate(['user_id' => Auth::id(), 'doctor_id' => $input['doctor_id']], ['user_id' => Auth::id(), 'doctor_id' => $input['doctor_id']]);

						DB::commit();
						$result = [
							'status' => $this->success_name,
							'code' => $this->success_code,
							'message' => 'Your appointment is under process',
							'data' => $data = ['result' => $paymentParameter]
						];
					} else {
						DB::rollback();
						$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
					}
				} else {
					DB::rollback();
					$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => 'This appointment time already booked. Please try another time!'];
				}
			} catch (Exception $e) {
				DB::rollback();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//verify payment
	public function verifyPayment(Request $request)
	{
		$rules = [
			'appointment_id' => 'required|exists:appointments,id',
			'appointment_payment_id' => 'required|exists:payments,id',
			'razorpay_order_id' => 'required',
			'razorpay_payment_id' => 'required',
			'razorpay_signature' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				$appointment = Appointment::find($request->get('appointment_id'));
				$attributes = array(
					'razorpay_order_id' => $request->get('razorpay_order_id'),
					'razorpay_payment_id' => $request->get('razorpay_payment_id'),
					'razorpay_signature' => $request->get('razorpay_signature')
				);

				$this->api->utility->verifyPaymentSignature($attributes);
				$orderStatus = $this->api->order->fetch($request->get('razorpay_order_id'));
				if (!empty($orderStatus) && isset($orderStatus->id)) {
					$payment = Payment::where('id', $request->get('appointment_payment_id'))->where('order_id', $request->get('razorpay_order_id'))->first();
					$orderData = getOrderID();
					$payment->payment_id = $request->get('razorpay_payment_id');
					$payment->status = $orderStatus->status;
					$payment->order_no = $orderData['order_no'];
					$payment->invoice_id = $orderData['invoice_id'];
					$payment->save();
					if ($payment) {
						$appointment->update(['status' => 'create']);
					}

					$record = [
						'subject' => '(Appointment Booked) ' . $appointment->doctor->name . ' on ' . date('l jS F, \a\t h:i a', strtotime($appointment->start_time)) . ' EST',
						'recipient_name' => $appointment->patient_name,
						'recipient_email' => $appointment->patient_email,
						'recipient_phone' => $appointment->patient_phone,
						'text' => "Your Appointment booked successfully.",
						'title' => "Your appointment has been scheduled!",
						'invoice' => '',
						'invoice_filename' => '',
						'doctor_email' => $appointment->doctor->email,
						'doctor_name' => $appointment->doctor->name,
						'manager' => $appointment->doctor->manager()->pluck('email', 'name'),
						'payment_id' => $payment->id,
						'appointment_id' => $appointment->id,
						'appointment_time' => $appointment->start_time,
						'practice_name' => $appointment->practice->name,
						'practice_address' => $appointment->practice->full_address,
						'practice_phone' => $appointment->practice->phone,
					];
					if ($appointment->patient_email) {
						dispatch(new BookAppointmentJob($record)); //add mail to queue
					}
					if ($appointment->doctor->email) {
						dispatch(new SendAppointmentToDoctorJob($record)); //add mail to queue
					}

					$ids = $appointment->doctor->manager()->pluck('id');
					$ids[] = $appointment->doctor_id;
					$input = [];
					foreach ($ids as $id) {
						$input[] = [
							'sender_id' => Auth::id(),
							'receiver_id' => $id,
							'title' => "Appointment Booked ",
							'type' => "book_appointment",
							'message' => ucfirst(Auth::user()->name) . " has booked an appointment with you on " . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ".",
						];
					}
					Notification::insert($input);

					$chat = Chat::where('sender_id', Auth::id())->where('recipient_id', $appointment->doctor_id)
						->orWhere('sender_id', $appointment->doctor_id)->where('recipient_id', Auth::id())
						->get();
					if ($chat->isEmpty()) {
						$chat = Chat::create(['sender_id' => Auth::id(), 'recipient_id' => $appointment->doctor_id]);
					}

					//send notification to doctor
					$androidToken = UserApp::where('user_id', $appointment->doctor_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

					if (!empty($androidToken)) {
						$subject = "Book Appointment";
						$sms_push_text = ucfirst(Auth::user()->name) . " has booked an appointment with you on " . date('l jS F, \a\t h:i a', strtotime($appointment->start_time)) . ".";
						$extra = ['id' => $appointment->patient_id, 'type' => 'book_appointment', 'user_type' => 'doctor', 'appointment_status' => 'create'];

						$this->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
					}

					//start wallet management
					$wallet = [];
					$is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
						$q->where('keyword', 'admin');
					})->first();
					if ($is_admin->commission->neucrad_commission) {
						$admin_fee = $appointment->payment->amount * ($is_admin->commission->neucrad_commission / 100);

						if ($appointment->practice->added_by) {
							$payable_commission = (100 - $is_admin->commission->neucrad_commission) / 100;
							$payable_amount = $appointment->payment->amount * $payable_commission;
							$wallet[] = [
								'user_id' => $appointment->practice->added_by,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $payable_amount,
							];
						}

						if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1 && $appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
							$wallet[] = [
								'user_id' => $appointment->patient->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
							];
							$wallet[] = [
								'user_id' => $appointment->practice->addedBy->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
							];

							$admin_commission = 100 - ($is_admin->commission->other_agent + $is_admin->commission->patient_agent);
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($admin_commission / 100),
							];
						} else if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1) {
							$wallet[] = [
								'user_id' => $appointment->patient->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
							];
							$admin_commission = 100 - $is_admin->commission->patient_agent;
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($admin_commission / 100),
							];
						} else if ($appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
							$wallet[] = [
								'user_id' => $appointment->practice->addedBy->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
							];
							$admin_commission = 100 - $is_admin->commission->other_agent;
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($admin_commission / 100),
							];
						} else {
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee,
							];
						}

						UserWallet::insert($wallet);
					}
					//end wallet management

					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'Your appointment has been booked successfully'
					];
				}
			} catch (SignatureVerificationError $e) {
				DB::rollBack();
				$this->status = 401;
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => 'Your payment has been failed.', "result" => $e->getMessage()];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//book appointment
	public function bookDiagnosticsAppointment(Request $request)
	{
		$rules = [
			'patient_id' => 'required|exists:users,id',
			'diagnostics_id' => 'required|exists:users,id',
			'appointment_type' => 'required|in:ONLINE,INPERSON',
			'services_ids' => 'required',
			'patient_name' => 'required',
			'patient_phone' => 'required|regex:/^[0-9]{10}+$/|numeric',
			'patient_email' => 'required|email',
			'date' => 'required|date',
			'start_time' => 'required',
			'practice_id' => 'required|exists:practice_manager,id',
			'appointment_from' => 'required|in:android,ios',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				$practice = PracticeManager::find($input['practice_id']);
				$consultant_duration = isset($practice->addedBy->setting->consultant_duration) ? $practice->addedBy->setting->consultant_duration : 30;
				$input['end_time'] = Carbon::parse($input['start_time'])->addMinutes($consultant_duration);

				if ($request->get('services_ids')) {
					$services_ids = stringToArray($request->get('services_ids'));
				}
				$services_fee = DiagnosticsService::whereIn('id', $services_ids)->sum('price');

				if ($request->get('sample_pickup') && $request->get('sample_pickup') == 1) {
					$input['is_sample_pickup'] = $request->get('sample_pickup');
					$sample_pickup_charge = isset($practice->addedBy->setting->sample_pickup_charge) ? $practice->addedBy->setting->sample_pickup_charge : 0;
					$services_fee = $services_fee + $sample_pickup_charge;
				}

				$orderParameters = [
					'receipt' => '#' . Auth::id() . strtotime(Carbon::now()),
					'amount' => $services_fee * 100,
					'currency' => 'INR',
					'payment_capture' => 1 // auto capture
				];

				//check appointment already booked or not
				$appointment = Appointment::where('diagnostics_id', $input['diagnostics_id'])
					->where('practice_id', $practice->id)
					->where('start_time', $input['start_time'])
					->where('date', $input['date'])->whereHas('payment', function ($payment) {
						$payment->where('status', 'paid');
					})->first();

				if (!$appointment) {

					$razorpayOrder = $this->api->order->create($orderParameters);

					if ($razorpayOrder) {
						$paymentParameter = [
							'name'  => Auth::user()->name,
							'user_id' => Auth::id(),
							'receipt_id' => $orderParameters['receipt'],
							'order_id' => $razorpayOrder->id,
							'customer_id' => isset($razorpayOrder->customer_id) ? $razorpayOrder->customer_id : '',
							'txn_date' => Carbon::createFromTimestamp($razorpayOrder->created_at),
							'amount' => $services_fee,
							'payable_amount' => $services_fee,
							'status' => $razorpayOrder->status,
							'theme_color' => '#3399cc',
							'currency' => $orderParameters['currency'],
							'image' => url(asset('images/favicon/neucrad.png')),
							'email' => Auth::user()->email,
							'contact' => Auth::user()->phone,
							'api_key' => config('razorpay.razor_key')
						];
						//create payment here
						$payment = Payment::create($paymentParameter);
						$input['payment_id'] = $payment->id;
						$appointment = Appointment::create($input);
						$paymentParameter['appointment_id'] = $appointment->id;
						$paymentParameter['appointment_payment_id'] = $payment->id;

						DB::commit();
						$result = [
							'status' => $this->success_name,
							'code' => $this->success_code,
							'message' => 'Your appointment is under process',
							'data' => $data = ['result' => $paymentParameter]
						];
					} else {
						DB::rollback();
						$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
					}
				} else {
					DB::rollback();
					$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => 'This appointment time already booked. Please try another time!'];
				}
			} catch (Exception $e) {
				DB::rollback();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//verify payment
	public function verifyDiagnosticsPayment(Request $request)
	{
		$rules = [
			'appointment_id' => 'required|exists:appointments,id',
			'appointment_payment_id' => 'required|exists:payments,id',
			'razorpay_order_id' => 'required',
			'razorpay_payment_id' => 'required',
			'razorpay_signature' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				$appointment = Appointment::find($request->get('appointment_id'));
				$attributes = array(
					'razorpay_order_id' => $request->get('razorpay_order_id'),
					'razorpay_payment_id' => $request->get('razorpay_payment_id'),
					'razorpay_signature' => $request->get('razorpay_signature')
				);

				$this->api->utility->verifyPaymentSignature($attributes);
				$orderStatus = $this->api->order->fetch($request->get('razorpay_order_id'));
				if (!empty($orderStatus) && isset($orderStatus->id)) {
					$payment = Payment::where('id', $request->get('appointment_payment_id'))->where('order_id', $request->get('razorpay_order_id'))->first();
					$orderData = getOrderID();
					$payment->payment_id = $request->get('razorpay_payment_id');
					$payment->status = $orderStatus->status;
					$payment->order_no = $orderData['order_no'];
					$payment->invoice_id = $orderData['invoice_id'];
					$payment->save();
					if ($payment) {
						$appointment->update(['status' => 'create']);
					}

					$record = [
						'subject' => '(Appointment Booked) ' . $appointment->diagnostics->name . ' on ' . date('l jS F, \a\t h:i a', strtotime($appointment->start_time)) . ' EST',
						'recipient_name' => $appointment->patient_name,
						'recipient_email' => $appointment->patient_email,
						'recipient_phone' => $appointment->patient_phone,
						'text' => "Your Appointment booked successfully.",
						'title' => "Your appointment has been scheduled!",
						'invoice' => '',
						'invoice_filename' => '',
						'doctor_email' => $appointment->diagnostics->email,
						'doctor_name' => $appointment->diagnostics->name,
						'manager' => $appointment->diagnostics->manager()->pluck('email', 'name'),
						'payment_id' => $payment->id,
						'appointment_id' => $appointment->id,
						'appointment_time' => $appointment->start_time,
						'practice_name' => $appointment->practice->name,
						'practice_address' => $appointment->practice->full_address,
						'practice_phone' => $appointment->practice->phone,
					];
					if ($appointment->patient_email) {
						dispatch(new BookAppointmentJob($record)); //add mail to queue
					}
					if ($appointment->diagnostics->email) {
						dispatch(new SendAppointmentToDoctorJob($record)); //add mail to queue
					}

					$ids = $appointment->diagnostics->manager()->pluck('id');
					$ids[] = $appointment->diagnostics_id;
					$input = [];
					foreach ($ids as $id) {
						$input[] = [
							'sender_id' => Auth::id(),
							'receiver_id' => $id,
							'title' => "Appointment Booked ",
							'type' => "book_appointment",
							'message' => ucfirst(Auth::user()->name) . " has booked an appointment with you on " . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ".",
						];
					}
					Notification::insert($input);


					//start wallet management
					$wallet = [];
					$is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
						$q->where('keyword', 'admin');
					})->first();
					if ($is_admin->commission->neucrad_commission) {
						$admin_fee = $appointment->payment->amount * ($is_admin->commission->neucrad_commission / 100);

						if ($appointment->practice->added_by) {
							$payable_commission = (100 - $is_admin->commission->neucrad_commission) / 100;
							$payable_amount = $appointment->payment->amount * $payable_commission;
							$wallet[] = [
								'user_id' => $appointment->practice->added_by,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $payable_amount,
							];
						}

						if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1 && $appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
							$wallet[] = [
								'user_id' => $appointment->patient->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
							];
							$wallet[] = [
								'user_id' => $appointment->practice->addedBy->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
							];

							$admin_commission = 100 - ($is_admin->commission->other_agent + $is_admin->commission->patient_agent);
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($admin_commission / 100),
							];
						} else if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1) {
							$wallet[] = [
								'user_id' => $appointment->patient->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
							];
							$admin_commission = 100 - $is_admin->commission->patient_agent;
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($admin_commission / 100),
							];
						} else if ($appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
							$wallet[] = [
								'user_id' => $appointment->practice->addedBy->referrer_id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
							];
							$admin_commission = 100 - $is_admin->commission->other_agent;
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee * ($admin_commission / 100),
							];
						} else {
							$wallet[] = [
								'user_id' => $is_admin->id,
								'appointment_id' => $appointment->id,
								'payment_id' => $appointment->payment_id,
								'price' => $admin_fee,
							];
						}

						UserWallet::insert($wallet);
					}
					//end wallet management

					DB::commit();
					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'Your appointment book successfully'
					];
				}
			} catch (SignatureVerificationError $e) {
				DB::rollBack();
				$this->status = 401;
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => 'Your payment has been failed.', "result" => $e->getMessage()];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}


	//get appointment list
	public function getAppointments(Request $request)
	{
		$rules = [
			'appointment_status' => 'required|in:create,completed,cancelled',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$appointments = Appointment::with(['practice' => function ($obj) {
					$obj->select(['id', 'name', 'locality', 'added_by', 'latitude', 'longitude']);
				}, 'doctor' => function ($obj) {
					$obj->select(['id', 'name']);
				}, 'diagnostics' => function ($obj) {
					$obj->select(['id', 'name']);
				}, 'cancelled' => function ($obj) {
					$obj->select(['id', 'name']);
				}])->where('patient_id', Auth::id())->where('status', $request->get('appointment_status'))->orderBy('id', 'DESC')->paginate($this->num_per_page);
				$data['total'] = $appointments->total();
				$appointments =  $appointments->items();
				if (!empty($appointments)) {
					foreach ($appointments as $key => $appointment) {
						$appointment->type = $appointment->type($appointment->id);
					}
				}
				$data['appointments'] = $appointments;

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Your appointment List',
					'data' => $data
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage appointment
	public function manageAppointment(Request $request)
	{
		$rules = [
			'id' => 'required|exists:appointments,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$appointment = Appointment::find($request->get('id'));
				if ($appointment->status != 'cancelled') {
					$startTimeWithTimeZone = Carbon::now(Auth::user()->timezone)->addMinute(60);
					if ($startTimeWithTimeZone < $appointment->start_time) {
						if ($appointment->payment->payment_id) {
							$refundParameters = [
								'amount' => $appointment->payment->amount * 100,
								'speed' => 'optimum'
							];
							$payment = $this->api->payment->fetch($appointment->payment->payment_id);
							$refund = $payment->refund($refundParameters);
							if ($refund) {
								$appointment->update([
									'status' => 'cancelled',
									'cancelled_by' => Auth::id()
								]);
								Payment::where('id', $appointment->payment_id)->update([
									'status' => 'refunded',
									'refunded_date' => Carbon::createFromTimestamp($refund->created_at)->format('Y-m-d'),
								]);
							}

							if (isset($appointment->doctor_id)) {
								$receiver_id = ($appointment->doctor_id == Auth::id()) ? $appointment->patient_id : $appointment->doctor_id;
								$notify_message = ($appointment->doctor_id == Auth::id()) ? 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled. Payment will be credited to your account within 5 to 7 working days' : 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled.';
								$type = ($appointment->doctor_id == Auth::id()) ? 'cancelled_appointment_by_doctor' : 'cancelled_appointment_by_patient';
							} else {
								$receiver_id = ($appointment->diagnostics_id == Auth::id()) ? $appointment->patient_id : $appointment->diagnostics_id;
								$notify_message = ($appointment->diagnostics_id == Auth::id()) ? 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled. Payment will be credited to your account within 5 to 7 working days' : 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled.';
								$type = ($appointment->diagnostics_id == Auth::id()) ? 'cancelled_appointment_by_diagnostics' : 'cancelled_appointment_by_patient';
							}

							$data = [
								'sender_id' => Auth::id(),
								'receiver_id' => $receiver_id,
								'title' => 'Cancelled Appointment',
								'type' => $type,
								'message' => $notify_message,
							];
							Notification::create($data);

							if (isset($appointment->doctor_id)) {
								$receiver = ($appointment->doctor_id == Auth::id()) ? $appointment->patient : $appointment->doctor;
							} else {
								$receiver = ($appointment->diagnostics_id == Auth::id()) ? $appointment->patient : $appointment->diagnostics;
							}

							if ($receiver->email) {
								$mailInfo = ([
									'recipient_email' => $receiver->email,
									'recipient_name' => $receiver->name,
									'title' => 'Your appointment has been cancelled.',
									'subject' => '(Appointment Cancellation) ' . Auth::user()->name . ' on ' . date('d M, Y h:i a', strtotime($appointment->start_time)) . ' EST',
									'content' => 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('d M, Y h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled.',
									'reason' => '',
									'sender_name' => Auth::user()->name,
								]);
								dispatch(new CancelAppointmentJob($mailInfo)); //add mail to queue
							}
							if (isset($appointment->doctor_id)) {
								/* start notification*/
								//send notification to app 
								$androidToken = UserApp::where('user_id', $receiver_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();
								$userType = ($appointment->doctor_id == Auth::id()) ? 'patient' : 'doctor';

								if (!empty($androidToken)) {
									$subject = 'Cancelled Appointment';
									$extra = ['id' => $appointment->patient_id, 'type' => $type, 'user_type' => $userType, 'appointment_status' => 'cancelled'];
									$sms_push_text = $notify_message;
									$this->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
								}
								/* end notification */
							}

							UserWallet::where('appointment_id', $appointment->id)->update(['status' => 'refunded']);
							UserWallet::where('appointment_id', $appointment->id)->delete();

							DB::commit();
							$result = [
								'status' => $this->success_name,
								'code' => $this->success_code,
								'message' => "Appointment has been cancelled.",
							];
						} else {
							DB::rollBack();
							$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => "Your Payment not available."];
						}
					} else {
						DB::rollBack();
						$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => "You can not cancel your appointment."];
					}
				} else {
					DB::rollBack();
					$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => "Your Payment was already refunded."];
				}
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get payment history
	public function getPaymentHistory(Request $request)
	{
		$rules = [
			'type' => 'required|in:pay,received,all',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$payments = Payment::select('id', 'amount', 'txn_date', 'receipt_id')->with(['appointment' => function ($q) {
					$q->select('id', 'payment_id', 'doctor_id', 'diagnostics_id', 'patient_name');
					$q->with(['doctor' => function ($d) {
						$d->select('name', 'id');
					}, 'diagnostics' => function ($d) {
						$d->select('name', 'id');
					}]);
				}]);
				if ($request->get('type') == 'pay') {
					$payments = $payments->where('user_id', Auth::id())->orderBy('id', 'DESC')->paginate($this->num_per_page);
				} elseif ($request->get('type') == 'received') {
					$payments = $payments->whereHas('appointment', function ($q) {
						$q->where('doctor_id', Auth::id());
					})->orderBy('id', 'DESC')->paginate($this->num_per_page);
				} else {
					$payments = $payments->whereHas('appointment', function ($q) {
						$q->where('doctor_id', Auth::id());
					})->orWhere('user_id', Auth::id())->orderBy('id', 'DESC')->paginate($this->num_per_page);
				}
				$data['total'] = $payments->total();
				$payments =  $payments->items();
				if ($payments) {
					foreach ($payments as $p) {
						$p->invoice = '';
						$payment = Payment::find($p->id);
						if (!empty($payment) && !empty($payment->invoice_id)) {

							$invoice = new Invoice();
							$invoice_filename = 'invoice_' . $payment->invoice_id . '.pdf';
							// File::exists($myfile);
							$invoice_filepath = storage_path('app/invoice/' . $invoice_filename);
							if (File::exists($invoice_filepath)) {
								$invoice_url = url('storage/app/invoice/') . '/' . $invoice_filename;
							} else {
								// p($payment->appointment);
								if (!empty($payment->appointment)) {
									$price = $payment->amount - ($payment->amount * 0.18);
									$gst = $payment->amount  - $price;
									$data = [
										'appointment' => $payment->appointment,
										'price' => $price,
										'gst' => $gst
									];
									$output = $invoice->generate('front.invoice.book_appointment', $data);
								}
								Storage::put('invoice/' . $invoice_filename, $output);
								$invoice_filepath = storage_path('app/invoice/' . $invoice_filename);
								$invoice_url = url('storage/app/invoice/') . '/' . $invoice_filename;
							}

							$p->invoice = $invoice_url;
							$p->symbol = "₹";
						}
					}
				}

				$data['payments'] =  $payments;

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Your payment list.',
					'data' => $data

				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage Medical Record (add,update,delete)
	public function manageMedicalRecord(Request $request)
	{
		$rules = [
			'action' => 'required|in:add,edit,delete,delete_record_file',
		];
		if ($request->get('action') == 'add') {
			$rules += [
				'title' => 'required',
				'record_for' => 'required',
				'record_date' => 'required|date',
				'type' => 'required|in:Report,Prescription,Invoice',
				'images' => 'required',
				'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
			];
		}
		if ($request->get('action') == 'edit') {
			$rules  += [
				'id' => 'required|exists:medical_records,id',
				'title' => 'required',
				'record_for' => 'required',
				'record_date' => 'required|date',
				'type' => 'required|in:Report,Prescription,Invoice',
				'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
			];
		}
		if ($request->get('action') == 'delete') {
			$rules += [
				'id' => 'required|exists:medical_records,id',
			];
		}
		if ($request->get('action') == 'delete_record_file') {
			$rules += [
				'id' => 'required|exists:medical_record_files,id',
			];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				if ($input['action'] == 'add') {
					$input['added_by'] = Auth::id();
					$medical_record = MedicalRecord::create($input);
					if ($medical_record) {
						$images = [];
						if ($request->hasFile('images')) {
							foreach ($request->file('images') as $key => $file) {
								$filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
								Image::make($file)->save(storage_path('app/medical-record-files/' . $filename));
								$images[] = [
									'record_id' => $medical_record->id,
									'filename' => $filename
								];
							}
							MedicalRecordFile::insert($images);
						}
					}
					$message = "Record created";
				}
				if ($input['action'] == 'edit') {
					$input['added_by'] = Auth::id();
					$medical_record = MedicalRecord::find($input['id']);
					if ($medical_record) {
						$medical_record->update($input);
						$images = [];
						if ($request->hasFile('images')) {
							foreach ($request->file('images') as $key => $file) {
								$filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
								Image::make($file)->save(storage_path('app/medical-record-files/' . $filename));
								$images[] = [
									'record_id' => $medical_record->id,
									'filename' => $filename
								];
							}
							MedicalRecordFile::insert($images);
						}
						$message = "Record Updated";
					}
				}
				if ($input['action'] == 'delete') {
					$medical_record = MedicalRecord::find($input['id']);
					if ($medical_record) {
						if ($medical_record->files) {
							foreach ($medical_record->files as $key => $file) {
								$file_path = storage_path('app/medical-record-files/' . $file->filename_value);
								@unlink($file_path);
								$file->delete();
							}
						}
						$medical_record->delete();
						$message = "Record Deleted";
					}
				}
				if ($input['action'] == 'delete_record_file') {
					$medical_record_file = MedicalRecordFile::find($input['id']);
					if ($medical_record_file) {
						$file_path = storage_path('app/medical-record-files/' . $medical_record_file->filename_value);
						@unlink($file_path);
						$medical_record_file->delete();
						$message = "Record File Deleted";
					}
				}
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message,
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//Get MedicalRecord
	public function getMedicalRecord(Request $request)
	{
		$rules = [
			'id' => 'required|exists:users,id',
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$records = MedicalRecord::with('files')->where('added_by', $request->get('id'))->paginate($this->num_per_page);
				$data['total'] = $records->total();
				$data['records'] =  $records->items();;
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => "medical record list",
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get Patient Appointment
	public function getPatientAppointment(Request $request)
	{
		$rules = [
			'patient_id' => 'exists:users,id',
			'appointment_status' => 'required|in:create,completed,cancelled',
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$appointments = Appointment::with(['practice' => function ($obj) {
					$obj->select(['id', 'name', 'locality', 'latitude', 'longitude']);
				}, 'doctor' => function ($obj) {
					$obj->select(['id', 'name']);
				}])->where('doctor_id', Auth::id())->WhereNotIn('status', ['pending'])->where('status', $request->get('appointment_status'));

				if ($request->get('patient_id')) {
					$appointments = $appointments->where('patient_id', $request->get('patient_id'));
				}
				$appointments = $appointments->orderBy('id', 'DESC')->paginate($this->num_per_page);
				$data['total'] = $appointments->total();
				$appointments =  $appointments->items();
				if (!empty($appointments)) {
					foreach ($appointments as $key => $appointment) {
						$appointment->type = $appointment->type($appointment->id);
					}
				}
				$data['appointments'] = $appointments;
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Your appointment List',
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get Patient list
	public function myPatient(Request $request)
	{
		try {
			$patients = User::whereHas('appointment', function ($q) {
				$q->where('doctor_id', Auth::id());
				$q->where('status', 'completed');
			})->orderBy('id', 'DESC')->paginate($this->num_per_page);

			$data['total'] = $patients->total();
			$data['patients'] =  $patients->items();
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Your patients List',
				'data' => $data
			];
		} catch (Exception $e) {
			DB::rollBack();
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//get degree list
	public function getDegree(Request $request)
	{
		try {
			$degrees = array();
			foreach (config('view.Degree') as $key => $value) {
				$degrees[] = $value;
			}
			$data['degrees'] = $degrees;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Degree List',
				'data' => $data
			];
		} catch (Exception $e) {
			DB::rollBack();
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//get drugs type list
	public function getDrugsType(Request $request)
	{
		try {
			$Drug_Type = array();
			foreach (config('view.Drug_Type') as $key => $value) {
				$Drug_Type[] = $value;
			}
			$data['drugTypes'] = $Drug_Type;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Drugs Type List',
				'data' => $data
			];
		} catch (Exception $e) {
			DB::rollBack();
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//get drugs unit list
	public function getDosageUnit(Request $request)
	{
		try {
			$Dosage_Unit = array();
			foreach (config('view.Dosage_Unit') as $key => $value) {
				$Dosage_Unit[] = $value;
			}
			$data['dosageUnits'] = $Dosage_Unit;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Dosage Unit List',
				'data' => $data
			];
		} catch (Exception $e) {
			DB::rollBack();
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//manage Appointment prescription
	public function managePrescription(Request $request)
	{
		$rules = ['action' => 'required|in:add,edit,delete'];
		if ($request->get('action') == 'add') {
			$rules += [
				'appointment_id' => 'required|exists:appointments,id',
				'drug' => 'required',
			];
		}
		if ($request->get('action') == 'add' || $request->get('action') == 'edit') {
			$rules += [
				'frequency' => 'required',
				'intake' => 'required',
				'duration' => 'required|integer',
			];
		}
		if ($request->get('action') == 'edit' || $request->get('action') == 'delete') {
			$rules += [
				'id' => 'required|exists:appointment_prescriptions,id',
			];
		}
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$input = $request->all();
				DB::beginTransaction();
				if ($request->get('id')) {
					$prescription = AppointmentPrescription::find($request->get('id'));
					if ($request->get('action') == 'edit') {
						$prescription->update($input);
						$message = "prescription update successfully";
						$title = "Prescription updated";
						$content = Auth::user()->name . " has been edit the prescription. please check it.";
					} else {
						$prescription->delete();
						$message = "prescription delete successfully";
					}
				} else {
					AppointmentPrescription::create($input);
					$message = "prescription added successfully";
					$title = "New Prescription Added";
					$content = Auth::user()->name . " has been uploaded the new prescription. please check it.";
				}

				if ($request->get('action') == 'add' || $request->get('action') == 'edit') {
					$appointment = Appointment::find($request->get('appointment_id'));
					Notification::create([
						'sender_id' => Auth::id(),
						'receiver_id' => $appointment->patient_id,
						'title' => $title,
						'type' => 'upload_prescription',
						'message' => $content
					]);

					$androidToken = UserApp::where('user_id', $appointment->patient_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();
					if (!empty($androidToken)) {
						$subject = $title;
						$sms_push_text = $content;
						$extra = ['id' => $appointment->id, 'type' => 'upload_prescription', 'appointment_status' => 'completed'];

						$this->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
					}
				}

				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage appointment File
	public function manageAppointmentFile(Request $request)
	{
		$rules = ['action' => 'required|in:add,delete',];
		if ($request->get('action') == 'add') {
			$rules += [
				'appointment_id' => 'required|exists:appointments,id',
				'images' => 'required',
				'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg'
			];
		}
		if ($request->get('action') == 'delete') {
			$rules += [
				'id' => 'required|exists:appointment_files,id',
			];
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				if ($input['action'] == 'add') {
					$images = [];
					if ($request->hasFile('images')) {
						foreach ($request->file('images') as $key => $file) {
							$filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
							Image::make($file)->save(storage_path('app/appointment_prescription_file/' . $filename));
							$images[] = [
								'appointment_id' => $input['appointment_id'],
								'filename' => $filename
							];
						}
						AppointmentFile::insert($images);
						$message = "file uploaded successfully";

						$appointment = Appointment::find($input['appointment_id']);

						Notification::create([
							'sender_id' => Auth::id(),
							'receiver_id' => $appointment->patient_id,
							'title' => Auth::user()->name . " has been uploaded the files. please check it",
							'type' => 'upload_file',
							'message' => Auth::user()->name . " has been uploaded the files. please check it."
						]);

						$androidToken = UserApp::where('user_id', $appointment->patient_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

						if (!empty($androidToken)) {
							$subject = "New File Upload";
							$sms_push_text = Auth::user()->name . " has been uploaded the files. please check it";
							$extra = ['id' => $appointment->id, 'type' => 'upload_file', 'appointment_status' => 'completed'];

							$this->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
						}
					}
				}
				if ($input['action'] == 'delete') {
					$appointment_file = AppointmentFile::find($input['id']);
					if ($appointment_file) {
						$file_path = storage_path('app/appointment_prescription_file/' . $appointment_file->filename_value);
						@unlink($file_path);
						$appointment_file->delete();
						$message = "File Deleted";
					}
				}
				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message,
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//manage appointment File
	public function getAppointmentDetail(Request $request)
	{
		$rules = [
			'id' => 'required|exists:appointments,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				//get allocated pharmacy details
				$share_pharmacy = SharePrescription::where('appointment_id', $request->id)->first();
				$pharmacy = isset($share_pharmacy) ? 'Recommended pharmacy is ' . $share_pharmacy->pharmacy->name . ', ' . $share_pharmacy->pharmacy->address . ', ' . $share_pharmacy->pharmacy->locality : null;

				//get Appointment details
				$appointment = Appointment::with(['practice' => function ($obj) {
					$obj->select(['id', 'name', 'locality']);
				}, 'prescriptions', 'files' => function ($obj) {
					$obj->select(['id', 'appointment_id', 'filename']);
				}])->where('id', $request->id)->get()->map(function ($object) use ($pharmacy) {
					$object->services = isset($object['services_ids']) ? DiagnosticsService::whereIn('id', stringToArray($object['services_ids']))->get() : null;
					$object->pharmacy_name = $pharmacy;
					return $object;
				});

				$data['appointments'] = $appointment;

				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'appointment data',
					'data' => $data
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get timezone list
	public function getTimezone()
	{
		try {
			//$data['timezones'] = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
			$timezones =  Timezone::getTimezones();
			$list = [];
			foreach ($timezones as $key => $value) {
				$list[] = ['key' => $value, 'value' => $key];
			}
			$data['timezones'] = $list;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'timezone list',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//get frequency list
	public function getFrequency()
	{
		try {
			foreach (config('view.Frequency') as $key => $value) {
				$Frequency[] = $value;
			}
			$data['frequencies'] = $Frequency;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'frequencies List',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//get chats list
	public function getRecentChat()
	{
		try {
			$chats = Chat::with(['sender' => function ($obj) {
				$obj->select(['id', 'name', 'profile_picture']);
			}, 'recipient' => function ($obj) {
				$obj->select(['id', 'name', 'profile_picture']);
			}])->where('sender_id', Auth::id())->orWhere('recipient_id', Auth::id())->get();
			$recent_records = [];
			foreach ($chats as $c) {
				if ($c->sender_id == Auth::id()) {
					$c->recipient->chat_id = $c->id;
					$recent_records[] = $c->recipient;
				} else {
					$c->sender->chat_id = $c->id;
					$recent_records[] = $c->sender;
				}
			}
			$data['recent_records'] = $recent_records;
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Recent Record List',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	public function openChat(Request $request)
	{
		$rules = [
			'receiver_id' => 'required|exists:users,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$chat = Chat::where('sender_id', Auth::id())->where('recipient_id', $request->get('receiver_id'))
					->orWhere('sender_id', $request->get('receiver_id'))->where('recipient_id', Auth::id())
					->get();
				if (!$chat->isEmpty()) {
					$chat_id = $chat->first()->id;
				} else {
					$chat = Chat::create(['sender_id' => Auth::id(), 'recipient_id' => $request->get('receiver_id')]);
					$chat_id = $chat->id;
				}
				$currentTime = Carbon::now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');

				$receiver_id = $request->get('receiver_id');
				$appointment = Appointment::Where(function ($query) use ($receiver_id) {
					$query->where('patient_id', Auth::id())->where('doctor_id', $receiver_id)
						->orWhere('patient_id', $receiver_id)->where('doctor_id', Auth::id());
				})->where('date', today())
					->where('start_time', '<=', $currentTime)
					->where('end_time', '>=', $currentTime)
					->WhereNotIn('status', ['pending', 'cancelled'])
					->first();

				$user = User::select('id', 'name', 'profile_picture')->where('id', $request->get('receiver_id'))->first();
				$androidToken = UserApp::where('user_id', $request->get('receiver_id'))->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

				if ($appointment) {
					$startTime = Carbon::parse($currentTime);
					$endTime = Carbon::parse($appointment->end_time);
					$totalSecond = $startTime->diff($endTime)->format('%s');
					$totalMinute = $startTime->diff($endTime)->format('%I');
					$data = ['chat_id' => $chat_id, 'is_appointment_available' => 1, 'totalSecond' => $totalSecond, 'totalMinute' => $totalMinute, 'name' => $user->name, 'profile_picture' => $user->profile_picture, 'firebase_token' => $androidToken];
				} else {
					$data = ['chat_id' => $chat_id, 'is_appointment_available' => 0, 'name' => $user->name, 'profile_picture' => $user->profile_picture, 'firebase_token' => $androidToken];
				}

				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'data load successfully',
					'data' => $data
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	public function notificationLog()
	{
		try {
			$notification = Notification::select('id', 'sender_id', 'receiver_id', 'title', 'type', 'message', 'is_read', 'created_at', 'action_id')->where('receiver_id', Auth::id())->orderBy('id', 'DESC')->paginate($this->num_per_page);
			$data['total'] = $notification->total();
			$data['notifications'] = $notification->items();
			Notification::where('receiver_id', Auth::id())->update(['is_read' => '1']);
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Notification List',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	//staff invitation reply by doctor
	public function invitationReply(Request $request)
	{
		$rules = [
			'action' => 'required|in:1,0',
			'action_id' => 'required|exists:staff_manager,id',
			'notification_id' => 'required|exists:notifications,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				/* accept invitation accept = 1 ,reject = 0 */
				if ($request->get('action') == 1) {
					$practice = PracticeManager::where('staff_id', $request->get('action_id'))->first();
					$practice->update(['status' => 1]);
					Notification::find($request->get('notification_id'))->delete();
					Notification::create([
						'sender_id' => Auth::id(),
						'receiver_id' => $practice->added_by,
						'title' => "" . Auth::user()->name . " has Accept your Invitation as Staff",
						'type' => 'staff_invitation_accept',
						'message' => 'Your invitation accepted by ' . Auth::user()->name . '. so please add doctor everyday schedule.'
					]);
					$message = "Invitation Accept";

					if ($practice->addedBy->email) {
						$record = [
							'subject' => 'Invitation Accept As Staff',
							'recipient_name' => $practice->addedBy->name,
							'recipient_email' => $practice->addedBy->email,
							'content' => "Your invitation accepted by " . Auth::user()->name . ". so please add doctor everyday schedule. 
	
							Regards,                            
							" . Auth::user()->name . " & Team!",
							'title' => 'Inviatation Accepted',
						];

						dispatch(new StaffApprovalMailJob($record));
					}
				} else {
					$practice = PracticeManager::where('staff_id', $request->get('action_id'))->first();
					$practice->delete();
					StaffManager::where('id', $request->get('action_id'))->delete();
					Notification::find($request->get('notification_id'))->delete();
					Notification::create([
						'sender_id' => Auth::id(),
						'receiver_id' => $practice->added_by,
						'title' => "" . Auth::user()->name . " has Reject your Invitation as Staff",
						'type' => 'staff_invitation_reject',
						'message' => 'Your invitation rejected by ' . Auth::user()->name . '. please contact to doctor if have any concern.'
					]);
					$message = "Invitation Reject";

					if ($practice->addedBy->email) {
						$record = [
							'subject' => 'Invitation Reject As Staff',
							'recipient_name' => $practice->addedBy->name,
							'recipient_email' => $practice->addedBy->email,
							'content' => "Your invitation rejected by " . Auth::user()->name . ". please contact to doctor if have any concern. 
	
							Regards,                            
							" . Auth::user()->name . " & Team!",
							'title' => 'Invitation Rejected',
						];

						dispatch(new StaffApprovalMailJob($record));
					}
				}
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => $message
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	//get Calender data
	public function getCalenderData(Request $request)
	{
		$rules = [
			'start_date' => 'required|before:end_date|date_format:Y-m-d',
			'end_date' => 'required|after:start_date|date_format:Y-m-d',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {

				$appointments = Appointment::select('id', 'patient_name', 'patient_phone', 'patient_email', 'start_time', 'end_time', 'appointment_type', 'status', 'doctor_id', 'practice_id')->with(['doctor' => function ($q) {
					$q->select('id', 'name');
				}, 'practice' => function ($q) {
					$q->select('id', 'name');
				}])->where('doctor_id', Auth::id())->whereNotIn('status', ['pending', 'cancelled'])->whereBetween('date', [$request->start_date, $request->end_date])->get();

				if (!empty($appointments)) {
					foreach ($appointments as $key => $appointment) {
						$appointment->type = $appointment->type($appointment->id);
					}
				}

				$data['appointments'] = $appointments;
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => "calender data",
					'data' => $data,
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	public function verifyAgentProfile(Request $request)
	{
		$rules = [
			'identity_document' => 'image|mimes:jpeg,png,jpg,gif,svg',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$user = Auth::user();
				$data = [];
				if ($request->has('identity_document') && !empty($request->file('identity_document'))) {

					$identity_document = $request->file('identity_document');
					$filename = time() . uniqId() . '.' . $identity_document->getClientOriginalExtension();
					Image::make($identity_document)->fit(500, 500, function ($constraint) {
						$constraint->upsize();
					})->save(storage_path('app/document/' . $filename));
					$data['identity_proof'] = $filename;

					/*remove the existing profile picture*/
					$identity_path = storage_path('app/document/' . $user->detail->identity_proof_name);
					if ($user->detail->identity_proof_name != "no_image.png") {
						@unlink($identity_path);
					}
				}

				$user->detail()->update($data);
				$user->update(['as_agent_verified' => '1']);

				//get super admin id
				$is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
					$q->where('keyword', 'admin');
				})->first();

				$data = [
					'sender_id' => Auth::id(),
					'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
					'title' => 'User Request For Agent Profile Approval',
					'type' => 'agent_profile_verification',
					'message' => Auth::user()->name . ' has requested for approval profile as a agent. please verify the details.',
				];

				Notification::create($data);

				if ($is_admin->email) {
					$mailInfo = ([
						'receiver_email' => $is_admin->email,
						'receiver_name' => 'NC Health HUB',
						'title' => '',
						'subject' => 'Apply Profile As Agent ' . $user->name,
						'content' => 'I would like to request approval for my profile as a agent. I have uploaded all the details and documents.<br>
                        Please click on the button to see all details.<br>
                        Please, let me know if any concerns about the same.<br>
                        <br>
                        <br>
                        Thanks.',

					]);
					dispatch(new AgentProfileVerificationJob($mailInfo)); //add mail to queue
				}

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => "Your Documents uploaded successfully.",
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	public function getPharmacy()
	{
		try {
			$data['pharmacies'] = User::select('id', 'name')->whereHas('role', function ($p) {
				$p->where('keyword', 'pharmacy');
			})->get();
			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'Pharmacy List',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	public function sendInvitation(Request $request)
	{
		$rules = [
			'subject' => 'required',
			'recipient_email' => 'required|unique:users,email',
			'recipient_phone' => 'required|unique:users,phone',
			'content' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$input = $request->all();
				$input['sender_user'] = Auth::user()->name;
				$input['referral_link'] = Auth::user()->referral_link;
				$input['referral_code'] = Auth::user()->referral_code;

				if (Auth::user()->is_bank_verified == 2) {
					dispatch(new SendRefferalInvite($input));

					$result = [
						'status' => $this->success_name,
						'code' => $this->success_code,
						'message' => 'Send Referral Invitation Successfully.',
						'data' => $input
					];
				} else {
					$result = [
						'status' => $this->error_name,
						'code' => $this->error_code,
						'message' => 'Your bank account details not verified.'
					];
				}
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}

		return Response::json($result);
	}

	public function sendPrescription(Request $request)
	{
		$rules = [
			'appointment_id' => 'required|exists:appointments,id',
			'pharmacy_id' => 'required|exists:users,id',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				DB::beginTransaction();
				$input = $request->all();
				$appointment = Appointment::find($request->get('appointment_id'));
				$input['patient_id'] = $appointment->patient_id;
				$input['doctor_id'] = $appointment->doctor_id;
				$share_pharmacy = SharePrescription::create($input);
				$data['pharmacy_name'] = 'Recommanded pharmacy is ' . $share_pharmacy->pharmacy->name . ', ' . $share_pharmacy->pharmacy->address . ', ' . $share_pharmacy->pharmacy->locality;
				//send to patient
				Notification::create([
					'sender_id' => Auth::id(),
					'receiver_id' => $appointment->patient_id,
					'title' => Auth::user()->name . " has been send your prescription to " . $share_pharmacy->pharmacy->name . ".",
					'type' => 'share_prescription',
					'message' => Auth::user()->name . " has been the prescription to " . $share_pharmacy->pharmacy->name . ". Your prescription id was <strong>#" . $share_pharmacy->id . "</strong>. Pharmacy address was " . $share_pharmacy->pharmacy->address . "," . $share_pharmacy->pharmacy->locality,
				]);

				$androidToken = UserApp::where('user_id', $appointment->patient_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

				if (!empty($androidToken)) {
					$subject = "Send Prescription To Pharmacy";
					$sms_push_text = Auth::user()->name . " has been send your prescription to " . $share_pharmacy->pharmacy->name . ". Your prescription id was <strong>#" . $share_pharmacy->id . "</strong>. Pharmacy address was " . $share_pharmacy->pharmacy->address . "," . $share_pharmacy->pharmacy->locality . ".";
					$extra = ['id' => $appointment->id, 'type' => 'share_prescription', 'appointment_status' => 'completed'];

					$this->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
				}

				//send to pharmacy
				Notification::create([
					'sender_id' => Auth::id(),
					'receiver_id' => $input['pharmacy_id'],
					'title' => Auth::user()->name . " has been send the prescription of " . $share_pharmacy->patient->name . ".",
					'type' => 'share_prescription',
					'message' => Auth::user()->name . " has been send the prescription of " . $share_pharmacy->patient->name . ". The prescription id was <strong>#" . $share_pharmacy->id . "</strong>.",
				]);

				DB::commit();
				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Prescription send Successfully..',
					'data' => $data
				];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	public function myReferralUsers()
	{
		try {
			$users = User::where(['referrer_id' => Auth::id()])->orderBy('id', 'DESC')->paginate($this->num_per_page);
			$data['total'] = $users->total();
			$data['users'] = $users->items();

			$result = [
				'status' => $this->success_name,
				'code' => $this->success_code,
				'message' => 'user List',
				'data' => $data
			];
		} catch (Exception $e) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
		}
		return Response::json($result);
	}

	public function addBankDetails(Request $request)
	{

		$rules = [
			'bank_name'  => 'required',
			'account_number'  => 'required',
			'ifsc_code'  => 'required',
			'beneficiary_name'  => 'required|regex:/^[a-zA-Z]+$/u',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$input = $request->all();
				$input['user_id'] = Auth::id();
				$user = Auth::user();
				$data = [];
				$user_account = UserBankAccount::updateOrCreate(['user_id' => $input['user_id']], $input);
				$user->update(['is_bank_verified' => 1]);

				//get super admin id
				$is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
					$q->where('keyword', 'admin');
				})->first();

				$data = [
					'sender_id' => Auth::id(),
					'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
					'title' => 'User Request For Bank Account Approval',
					'type' => 'bank_account_verification',
					'message' => Auth::user()->name . ' has requested for approval bank account details. please verify the details.',
				];

				Notification::create($data);

				if ($is_admin->email) {
					$mailInfo = ([
						'receiver_email' => $is_admin->email,
						'receiver_name' => 'NC Health HUB',
						'title' => '',
						'subject' => 'Apply For Verification Of Bank Details',
						'content' => 'I would like to request approval for my bank account details. I have uploaded all the details.<br>
                         Please click on the button to see all details.<br>
                         Please, let me know if any concerns about the same.<br>
                         <br>
                         <br>
                         Thanks.',

					]);
					dispatch(new BankAccountVerificationJob($mailInfo)); //add mail to queue
				}

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Your Bank Details Submitted Successfully'
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	public function userInquiry(Request $request)
	{
		$rules = [
			'name' => 'required',
			'email' => 'required|email',
			'subject' => 'required',
			'message' => 'required'
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {

				$record = [
					'subject' => $request->get('subject'),
					'name' => $request->get('name'),
					'email' => $request->get('email'),
					'content' => $request->get('message'),
				];

				/* get admin email */
				$record['admin_email'] = User::whereHas('role', function ($a) {
					$a->where('keyword', 'admin');
				})->pluck('email')->first();

				dispatch(new SendInquiryJob($record));

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Mail Send Successfully'
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	public function walletHistory(Request $request)
	{
		$rules = [
			'type' => 'required|in:wallet_history,withdraw_history',
		];

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error_name, 'code' => $this->error_code, 'message' => $validator->errors()->first()];
		} else {
			try {
				$data['total_balance'] = UserWallet::whereHas('appointment', function ($q) {
					$q->whereIn('status', ['completed', 'create']);
				})->where('user_id', [Auth::id()])->where('status', null)->sum('price');

				$data['withdrawable_balance'] = UserWallet::whereHas('appointment', function ($q) {
					$q->where('status', ['completed']);
				})->where('user_id', [Auth::id()])->where('status', null)->sum('price');

				if ($request->get('type') == 'wallet_history') {
					$userWallet = UserWallet::select('id', 'user_id', 'appointment_id', 'payment_id', 'price', 'status', 'created_at')->with(['appointment' => function ($obj) {
						$obj->select('id', 'patient_name', 'status', 'patient_id');
						$obj->with(['patient' => function ($p) {
							$p->select('id', 'profile_picture');
						}]);
					}])->where('user_id', [Auth::id()])->withTrashed()->orderBy('id', 'DESC')->paginate($this->num_per_page);
					$data['total'] = $userWallet->total();
					$wallet_data = $userWallet->items();
					$history = [];
					foreach ($wallet_data as $w) {
						$history[] = [
							"id" =>  $w->id,
							"user_id" =>  $w->user_id,
							"appointment_id" =>  $w->appointment_id,
							"payment_id" =>  $w->payment_id,
							"price" =>  $w->price,
							"status" =>  $w->status,
							"created_at" =>  $w->created_at,
							"patient_name" =>  $w->appointment->patient_name,
							"appointment_status" =>  $w->appointment->status,
							"profile_picture" =>  $w->appointment->patient->profile_picture,
						];
					}
					$data['wallet_history'] = $history;
				}

				if ($request->get('type') == 'withdraw_history') {
					$withdra_history = UserWithdrawHistory::select('id', 'transfer_id', 'amount', 'currency', 'created_at')->where('user_id', [Auth::id()])->orderBy('id', 'DESC')->paginate($this->num_per_page);
					$data['total'] = $withdra_history->total();
					$data['withdraw_history'] = $withdra_history->items();
				}

				$result = [
					'status' => $this->success_name,
					'code' => $this->success_code,
					'message' => 'Data load successfully',
					'data' => $data
				];
			} catch (Exception $e) {
				$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}

	public function withdrawBalance(Request $request)
	{
		try {
			$withdrawable_balance = UserWallet::whereHas('appointment', function ($q) {
				$q->where('status', ['completed']);
			})->where('user_id', [Auth::id()])->where('status', null)->sum('price');

			if ($withdrawable_balance > 0 && Auth::user()->account_id) {
				$input = [
					'account' => Auth::user()->account_id,
					'amount' => $withdrawable_balance * 100,
					'currency' => 'INR',
				];
				$razorpayOrder = $this->api->transfer->create($input);

				if ($razorpayOrder->id) {
					$history = [
						'user_id' => Auth::id(),
						'transfer_id' => $razorpayOrder->id,
						'source_id' => $razorpayOrder->source,
						'recipient_id' => $razorpayOrder->recipient,
						'amount' => $razorpayOrder->amount / 100,
						'currency' => $razorpayOrder->currency,
						'fee' => $razorpayOrder->fees / 100,
						'tax' => $razorpayOrder->tax / 100,
						'date' => Carbon::createFromTimestamp($razorpayOrder->created_at),
					];

					UserWithdrawHistory::create($history);
					UserWallet::whereHas('appointment', function ($q) {
						$q->where('status', ['completed']);
					})->where('user_id', [Auth::id()])->where('status', null)->update(['status' => 'received']);
					UserWallet::whereHas('appointment', function ($q) {
						$q->where('status', ['completed']);
					})->where('user_id', [Auth::id()])->where('status', 'received')->delete();

					$total_balance = UserWallet::whereHas('appointment', function ($q) {
						$q->whereIn('status', ['completed', 'create']);
					})->where('user_id', [Auth::id()])->where('status', null)->sum('price');

					$withdrawable_balance = UserWallet::whereHas('appointment', function ($q) {
						$q->where('status', ['completed']);
					})->where('user_id', [Auth::id()])->where('status', null)->sum('price');

					$result = ['status' => $this->success_name, 'code' => $this->success_code, "message" => "Wallet balance transfer successfully", 'total_balance' => $total_balance, "withdrawable_balance" => $withdrawable_balance];
				} else {
					$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $this->exception_message];
				}
			} else {
				if (!Auth::user()->account_id) {
					$message = "Please verify your bank Account";
				} else {
					$message = "You have not sufficient balance in wallet";
				}
				$result = [
					'status' => $this->error_name,
					'code' => $this->error_code,
					'message' =>  $message
				];
			}
		} catch (Exception $e) {
			if ($e->getMessage()) {
				$message = $e->getMessage();
			} else {
				$message = $this->exception_message;
			}
			$result = ['status' => $this->error_name, 'code' => $this->error_code, "message" => $message];
		}
		return Response::json($result);
	}

	//testing push notification
	public function testing(Request $request)
	{
		$androidToken = $request->token;
		$subject = $request->title;
		$sms_push_text = $request->text;
		$extra = ['id' => $request->id, 'type' => $request->click_action];
		$this->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra); /* send push notification android */
	}

	//testing push notification
	public function sendChatNotification(Request $request)
	{
		$androidToken = $request->token;
		$subject = $request->title;
		$sms_push_text = $request->text;
		$extra = ['id' => $request->sender_id, 'type' => $request->click_action];
		$this->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra); /* send push notification android */
	}

	//for send notification to mobile device
	public function sendPushNotification($type, $token, $title, $text, $extraData = [])
	{

		$extra = ['title' => $title, 'text' => $text];
		if (!empty($extraData)) {
			$extra += $extraData;
		}

		if ($type == 'android') {
			$push = new PushNotification('fcm');
			$push->setMessage([
				'notification' => [
					'title' => $title,
					'body' => $text,
					'sound' => 'default',
					'click_action' => isset($extra['type']) ? $extra['type'] : ''
				],
				'data' => $extra
			])->setDevicesToken($token)->send();
			$a = $push->getFeedback();
		}
	}
}
