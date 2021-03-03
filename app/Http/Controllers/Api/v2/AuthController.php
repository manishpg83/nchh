<?php

namespace App\Http\Controllers\Api\v2;

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
use App\OtherDetail;
use App\User;

class AuthController extends Controller
{
	public $success = 200;
	public $error = 400;
	private $exception_message = 'Something went wrong, please try again.';

    //login user
	public function login(Request $request)
	{
		$rule = [
			'email' => 'required',
			'password' => 'required',
		];
		
		if(is_numeric($request->get('email'))){
			$rule += ['email' => 'required|exists:users,phone'];
			$message = ['email.required' => 'This field is required.','email.exists' => 'The selected Mobile Number is invalid.'];
		}else{
			$rule += ['email' => 'required|email|exists:users,email'];
			$message = ['email.required' => 'This field is required.','email.exists' => 'The selected email is invalid.','email.email' => 'Enter valid email address.'];
		}
		
		$validator = Validator::make($request->all(), $rule);

		if ($validator->fails()) {
			$result = ['status' => $this->error, 'message' => $validator->errors()];
		} else {
			try {
				if (is_numeric($request->input('email'))) {
					Auth::attempt(['phone' => $request->input('email'), 'password' => $request->password], $request->get('remember'));
				} else {
					Auth::attempt(['email' => $request->input('email'), 'password' => $request->password], $request->get('remember'));
				}
				if (Auth::check()) {
					$user = Auth::user();
					$data['user'] = $user;
					$data['token']= $user->createToken($user->name)->accessToken;
					$result = ['status' => $this->success, 'message' => "Login successfully.",'result'=>$data];
					return Response::json($result); 
				}else{
					$result = ['status' => $this->error, 'message' => "Credentials don't match"];
				}
			} catch (Exception $e) {
				$result = ["status" => $this->error, "message" => $this->exception_message];
			}
		}
		return Response::json($result);
	}
	
	//get user details
	public function details()
	{
		try{
			$user = User::with('detail')->where('id', Auth::id())->get();
			$result = ['status' => $this->success, 'message' => "Data load successfully",'result'=>$user];
		}catch(Exception $e){
			$result = ["status" => $this->error, "message" => $this->exception_message];
		}
		return response()->json($result);
	}	
	
	public function getOtp(Request $request){
		$rules = [
			'phone' => 'required|string|min:10',
		];
		
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$result = ['status' => $this->error, 'message' => $validator->errors()];
		} else {
			$input = $request->all();
			try {
				DB::beginTransaction();
				$randomid = mt_rand(100000,999999);
				$data['otp'] = $randomid;
				$data['phone'] = $request->get('phone');

				DB::commit();
				$result = ['status' => $this->success, 'message' => "Sent otp successfully.",'result' => $data];
			} catch (Exception $e) {
				DB::rollBack();
				$result = ['status' => $this->error, 'message' => $this->exception_message];
			}
		}
		return Response::json($result);
	}
}
