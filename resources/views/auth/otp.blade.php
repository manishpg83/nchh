<form method="POST" action="{{ route('register') }}" id="userForm">
    @csrf
    <input type="hidden" name="name" value="{{$name}}">
    <input type="hidden" name="phone" value="{{$phone}}">
    <input type="hidden" name="password" value="{{$password}}">
    <input type="hidden" name="role_id" value="{{$role_id}}">
    <input type="hidden" name="dialcode" value="{{$dialcode}}">
    <input type="hidden" name="otp" value="{{$otp}}" id="otp">

    <h6 class="">We have sent you an OTP on {{$phone}}</h6>

    <div class="form-group row">
        <label for="otp_confirmation">{{ __('Enter OTP') }}</label>
        <input id="otp_confirmation" type="text" class="@error('otp_confirmation') is-invalid @enderror" name="otp_confirmation" id="otp_confirmation">

        @error('otp_confirmation')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="CTA">
        <input type="submit" value="Register" onclick="userRegister()">
        <a id="resend_otp" href="javascript:;" onclick="resendOtp()">Resend OTP</a>
        <a class="switch" href="{{ route('login') }}">{{ __('I have an account?') }}</a>
    </div>

    {{-- <div class="form-group row mb-0">
        <div class="col-md-6 offset-md-4">
            <button class="btn btn-primary" onclick="userRegister()">Login</button>
        </div>
    </div> --}}
</form> 
