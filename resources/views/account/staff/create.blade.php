@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item active"><a href="{{route('account.staff.index')}}">Staffs</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="col-sm-12">
                        @if(session()->get('error'))
                        <div class="alert alert-danger">
                            {{ session()->get('error') }}
                        </div>
                        @endif
                        @error('name')
                        <div class="alert alert-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="card-body">

                        <form id="staffManagerForm" action="{{route('account.staff.store')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="timing" id="field_timing" value="">
                            <div class="form-group row mb-0">
                                @if(!empty($roles))
                                <div class="col-md-4 col-sm-12 mb-4">
                                    <div class="form-group">
                                        <label class="form-label">Role As</label>
                                        <div class="selectgroup w-100">
                                            @foreach($roles as $role)
                                            <label class="selectgroup-item">
                                                <input type="radio" name="role_id" id="{{$role->keyword}}" value="{{$role->id}}" class="selectgroup-input">
                                                <span class="selectgroup-button">{{$role->name}}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                        @error('role_id')
                                        <label id="role_id-error" class="error" for="role_id">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>
                                @endif

                                <div id="doctor_dropdown" style="display: none;" class="col-md-5 col-sm-12 mb-4 neucrad_select_box">
                                    <label>Doctors<span class="text-danger">*</span></label>
                                    <select id="user_id" name="user_id" class="form-control select2 search_doctor_dropdown">
                                        <option hidden></option>
                                    </select>
                                </div>

                                <div style="display: none;" class="col-md-3 col-sm-12 mb-4 other_field">
                                    <label>Consultant Fee<span class="text-danger">*</span></label>
                                    <input type="number" name="fees" class="form-control" value="">
                                </div>
                            </div>

                            <div id="basic_detail_box">
                                <div class="form-group row mb-0">
                                    <div class="col-md-4 col-sm-12 mb-4">
                                        <label for="name">Name<span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control">
                                        @error('name')
                                        <label id="name-error" class="error" for="name">{{ $message }}</label>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 col-sm-12 mb-4">
                                        <label for="email">Email<span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror">
                                        @error('email')
                                        <label id="email-error" class="error" for="email">{{ $message }}</label>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 col-sm-12 mb-4">
                                        <label for="phone">Phone<span class="text-danger">*</span></label>
                                        <!-- <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror -->
                                        <input id="phone" type="number" class="form-control phone @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" placeholder="Phone Number">
                                        <input type="hidden" name="dialcode">
                                        @error('phone')
                                        <label id="phone-error" class="error" for="phone">{{ $message }}</label>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 col-sm-12 mb-4">
                                        <label class="form-label">Gender</label>
                                        <div class="selectgroup selectgroup-pills">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="gender" id="gender" value="Male" class="selectgroup-input" checked>
                                                <span class="selectgroup-button selectgroup-button-icon">Male</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="gender" id="gender" value="Female" class="selectgroup-input">
                                                <span class="selectgroup-button selectgroup-button-icon">Female</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="gender" id="gender" value="Other" class="selectgroup-input">
                                                <span class="selectgroup-button selectgroup-button-icon">Other</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4 col-sm-12 mb-4">
                                        <label for="dob">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control" value="" data-date="yyyy-mm-dd">
                                        @error('dob')
                                        <label id="dob-error" class="error" for="dob">{{ $message }}</label>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 col-sm-12 mb-4">
                                        <label for="bloodgroup">Blood Group</label>
                                        <select id="blood_group" name="blood_group" class="form-control select2">
                                            <option hidden>Select Blood Group</option>
                                            @if(config('view.Bloodgroup'))
                                            @foreach(config('view.Bloodgroup') as $group)
                                            <option value="{{$group}}">{{$group}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                        @error('blood_group')
                                        <label id="blood_group-error" class="error" for="blood_group">{{ $message }}</label>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 col-sm-12 mb-4">
                                        <label for="timezone">Timezone</label>
                                        {!!$timezonelist!!}
                                        @error('blood_group')
                                        <label id="timezone-error" class="error" for="timezone">{{ $message }}</label>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            <div id="address_detail_box">
                                <div class="card-header pl-0">
                                    <h4>Address</h4>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4 col-12">
                                        <label>Block No./ Street Name<span class="text-danger">*</span></label>
                                        <input type="text" name="address" class="form-control" value="">
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>Locality<span class="text-danger">*</span></label>
                                        <input type="text" name="locality" class="form-control" value="">
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>City<span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control" value="">
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>State<span class="text-danger">*</span></label>
                                        <input type="text" name="state" class="form-control" value="">
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>Country<span class="text-danger">*</span></label>
                                        <select id="country" name="country" class="form-control select2">
                                            <option value="">Select Country</option>
                                            @foreach ($country ?? '' as $key => $value)
                                            <option value="{{$value}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>Pincode<span class="text-danger">*</span></label>
                                        <input type="text" name="pincode" class="form-control" value="">
                                    </div>
                                </div>

                            </div>

                            <div id="schedule_box" style="display: none;">
                                <div class="card-header pl-0">
                                    <h4>Practice Timings</h4>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12 col-md-12 schedule_container">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-4">
                                <div class="col-sm-12">
                                    <!-- <button class="btn btn-info btn-reset float-right" type="reset">Reset</button> -->
                                    <button class="btn btn-primary btn-submit float-right mr-2">Create</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

@endsection
@section('scripts')
<script src="{{ asset('account/js/page/staff.js')}}"></script>
<script type="text/javascript">
    var staffManagerForm = $('#staffManagerForm');
    var isDoctorRegisteredUrl = "{{route('account.is-doctor-register')}}";
    var getDoctorScheduleUrl = "{{route('account.get-doctor-schedule',':slug')}}";
    var getDoctorsUrl = "{{route('account.get-doctors')}}";
    var existing_doctors = JSON.parse("{{($existing_doctor)}}");
    console.log(existing_doctors);

    $.validator.setDefaults({
        errorPlacement: function(error, element) {
            if (element.attr("name") === "phone") {
                element.parent().after(error);
            } else if (element.attr("name") === "role_id") {
                element.parent().parent().after(error);
            } else if (element.attr("name") === "user_id") {
                element.parent().find('.select2-container').after(error);
            } else if (element.attr("name") === "country") {
                element.parent().append(error);
            } else {
                error.insertAfter(element);
            }
        }
    });
</script>
@endsection