@extends('layouts.login')

@section('content')

<section id="formHolder">

    <div class="row">
        <!-- Brand Box -->
        <div class="col-sm-6 brand">
            <a href="#" class="logo">MR <span>.</span></a>
            <div class="heading">
                <h2>Neucrad</h2>
                <p>We care about Your Health</p>
            </div>
            <div class="success-msg">
                <p>Great! You are one of our members now</p>
                <a href="#" class="profile">Your Profile</a>
            </div>
        </div>
        <!-- Form Box -->
        <div class="col-sm-6 form">
            <!-- Login Form -->
            <div class="login form-peice" id="otpForm">
                <form class="login-form" method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email Adderss OR Phone</label>
                        <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span class="form-check-label" for="remember">{{ __('Remember Me') }}</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" id="otp_flow" name="otp_flow" type="checkbox" value="y">
                            <span class="form-check-label" for="otp_flow">
                                {{ __('Login with OTP instead of password') }}
                            </span>
                        </div>
                    </div>

                    <div class="CTA">
                        <input type="submit" value="Login">
                        <!-- onclick="sendOTP()" -->
                        {{-- <a href="#" class="switch">I'm New</a> --}}
                        <a class="switch" href="{{ route('register') }}">{{ __("I'm New") }}</a>
                        @if (Route::has('password.request'))
                        <a class="switch" href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                        @endif
                        <a href="{{route('staff.login.form')}}" class="switch">Staff Login</a>
                    </div>

                </form>
                <!-- login form end -->
                <!--  otp form start -->
                <form class="d-none" method="POST" action="{{ route('user.otp.login') }}" id="otpLoginForm">
                    @csrf
                    <h6 class="otp-message"></h6>
                    <div class="form-group">
                        <label for="otp">{{ __('Enter OTP') }}</label>
                        <input id="otp" type="number" class="form-control" name="otp" id="otp" autocomplete="otp">
                    </div>
                    <div class="CTA">
                        <input type="submit" value="Login" onclick="userOTPLogin()">
                        <a id="resend_otp" href="javascript:;" onclick="resendOtp()">Resend OTP</a>
                        @if (Route::has('password.request'))
                        <a class="switch" href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                        @endif
                    </div>
                </form>
                <!-- End otp Form -->
            </div>
        </div>
    </div>

</section>
@endsection

@section('page_script')
<script src="{{ asset('js/page/login.js') }}"></script>

<script type="text/javascript">
    var loginForm;
    var formLogin = $('#loginForm');
    var otpLoginForm;
    var rules;
    /*Url List*/
    var userData = "{{Route('social.user.store')}}";
    var otpLogin = "{{Route('user.send.login.otp')}}";
    var verifiedOTPUrl = "{{Route('front.verified.otp')}}";
    var verify_detail_url = "{{route('front.verified.detail.login')}}";
</script>
@endsection