<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Jobs\NewUserRegistrationJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Mail\ApprovalMail;
use App\Mail\RegisterUser;
use App\User;
use App\UserRole;
use App\UserDetail;
use App\Notification;
use Exception;

class RegisterController extends Controller
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    public function showRegistrationForm(Request $request)
    {
        if ($request->has('ref')) {
            session(['referrer' => $request->query('ref')]);
        }

        $data['roles'] = UserRole::whereIn('keyword', ['patient', 'doctor', 'pharmacy', 'hospital', 'clinic', 'agent', 'diagnostics'])->pluck('name', 'id')->toArray();
        return view('auth.register')->with($data);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'phone' => 'required|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required',
            'dialcode' => 'required'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(Request $request)
    {
        $input = $request->all();
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required',
            'dialcode' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                DB::beginTransaction();

                if (session()->pull('referrer')) {
                    $referrer = User::Where('referral_code', session()->pull('referrer'))->first();
                    $input['referrer_id'] = $referrer ? $referrer->id : null;
                } 

                if ($request->get('password')) {
                    $input['password'] = Hash::make($request->get('password'));
                }

                /* if (UserRole::where(['id' => $request->get('role_id'), 'keyword' => 'agent'])->exists()) {
                } */

                $input['referral_code'] = passwordGenerate(6);

                $user = User::create($input);

                if ($user) {
                    UserDetail::updateOrCreate(['user_id' => $user->id], $input);

                    /*get super admin id*/
                    $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                        $q->where('keyword', 'admin');
                    })->first();

                    $data = [
                        'sender_id' => $user->id,
                        'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
                        'title' => $user->name . ' has joined as a ' . $user->role->keyword . ' in NC Health Hub. Phone Number : ' . $user->phone_with_dialcode,
                        'type' => 'user_register',
                        'message' => 'New user registration.'
                    ];

                    Notification::create($data);

                    if ($is_admin->email) {
                        $mailInfo = ([
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone_with_dialcode,
                            'admin_email' => $is_admin->email,
                            'role' => $user->role->keyword
                        ]);
                        dispatch(new NewUserRegistrationJob($mailInfo)); //add mail to queue
                    }

                    $this->guard()->login($user);
                    Session::put('panel', $user->role->keyword);
                    DB::commit();
                    $result = ["status" => $this->error, "message" => "User register successfully.", "result" => $user];
                } else {
                    DB::rollBack();
                    $result = ['status' => $this->error, 'message' => $this->exception_message];
                }
            } catch (Exception $e) {
                DB::rollBack();
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    public function getOTP(Request $request)
    {
        $input = [
            'name' => $request->get('name'),
            'phone' => $request->get('phone'),
            'password' => $request->get('password'),
            'role_id' => $request->get('role_id'),
            'dialcode' => $request->get('dialcode')
        ];

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required',
            'dialcode' => 'required',
        ];

        $validator = Validator::make($input, $rules);
        if ($validator->fails() && (!$request->get('resend_otp'))) {
            $result = ["status" => $this->error, "message" => $validator->errors()->first(), "result" => []];
        } else {
            try {
                DB::beginTransaction();
                $randomid = mt_rand(100000, 999999);
                if (!$request->get('resend_otp')) {
                    $input['MOBILE_OTP'] = $randomid;
                    Session::put('OTP', $input);
                } else {
                    $data = Session::get('OTP');
                    $input = [
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'password' => $data['password'],
                        'role_id' => $data['role_id'],
                        'dialcode' => $data['dialcode'],
                        'MOBILE_OTP' => $randomid
                    ];
                    Session::put('OTP', $input);
                }
                $data = Session::get('OTP');
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

    public function resendOTP(Request $request)
    {
        try {
            DB::beginTransaction();
            $randomid = mt_rand(100000, 999999);
            DB::commit();
            $result = ['status' => $this->success, 'message' => "OTP send successfully.", 'otp' => $randomid];
        } catch (Exception $e) {
            DB::rollBack();
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function isPhoneExist(Request $request)
    {
        if (User::where('phone', $request->get('phone'))->exists()) {
            return 'false';
        } else {
            return 'true';
        }
    }
}
