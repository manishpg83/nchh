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

                @if (session('status'))
                <div class="offset-md-4 col-8 alert alert-success alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="passwordResetForm">
                    @csrf

                    <div class="form-group">
                        <label for="email">Enter Email OR Mobile Number</label>
                        <input id="email" type="text" class="@error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>

                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="CTA">
                        <input type="submit" value="Send OTP" id="submit" onclick="resetOTP(()">
                        <a href="{{ route('login') }}" class="switch">I have an account</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

</section>

{{-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body" id="resetForm">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" id="passwordResetForm">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email ID / Mobile Number') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" onclick="resetOTP()">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> --}}
@endsection
@section('page_script')
<script src="{{ asset('js/global.js')}}"></script>
<script type="text/javascript">
    var passwordResetForm;
    /*Url List*/
    var resetPasswordOtp = "{{Route('password.reset.otp')}}";
</script>
@endsection