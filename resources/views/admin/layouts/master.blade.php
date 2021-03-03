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

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ url('node_modules/jqvmap/dist/jqvmap.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/summernote/dist/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/izitoast/dist/css/iziToast.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/dropzone/dist/min/dropzone.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/datatables.net-select-bs4/css/select.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/select2/dist/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/bootstrap-daterangepicker/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{ url('node_modules/selectric/public/selectric.css')}}">

    <!-- sweet alert framework -->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/js/sweetalert/css/sweetalert.css')}}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('account/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('account/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('account/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('account/css/global.css') }}">

    <!-- admin global css -->
    <link rel="stylesheet" href="{{ asset('admin/css/global.css') }}">
</head>


<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                @include('admin.layouts.header')
            </nav>
            <div class="main-sidebar">
                @include('admin.layouts.sidebar')
            </div>

            <!-- Main Content -->
            <div class="main-content">
                @yield('content')
            </div>
            <footer class="main-footer">
                @include('admin.layouts.footer')
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('js/app.js') }}?{{ uniqid() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>

    <script src="{{ asset('assets/js/jquery.validate.js') }}"></script>
    <script src="{{ asset('assets/js/validate.additional-methods.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-select.min.js')}}"></script>

    <!-- JS Libraies -->
    <script src="{{ url('node_modules/chart.js/dist/Chart.min.js')}}"></script>
    <script src="{{ url('node_modules/summernote/dist/summernote-bs4.js')}}"></script>
    <script src="{{ url('node_modules/izitoast/dist/js/iziToast.min.js')}}"></script>
    <script src="{{ url('node_modules/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.js')}}"></script>
    <script src="{{ url('node_modules/dropzone/dist/min/dropzone.min.js')}}"></script>
    <script src="{{ url('node_modules/datatables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{ url('node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
    <script src="{{ url('node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js')}}"></script>
    <script src="{{ url('node_modules/sweetalert/dist/sweetalert.min.js')}}"></script>
    <script src="{{ url('node_modules/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{ url('node_modules/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{ url('node_modules/selectric/public/jquery.selectric.min.js')}}"></script>
    <!-- Template JS File -->
    <script src="{{asset('account/js/scripts.js')}}"></script>
    <script src="{{asset('account/js/custom.js')}}"></script>
    <script src="{{asset('js/jquery.steps.js')}}"></script>
    <!-- Page Specific JS File -->
    <!-- sweet alert js -->
    <script type="text/javascript" src="{{asset('assets/js/sweetalert/js/sweetalert.min.js')}}"></script>
    <script src="{{asset('admin/assets/js/jquery.magnific-popup.js')}}"></script>
    <script src="{{asset('js/global.js')}}"></script>
    <script type="text/javascript">
        /*Global Variable*/
        var header = {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        };
        var my_id = "{{ Auth::id() }}";
        var header_profile_icon = $('#header_profile_icon');
        var asset_url = "{{asset('')}}";
    </script>

    @yield('page_script')
</body>

</html>