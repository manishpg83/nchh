<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="shortcut icon" href="{{ asset('images/favicon/neucrad.png') }}">

    <!-- Login with google -->
    <script src="https://apis.google.com/js/client:platform.js?onload=renderButton" async defer></script>
    <meta name="google-signin-client_id"
        content="970184753273-b6ijf32il7b67n73efkut3kaa8vu73db.apps.googleusercontent.com">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('css/ionicons/css/ionicons.min.css') }}"> --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Dialcode css -->
    <link rel="stylesheet" href="{{ asset('css/dialcode/intlTelInput.css')}}">

</head>

<body>

    <div class="container">
        @yield('content')
    </div>

    {{-- <main class="py-4">
        @yield('content')
    </main> --}}

    <script src="{{ asset('assets/js/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script> -->
    <script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('js/jquery.nicescroll.js')}}"></script>

    <script src="{{ asset('assets/js/jquery.validate.js') }}"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
    <script src="{{asset('assets/js/socket.io.js')}}"></script>

    <script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
    <!--dialcode js-->
    <script src="{{ asset('js/dialcode/intlTelInput.js')}}"></script>
    <script src="{{ asset('js/dialcode/intlTelInput-jquery.min.js')}}"></script>

    <!-- ckEditor js -->
    <script type="text/javascript" src="{{asset('assets/js/ckeditor/ckeditor.js')}}"></script>

    <!-- sweet alert js -->
    <script type="text/javascript" src="{{asset('assets/js/sweetalert/js/sweetalert.min.js')}}"></script>
    <script src="{{ asset('js/global.js') }}"></script>
    <script src="{{ asset('js/theme.js') }}"></script>

    <script type="text/javascript">
    /*Global Variable*/
    var header = {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    };
    // var socket = io.connect('http://localhost:3000');
    var my_id = "{{ Auth::id() }}";
    var asset_url = "{{asset('')}}";
    // $('select').selectpicker();
    </script>
    @yield('page_script')
</body>

</html>