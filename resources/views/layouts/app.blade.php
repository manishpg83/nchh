<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($pageTitle) ? $pageTitle. ' —' : '' }} {{isset($siteTitle) ? $siteTitle : 'NC Health Hub'}}</title>
    <!-- <title>{{ config('app.name', 'Neucrad') }}</title> -->
    <link rel="shortcut icon" href="{{ asset('images/favicon/neucrad.png') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/ionicons/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />

    <link rel="stylesheet" href="{{ url('node_modules/izitoast/dist/css/iziToast.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/js/sweetalert/css/sweetalert.css')}}">

    <!-- Image Gallery Script -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chocolat/1.0.3/css/chocolat.css" integrity="sha512-xRIXz2QBKCQz7tK3KY5+j4WJfizGSlaqzYjzLOO/Mw+BqoGqUKcjG2xPYXmC8uW5NQF1O6XDNq83528AVp7hsA==" crossorigin="anonymous" />

    <!-- Bootstrap slider -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/css/bootstrap-slider.min.css" integrity="sha512-3q8fi8M0VS+X/3n64Ndpp6Bit7oXSiyCnzmlx6IDBLGlY5euFySyJ46RUlqIVs0DPCGOypqP8IRk/EyPvU28mQ==" crossorigin="anonymous" />

    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/global.css') }}" rel="stylesheet">
    <link href="{{ asset('css/videocall.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/scrolltabs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/star-rating-svg.css')}}">

    <!-- Dialcode css -->
    <link rel="stylesheet" href="{{ asset('css/dialcode/intlTelInput.css')}}">

    <!-- Payment Gateway -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    @yield('page_css')
</head>

