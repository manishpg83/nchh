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
            <div class="login form-peice">
                <form class="login-form" method="POST" action="{{ route('staff.login') }}">
                    @csrf
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" autocomplete="username" autofocus>
                        @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me" {{ old('remember_me') ? 'checked' : '' }}>
                            <span class="form-check-label" for="remember_me">{{ __('Remember Me') }}</span>
                        </div>
                    </div>
                    <div class="CTA">
                        <input type="submit" value="Login">
                        <a class="switch" href="{{ route('login') }}">{{ __('Normal Login') }}</a>
                    </div>
                </form>
                <!-- login form end -->
            </div>
        </div>
    </div>

</section>
@endsection

@section('page_script')
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