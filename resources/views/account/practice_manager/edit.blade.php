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
                        <form id="PracticeForm" action="{{route('account.practice.update',$practice->id)}}" method="POST" enctype="multipart/form-data">
                            @method('PATCH')
                            @csrf
                            <input type="hidden" name="latitude" id="latitude" value="{{$practice->latitude}}">
                            <input type="hidden" name="longitude" id="longitude" value="{{$practice->longitude}}">
                            <input type="hidden" name="timing" id="field_timing" value="{{$allocated_timing}}">

                            <div class="form-group row mb-0">

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="title">Logo</label>
                                    <input type="file" name="logo" class="form-control browse_file">
                                    <div>
                                        <img src="{{$practice->logo}}" class="thumbnail w-25 pt-2" id="imagePreview" />
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="name">Name<span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{$practice->name}}">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $name }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="email">Email<span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{$practice->email}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="phone">Phone<span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control" value="{{$practice->phone}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="address">Address<span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control" value="{{$practice->address}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="locality">Locality<span class="text-danger">*</span></label>
                                    <input type="text" name="locality" class="form-control" value="{{$practice->locality}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="city">City<span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" value="{{$practice->city}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="state">State<span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control" value="{{$practice->state}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="country">Country<span class="text-danger">*</span></label>
                                    <select id="country" name="country" class="form-control select2_field">
                                        @foreach ($country ?? '' as $key => $value)
                                        <option value="{{$value}}" @if($value==$practice->country){{'selected'}}@endif>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="pincode">Pincode<span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" value="{{$practice->pincode}}">
                                </div>

                                <div class="col-md-4 col-sm-12 mb-4">
                                    <label for="fees">Consultant Fees<span class="text-danger">*</span></label>
                                    <input type="number" name="fees" class="form-control" value="{{$practice->fees}}">
                                </div>
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
                                    <!-- <input type="submit" class="btn btn-primary btn_submit" value="Update Practice"> -->
                                    <button class="btn btn-primary" id="btn_submit">Update Practice</button>
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
<script src="{{ asset('account/js/page/practice_manager.js')}}"></script>
<script type="text/javascript">
    var PracticeForm = $('#PracticeForm');
    var parseValue = JSON.parse($('#field_timing').val());
    var lati = parseFloat("{{$practice->latitude}}");
    var long = parseFloat("{{$practice->longitude}}");
</script>
@endsection