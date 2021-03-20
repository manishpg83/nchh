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

            <div class="signup form-peice" id="otpForm">
                <form class="signup-form" method="post" action="{{ route('user.send.otp') }}" id="userForm">
                    <input type="hidden" name="timezone" id="tz">
                    @csrf

                    <div class="form-group register_as">
                        <span for="name">Are you a</span>
                        <select class="col-6 @error('role_id') is-invalid @enderror" id="role_id" name="role_id" onclick="changeRole()">
                            @foreach ($roles as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>

                        @error('role_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input id="name" type="text" class="name @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" id="name">

                        @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        {{-- <label for="phone">Phone</label> --}}
                        <input id="phone" type="number" class="phone @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="Phone Number">
                        <input type="hidden" name="dialcode">
                        @error('phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="@error('password') is-invalid @enderror" name="password" id="password" autocomplete="new-password">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="agree" id="agree" style="margin-top: 0.5rem;">
                            <label class="form-check-label" for="agree" style="transform: none; text-transform: none;font-size: 13px;">
                                I have read and agree to the <a href="{{ route('terms', ['type' => 'patient']) }}" class="terms" target="_blank">Terms and Conditions</a>.
                            </label>
                        </div>
                        @error('agree')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="CTA">
                        <input type="submit" value="Send OTP" id="submit" onclick="requestOTP()">
                        <a href="{{ route('login') }}" class="switch">I have an account</a>
                    </div>
                </form>

                <form class="d-none" method="POST" action="{{ route('user.register') }}" id="otpRegisterForm">
                    @csrf
                    <h6 class="otp-message"></h6>
                    <div class="form-group">
                        <label for="otp">{{ __('Enter OTP') }}</label>
                        <input id="otp" type="number" class="form-control" name="otp" id="otp" autocomplete="otp">
                    </div>

                    <div class="CTA">
                        <input type="submit" value="Register">
                        <a id="resend_otp" href="javascript:;" onclick="resendOtp()">Resend OTP</a>
                        <a class="switch" href="{{ route('login') }}">{{ __('I have an account?') }}</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

</section>
@endsection
@section('page_script')
<script src="{{ asset('js/page/register.js')}}"></script>
<script type="text/javascript">
    var userForm;
    var formUser = $('#userForm');
    var otpRegisterForm;
    /*Url List*/
    var goto_home = "{{route('home')}}";
    var resendOTP = "{{route('user.send.otp')}}";
    var send_otp_url = "{{Route('user.send.otp')}}";
    var is_phone_exist_url = "{{route('register.phone.isExist')}}";
    var otp_verify_url = "{{Route('front.verified.otp')}}";
</script>
@endsection