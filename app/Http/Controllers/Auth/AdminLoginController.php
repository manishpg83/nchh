<?php

namespace App\Http\Controllers\auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use Auth;


class AdminLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin Login Controller
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
    protected $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    /**
     * Show the admin login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin.auth.login', [
            'title' => 'Admin Login',
            'loginRoute' => route('admin.login'),
        ]);
    }

    /**
     * Admin login 
     */
    public function login(Request $request)
    {
        $rule = ['password' => 'required|min:6'];
        if (is_numeric($request->get('email'))) {
            $rule += ['email' => 'required|exists:users,phone'];
            $message = ['email.required' => 'This field is required.', 'email.exists' => 'The selected Mobile Number is invalid.'];
        } else {
            $rule += ['email' => 'required|email|exists:users,email'];
            $message = ['email.required' => 'This field is required.', 'email.exists' => 'The selected email is invalid.', 'email.email' => 'Enter valid email address.'];
        }

        $this->validate($request, $rule, $message);
        $user = User::where('email', $request->input('email'))->orWhere('phone', $request->input('email'))->first();
        if ($user->role->keyword == 'admin') {
            if (is_numeric($request->input('email'))) {
                Auth::guard('admin')->attempt(['phone' => $request->input('email'), 'password' => $request->password], $request->get('remember'));
            } else {
                $this->validate($request, $rule, $message);
                Auth::guard('admin')->attempt(['email' => $request->input('email'), 'password' => $request->password], $request->get('remember'));
            }
            if (Auth::guard('admin')->check()) {
                return redirect()->intended(route('admin.dashboard'));
            }
        } else {
            return redirect()->route('admin.login')->with('message', 'Permission Denied!');
        }


        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Admin logout
     */
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->with('status', 'Admin has been logged out!');
    }
}
