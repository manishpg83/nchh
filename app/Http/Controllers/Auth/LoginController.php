<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
        if (is_numeric($request->get('email'))) {
            return ['phone' => $request->get('email'), 'password' => $request->get('password')];
        } elseif (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            return ['email' => $request->get('email'), 'password' => $request->get('password')];
        }
        return ['username' => $request->get('email'), 'password' => $request->get('password')];
    }

    public function SendLoginOTP(Request $request)
    {

        $input = ['phone' => $request->get('email')];

        $rules = ['phone' => 'required|exists:users'];


        $validator = Validator::make($input, $rules);
        if ($validator->fails() && (!$request->get('resend_otp'))) {
            $result = ['status' => $this->error, 'message' => $validator->errors()->first()];
        } else {
            try {
                DB::beginTransaction();
                $randomid = mt_rand(100000, 999999);
                if (!$request->get('resend_otp')) {
                    Session::put('OTP', ['MOBILE_OTP' => $randomid, 'MOBILE_NUMBER' => $request->get('email')]);
                } else {
                    $data = Session::get('OTP');
                    Session::put('OTP', ['MOBILE_OTP' => $randomid, 'MOBILE_NUMBER' => $data['MOBILE_NUMBER']]);
                }
                /*  $data = Session::get('OTP');
                dd($data['MOBILE_OTP']); */
                DB::commit();
                if (!$request->get('resend_otp')) {
                    $result = ['status' => $this->success, 'message' => "A verification code has been send to your phone number. Please check", 'otp' => Session::get('OTP')];
                } else {
                    $result = ["status" => $this->success, "message" => "A verification code has been send again to your phone number. Please check", 'otp' => Session::get('OTP')];
                }
            } catch (Exception $e) {
                DB::rollBack();
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    public function OTPLogin(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = Session::get('OTP');
            $user = User::where('phone', ($data['MOBILE_NUMBER']))->first();

            // Set Auth Details
            Auth::login($user);
            DB::commit();
            Session::put('panel', $user->role->keyword);
            //return redirect()->route('home');
            $result = ['status' => $this->success, 'message' => "Login successfully.", 'redirect' => "home"];
        } catch (Exception $e) {
            DB::rollBack();
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function emailVerify(Request $request)
    {
        $user_email = $request->get('email');
        $existing_users = User::where('email', $user_email)
            ->orWhere('phone', $user_email)
            ->count();

        if ($existing_users > 0) {
            return "true"; //already registered
        } else {
            return "false";  //user name is not available
        }
    }

    public function showStaffLoginForm(Request $request)
    {
        return view('auth.staff_login');
    }

    public function StaffLogin(Request $request)
    {
         $rule = [
            'username' => 'required|exists:users,username',
            'password' => 'required'
        ];
        $message = [
            'username.exists' => 'You have entered an incorrect username.'
        ];

        $this->validate($request, $rule, $message);

        $remember_me = $request->has('remember_me') ? true : false;


        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], $remember_me)) {
            $user = Auth::user();
            Auth::login($user);
            return $this->sendLoginResponse($request);
         } else {
             return Redirect::back()
             ->withInput()
             ->withErrors([
                 'password' => 'Incorrect password!'
             ]);
        }
    }

    protected function authenticated(Request $request, $user)
    {
        Auth::logoutOtherDevices(request('password'));
        Session::put('panel', $user->role->keyword);
    }
}