<body>
    <nav class="navbar navbar-expand-lg main-navbar {{Route::is('home') ? '' : 'bg-dark'}}">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="{{ url('/') }}">
                <!-- {{ config('app.name', 'Laravel') }} -->
                {{isset($siteTitle) ? $siteTitle : 'NC Health Hub'}}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">
                    <i class="ion-navicon"></i>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="mr-auto"></div>
                <ul class="navbar-nav">
                    @guest
                    <li class="nav-item active">
                        <a class="nav-link smooth-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link smooth-link" href="{{ route('register') }}">Register</a>
                    </li>
                    @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span></a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('account.show-profile-form') }}">
                                {{ __('Account') }}
                            </a>
                            @if(checkPermission(['patient','doctor','agent']))
                            <a class="dropdown-item {{route::is('medical_record.index') ? 'active' : ''}}" href="{{ route('medical_record.index') }}">
                                {{ __('Medical Record') }}
                            </a>
                            <a class="dropdown-item {{route::is('myAppointment') ? 'active' : ''}}" href="{{ route('myAppointment') }}">
                                {{ __('My Appointment') }}
                            </a>
                            <a class="dropdown-item {{route::is('account.myDoctors') ? 'active' : ''}}" href="{{ route('account.myDoctors') }}">
                                {{ __('My Doctor') }}
                            </a>
                            <a class="dropdown-item {{route::is('chat.index') ? 'active' : ''}}" href="{{ route('chat.index') }}">
                                {{ __('Chat') }}
                            </a>
                            @endif
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf</form>
                        </div>
                    </li>
                    @endguest
                </ul>
                <!-- <form class="form-inline">
                <a href="{{ route('login') }}" class="btn smooth-link align-middle btn-primary">Login</a> | <a href="{{ route('register') }}" class="btn smooth-link align-middle btn-primary">Register</a></form> -->
            </div>
        </div>
    </nav>
    <div class="modal fade fullscreen-modal" id="globalModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
    @yield('content')

    @if(Route::is('home'))
    <section class="bg-grey" id="contact">
        <div class="container">
            <h2 class="section-title text-center">Contact Us</h2>
            <p class="section-lead text-center text-muted">Send us your inquiry, we will help you and reply as soon as
                possible</p>
            <div class="section-body">
                <div class="row col-spacing">
                    <div class="col-12 col-md-5">
                        <p class="contact-text">Lorem Ipsum is simply dummy text of the printing and typesetting
                            industry.</p>
                        <ul class="contact-icon">
                            <li><i class="ion ion-ios-telephone"></i>
                                <div>+9100000000</div>
                            </li>
                            <li><i class="ion ion-ios-email"></i>
                                <div>developer@neucrad.com</div>
                            </li>
                        </ul>
                        <iframe src="https://snazzymaps.com/embed/10159" style="border:none;" class="maps"></iframe>
                    </div>
                    <div class="col-12 col-md-7">
                        <form class="contact row" id="inquiryForm" method="POST" action="{{route('user.inquiry')}}">
                            @csrf
                            <div class="form-group col-6">
                                <input type="text" class="form-control" placeholder="Name" name="name" id="name">
                            </div>
                            <div class="form-group col-6">
                                <input type="email" class="form-control" placeholder="Email" name="email" id="email">
                            </div>
                            <div class="form-group col-12">
                                <input type="text" class="form-control" placeholder="Subject" name="subject" id="subject">
                            </div>
                            <div class="form-group col-12">
                                <textarea class="form-control" placeholder="Message" name="message" id="message"></textarea>
                            </div>
                            <div class="form-group col-12 mt-2">
                                <button class="btn btn-primary btn-submit br-30">Send Message</button>
                                <span class="ml-2 text-success inquiry-message"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
    @endif

    <footer>
        <div class="container">
            <figure>
                {{-- <img src="img/logo-light.png" alt="Logo"> --}}
                NC HEALTH HUB
            </figure>
            <p>
                Copyright &copy; {{date('Y')}} NC HEALTH HUB
            </p>
              <!--<p>
                Made with <i class="ion-heart"></i> By Developer
            </p>-->
        </div>
    </footer>
    <div class="progress-overlay"></div>

    <!-- Incoming call alert -->
    <div class="incoming-call">
        <div class="call-popup">
            <div class="all-layer">
                <div class="call-name">
                    <div class="caller-name mt-3">
                        <h6 class="clr-white">
                            Dr. Sanjay Mangarolia
                            </h3>
                    </div>
                    <div class="calling">
                        <p class="clr-white">
                            is calling you <span class="dot dot1">.</span><span class="dot dot2">.</span><span class="dot dot3">.</span><span class="dot dot4">.</span>
                        </p>
                    </div>
                </div>
                <hr class="light-border">
                <div class="calling-buttons">
                    <ul>

                        <li class="call-reject">
                            <a href="javascript:;" class="call" onclick="incomingCallAction('reject')">
                                <div class="call-square">
                                    <img src="{{asset('images/controls/reject.svg')}}" alt="reject" class="reject">
                                    <span class="call-text">
                                        Decline
                                    </span>
                                </div>
                            </a>
                        </li>
                        <li class="call-accept">
                            <a href="javascript:;" class="call" onclick="incomingCallAction('accept')">
                                <div class="call-square">
                                    <img src="{{asset('images/controls/accept.svg')}}" alt="accept" class="accept">
                                    <span class="call-text">
                                        Accept
                                    </span>
                                </div>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="call-overlay"></div>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script> -->
    <script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('js/jquery.nicescroll.js')}}"></script>
    <script src="{{asset('assets/js/jquery.scrolltabs.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mousewheel.js')}}"></script>
    <script src="{{asset('assets/js/jquery.validate.js') }}"></script>

    <!-- CDN's -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.6/js/star-rating.min.js"></script>

    <!-- Confirm CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <script src="{{asset('assets/js/socket.io.js')}}"></script>
    <script src="{{asset('assets/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.star-rating-svg.js')}}"></script>
    <!--dialcode js-->
    <script src="{{ asset('js/dialcode/intlTelInput.js')}}"></script>
    <script src="{{ asset('js/dialcode/intlTelInput-jquery.min.js')}}"></script>

    <!-- ckEditor js -->
    <script type="text/javascript" src="{{asset('assets/js/ckeditor/ckeditor.js')}}"></script>

    <!-- sweet alert js -->
    <script src="{{ url('node_modules/izitoast/dist/js/iziToast.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/sweetalert/js/sweetalert.min.js')}}"></script>

    <!-- cryptLib js -->
    <script type="text/javascript" src="{{asset('js/crypto-js.min.js')}}"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js"></script> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js" integrity="sha512-nOQuvD9nKirvxDdvQ9OMqe2dgapbPB7vYAMrzJihw5m+aNcf0dX53m6YxM4LgA9u8e9eg9QX+/+mPu8kCNpV2A==" crossorigin="anonymous"></script> -->


    <!-- Bootstrap Slider js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/11.0.2/bootstrap-slider.js" integrity="sha512-tCkLWlSXiiMsUaDl5+8bqwpGXXh0zZsgzX6pB9IQCZH+8iwXRYfcCpdxl/owoM6U4ap7QZDW4kw7djQUiQ4G2A==" crossorigin="anonymous"></script>

    <!-- <script src="{{asset('assets/js/chocolat.js')}}"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chocolat/1.0.3/js/chocolat.js" integrity="sha512-Zhyq+bDxL3WVXbAT1MinV+96CBB3xe6JGM/gDBO828u5vn4XB0oInRYR3YvR2db/x7he99//6gE/p8bk5NmNjA==" crossorigin="anonymous"></script>

    <!-- Script for format time in javascript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

    <!-- lazy load cdnjs -->
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.plugins.min.js"></script>

    <!-- firebase cdnjs -->
    <script src="https://www.gstatic.com/firebasejs/7.22.1/firebase-app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/7.22.1/firebase-storage.min.js" integrity="sha512-GuMCyR1LXS+xovLmB5P/JzsOPo542879WQEYSkudnyeCX1LnG2ZpMFGG4IpMaJPCoDawmOSMo2/+30TZsjGE/Q==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/7.22.1/firebase-database.min.js" integrity="sha512-9pcV+9tJDsn39SvC/t4Jd8N3k8bt58aYbkL6izei0DKZdR3RPp5HSHC0Up7tSpTIftBQb32zhFCYI4gb3WLvmQ==" crossorigin="anonymous"></script>
    <script src="https://www.gstatic.com/firebasejs/7.22.1/firebase-analytics.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
    <script src="{{ asset('js/global.js') }}"></script>
    <script src="{{ asset('js/theme.js') }}"></script>


    <script type="text/javascript">
        /*Global Variable*/
        var header = {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        };
        var chatServerDomain = "{{config('services.chat.domain','https://www.nchealthhub.com:3000')}}";
        var my_id = "{{ Auth::id() }}";
        `@if(Auth::id())`
        var sender = JSON.parse(`{!! Auth::user()->toJson() !!}`);
        `@endif`
        var asset_url = "{{asset('')}}"
        var login_url = "{{ route('login') }}";
        var getLocation = "{{Route('detect.location')}}";
        var autoSearch = "{{Route('home.autoSearch')}}";
        var autoSearchCity = "{{Route('home.autoSearch.city')}}";
        var search = "{{Route('home.search')}}";
        var addReviewUrl = "{{Route('account.rating.create')}}";
        var videoConsultUrl = "{{Route('chat.video.open',':slug')}}";
        var videoChatboxUrl = "{{route('chat.video.chatbox',':id')}}";
        var videoConsultatScreen = "{{route('chat.private',':id')}}"
        var chatScreen = "{{route('chat.private.window.open',':slug')}}"
        var sendChatNotification = "{{route('send.chat.notification')}}";
        var globalModal = $('#globalModal');
        var inquiryForm = $('#inquiryForm');
        var incomingCallPopup = $('.incoming-call');
        $('select').selectpicker();

        var firebaseConfig = {
            apiKey: "{{config('services.chat.apiKey')}}",
            authDomain: "{{config('services.chat.authDomain')}}",
            databaseURL: "{{config('services.chat.databaseURL')}}",
            projectId: "{{config('services.chat.projectId')}}",
            storageBucket: "{{config('services.chat.storageBucket')}}",
            messagingSenderId: "{{config('services.chat.messagingSenderId')}}",
            appId: "{{config('services.chat.appId')}}",
            measurementId: "{{config('services.chat.measurementId')}}"
        };
        firebase.initializeApp(firebaseConfig);
        // firebase.analytics();
        var database = firebase.database();
        var storage = firebase.storage();
        if (my_id) {
            var socket = io.connect("{{config('services.chat.domain','https://www.nchealthhub.com:3000')}}", {
                query: sender
            })
            socket.on('getNotification', function(res) {
                c(res);
            });
        }
    </script>
    @yield('page_script')
</body>

</html>