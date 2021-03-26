@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <!-- <div>
                        <a href="{{route('account.practice.create')}}" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4"><i class="far fa-edit"></i>
                            Add</a>
                    </div> -->
                    <div class="card-body">
                        <form id="PracticeForm" action="{{route('account.practice.store')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="timing" id="field_timing" value="{{$allocated_timing}}">

                            <div class="form-group row mb-0">
                                @if(checkPermission(['doctor']))
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="title">Logo</label>
                                    <input type="file" name="logo" class="form-control browse_file">
                                    <div>
                                        <img src="{{asset('images/default.png')}}" class="thumbnail w-25 pt-2" id="imagePreview" />
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="name">Name<span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" id="name">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $name }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                @endif
                                @if(checkPermission(['diagnostics']))
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="name">Name<span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" id="name" value="{{Auth::user()->name}}">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $name }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                @endif

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="email">Email<span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{Auth::user()->email}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="phone">Phone<span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control" value="{{Auth::user()->phone}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="address">Address<span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control" value="{{Auth::user()->address}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="locality">Locality<span class="text-danger">*</span></label>
                                    <input type="text" name="locality" class="form-control" value="{{Auth::user()->locality}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="city">City<span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" value="{{Auth::user()->city}}">
                                </div>
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="state">State<span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control" value="{{Auth::user()->state}}">
                                </div>
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="country">Country<span class="text-danger">*</span></label>
                                    <select id="country" name="country" class="form-control select2_field">
                                        @foreach ($country ?? '' as $key => $value)
                                        <option value="{{$value}}" @if($value==Auth::user()->country){{'selected'}}@endif>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="pincode">Pincode<span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" value="{{Auth::user()->pincode}}">
                                </div>
                                @if(checkPermission(['doctor']))
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="fees">Consultant Fees<span class="text-danger">*</span></label>
                                    <input type="number" name="fees" class="form-control">
                                </div>
                                @endif
                                <div class="col-md-12 col-sm-12 mb-4">
                                    <div class="location-map" id="location-map">
                                        <div style="height: 400px;" id="map_canvas"></div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-header pl-0">
                                <h4>Practice Timings</h4>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-12 col-md-12">
                                    <div id="timing_chart" class="schedule_container"></div>
                                </div>
                            </div>

                            <div class="form-group row mb-4">
                                <div class="col-sm-12 col-md-7">
                                    <button class="btn btn-primary" id="btn_submit">Create Practice</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<!-- <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div> -->
@endsection
@section('scripts')
<script type="text/javascript">
    var PracticeForm = $('#PracticeForm');
    var parseValue = JSON.parse($('#field_timing').val());
    var lati = parseFloat("23.033863");
    var long = parseFloat("72.585022");
    var googleMapApi = "{{ config('custom.google_map_api_key') }}";
</script>
<script src="{{ asset('account/js/page/practice_manager.js')}}"></script>
@endsection