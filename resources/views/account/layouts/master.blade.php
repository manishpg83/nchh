<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ isset($pageTitle) ? $pageTitle. ' —' : '' }} {{isset($siteTitle) ? $siteTitle : 'NC Health Hub'}}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- &mdash; -->
    <link rel="shortcut icon" href="{{ asset('images/favicon/neucrad.png') }}">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="asset('assets/css/bootstrap-select.css')" /> -->

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ url('node_modules/jqvmap/dist/jqvmap.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/summernote/dist/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/izitoast/dist/css/iziToast.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/dropzone/dist/min/dropzone.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/select2/dist/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/fullcalendar/main.css') }}">
    <!-- <link rel="stylesheet" href="{{ url('node_modules/chocolat/dist/css/chocolat.css')}}"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chocolat/1.0.3/css/chocolat.css" integrity="sha512-xRIXz2QBKCQz7tK3KY5+j4WJfizGSlaqzYjzLOO/Mw+BqoGqUKcjG2xPYXmC8uW5NQF1O6XDNq83528AVp7hsA==" crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="{{asset('assets/js/sweetalert/css/sweetalert.css')}}">

    <!-- star rating -->
    <link rel="stylesheet" href="{{asset('assets/css/star-rating-svg.css')}}">

    <!-- jquery-schedule -->
    <link rel="stylesheet" href="{{ url('node_modules/jquery-schedule/dist/jquery.schedule.css')}}">
    <!-- <link rel="stylesheet" href="{{ asset('assets/js/week-scheduler/css/scheduler.css') }}"> -->

    <!-- Dialcode css -->
    <link rel="stylesheet" href="{{ asset('css/dialcode/intlTelInput.css')}}">

    <!-- Template CSS -->
    
    <link rel="stylesheet" href="{{ asset('account/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('account/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('account/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('account/css/global.css') }}">
</head>


<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                @include('account.layouts.header')
            </nav>
            <div class="main-sidebar">
                @include('account.layouts.sidebar')
            </div>

            <!-- Main Content -->
            <div class="main-content">
                @yield('content')
            </div>
            <footer class="main-footer">
                @include('account.layouts.footer')
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('js/app.js') }}?{{ uniqid() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.31/moment-timezone.min.js"></script>
    <script src="{{ asset('assets/js/jquery.validate.js') }}"></script>
    <script src="{{ asset('assets/js/validate.additional-methods.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-select.min.js')}}"></script>
    <script src="{{ asset('assets/js/jquery.slimscroll.js')}}"></script>

    <!-- JS Libraies -->
    <script src="{{ url('node_modules/summernote/dist/summernote-bs4.js')}}"></script>
    <script src="{{ url('node_modules/izitoast/dist/js/iziToast.min.js')}}"></script>
    <script src="{{ url('node_modules/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js')}}"></script>
    <script src="{{ url('node_modules/dropzone/dist/min/dropzone.min.js')}}"></script>
    <script src="{{ url('node_modules/datatables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{ url('node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ url('node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js')}}"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chocolat/1.0.3/js/chocolat.js" integrity="sha512-Zhyq+bDxL3WVXbAT1MinV+96CBB3xe6JGM/gDBO828u5vn4XB0oInRYR3YvR2db/x7he99//6gE/p8bk5NmNjA==" crossorigin="anonymous"></script>

    <script src="{{ url('node_modules/sweetalert/dist/sweetalert.min.js')}}"></script>
    <script src="{{ url('node_modules/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{ url('node_modules/fullcalendar/main.js')}}"></script>
    <script src="{{asset('assets/js/sweetalert/js/sweetalert.min.js')}}"></script>

    <!-- jquery-schedule -->
    <!-- <script src="{{ url('node_modules/jquery-schedule/dist/jquery.schedule.js')}}"></script> -->
    <script src="{{ asset('js/jquery.schedule.js')}}"></script>
    <!-- <script src="{{ asset('assets/js/week-scheduler/js/scheduler.js') }}"></script> -->

    <!--dialcode js-->
    <script src="{{ asset('js/dialcode/intlTelInput.js')}}"></script>
    <script src="{{ asset('js/dialcode/intlTelInput-jquery.min.js')}}"></script>

    <!-- Bootstrap ToolTip Js -->
    <script>
        var bootstrapTooltip = jQuery.fn.tooltip;
    </script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script>
        jQuery.fn.tooltip = bootstrapTooltip;
    </script>
    <!-- Bootstrap ToolTip Js -->

    <!-- star rating -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.6/js/star-rating.min.js"></script>
    <script src="{{asset('assets/js/jquery.star-rating-svg.js')}}"></script>
    
    <!-- Template JS File -->
    
    <script src="{{ asset('account/js/theme.js')}}"></script>
    <script src="{{asset('account/js/scripts.js')}}"></script>
    <script src="{{asset('account/js/custom.js')}}"></script>
    <script src="{{asset('js/jquery.steps.js')}}"></script>
    <script src="{{asset('js/global.js')}}"></script>
    <!-- Page Specific JS File -->

    <!-- Google Map API -->
    <script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC6NuAPEcUcc9anPu6CMEYgDWkLlnDZXug"></script>
    <!-- <script src="{{asset('account/js/page/index-0.js')}}"></script> -->

    <script type="text/javascript">
        /*Global Variable*/
        var header = {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        };
        //var socket = io.connect('http://localhost:3000');
        var my_id = "{{ Auth::id() }}";
        var asset_url = "{{asset('')}}";
        var header_profile_icon = $('#header_profile_icon');
         // $('select').selectpicker();
    </script>

    @yield('scripts')
</body>

</html>