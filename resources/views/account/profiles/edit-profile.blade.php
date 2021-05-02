@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}} <!-- ( As {{Auth::user()->role->name}}) -->
        </h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <h2 class="section-title">Hi, {{$user->name}}</h2>
        <p class="section-lead">
            Change information about yourself.

        </p>
        <div class="row mt-sm-4">
            <div class="col-12 col-md-12 col-lg-12">

                <div class="col-sm-12 p-0">
                    @include('account.layouts.flash-message')
                </div>

                <div class="card">
                    <form method="post" action="{{ route('account.edit-profile',[$user->id]) }}" id="userProfile">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="card-body">
                            <button type="submit" class="btn btn-primary float-right submitProfile" onclick="submitProfileForm($(this))">Save Changes</button>
                            <div class="row">
                                <div class="col-md-5 col-12">
                                    <div class="card profile-widget">
                                        <div class="profile-widget-header">
                                            <img alt="image" src="{{$user->profile_picture}}" class="rounded-circle profile-widget-picture" id="previewPicture">
                                            <input type="file" name="profile_picture" class="mt-2" id="profile_picture" style="display: none">
                                            <div class="profile-widget-items">
                                                <div class="profile-widget-item">
                                                    <div class="profile-widget-item-label mb-2">Pick a photo from your
                                                        computer</div>
                                                    <div class="profile-widget-item-value badges">
                                                        @if($user->image_name === "default.png")
                                                        <a href="javascript:;" class="nav-link badge badge-primary" onclick="browsePicture()">Add Photo</a>
                                                        @else
                                                        <a href="javascript:;" class="nav-link badge badge-primary" onclick="browsePicture()">Edit</a>
                                                        <a href="javascript:;" class="nav-link badge badge-light" onclick="removePicture('{{$user->id}}',this)">Remove</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label>Name<i class="asterisk">*</i></label>
                                    <input type="text" class="form-control" name="name" value="{{$user->name}}">
                                </div>
                            </div>

                            <div class="row primarybox">
                                <div class="form-group col-md-4 col-12">
                                    <label>Phone Number<i class="asterisk">*</i></label>
                                    <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" value="{{$user->phone}}" disabled="disabled">
                                    <!-- <a href="javascript:;" class="nav-link btn btn-primary btn-sm btn_inline is-textbox"
data-field="phone" onclick="viewTextbox(this)">Edit</a> -->
                                    <a href="javascript:;" type="submit" class="nav-link btn btn-primary btn-sm btn_inline hide is-edit" data-field="phone" onclick="sendOTP(this)">Change</a>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>Email Address<i class="asterisk">*</i></label>
                                    <input type="email" name="email" class="form-control user-email" placeholder="Enter your email address" disabled="disabled" value="{{$user->email}}">
                                    <a href="javascript:;" class="btn btn-primary btn-sm btn_inline is-textbox" data-field="email" onclick="viewTextbox(this)">@if($user->email) Edit @else Add @endif</a>
                                    <a href="javascript:;" class="btn btn-primary btn-sm btn_inline hide is-edit" data-field="email" onclick="sendOTP(this)">Change</a>
                                </div>
                                @if(checkPermission(['doctor','patient','agent']))
                                <div class="form-group col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="form-label">Gender</label>
                                        <div class="selectgroup selectgroup-pills">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="gender" id="gender" value="Male" class="selectgroup-input" @if($user->gender ==
                                                "Male"){{'checked=""'}}"@else {{'checked'}} @endif>
                                                <span class="selectgroup-button selectgroup-button-icon">Male</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="gender" id="gender" value="Female" class="selectgroup-input" @if($user->gender ==
                                                "Female"){{'checked=""'}}@endif>
                                                <span class="selectgroup-button selectgroup-button-icon">Female</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="gender" id="gender" value="Other" class="selectgroup-input" @if($user->gender ==
                                                "Other"){{'checked=""'}}@endif>
                                                <span class="selectgroup-button selectgroup-button-icon">Other</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="{{$user->dob}}" data-date="yyyy-mm-dd">
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>Blood Group</label>
                                    <select id="blood_group" name="blood_group" class="form-control select2">
                                        <option hidden value="">Select Blood Group</option>
                                        @if(config('view.Bloodgroup'))
                                        @foreach(config('view.Bloodgroup') as $group)
                                        <option value="{{$group}}" @if($user->blood_group ==
                                            $group){{'selected'}}@endif>{{$group}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endif
                                <div class="form-group col-md-4 col-12">
                                    <label>Timezone<i class="asterisk">*</i></label>
                                    {!!$timezonelist!!}
                                    <label id="timezone-error" class="error" for="timezone"></label>
                                </div>
                            </div>

                            <div class="card-header pl-0">
                                <h4>Address</h4>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 col-12">
                                    <label>House No./ Street Name<i class="asterisk">*</i></label>
                                    <input type="text" name="address" class="form-control" value="{{$user->address}}">
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>Locality<i class="asterisk">*</i></label>
                                    <input type="text" name="locality" class="form-control" value="{{$user->locality}}">
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>City<i class="asterisk">*</i></label>
                                    <input type="text" name="city" class="form-control" value="{{$user->city}}">
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>State<i class="asterisk">*</i></label>
                                    <input type="text" name="state" class="form-control" value="{{$user->state}}">
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>Country<i class="asterisk">*</i></label>
                                    <select id="country" name="country" class="form-control select2">
                                        @foreach ($country ?? '' as $key => $value)
                                        <option value="{{$value}}" @if($value==$user->
                                            country){{'selected'}}@elseif($value=="India"){{'selected'}}@endif
                                            >{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>Pincode<i class="asterisk">*</i></label>
                                    <input type="text" name="pincode" class="form-control" value="{{$user->pincode}}">
                                </div>
                            </div>
                            <div class="card-header pl-0">
                                <h4>Other Information</h4>
                            </div>
                            <div class="row other_information_card">
                                @if(checkPermission(['doctor','patient','agent']))
                                    <div class="form-group col-md-4 col-12">
                                        <label>Feet (Height)</label>
                                        <select class="form-control select2" name="height_feet" id="height_feet">
                                            <option value="" hidden>Select</option>
                                            @for($i = 1; $i <= 9; $i++)
                                                <option value="{{ $i }}" @if($user->height_feet == $i) selected @endif>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>Inches (Height)</label>
                                    <select class="form-control select2" name="height_inches" id="height_inches">
                                            <option value="" hidden>Select</option>
                                            @for($i = 0; $i <= 11; $i++)
                                                <option value="{{ $i }}" @if($user->height_inches == $i) selected @endif>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>Weight</label>
                                        <div class="input-group mb-2">
                                            <input type="text" name="weight" class="form-control" value="{{ $user->weight }}">
                                            <div class="input-group-append">
                                                <div class="input-group-text">KG</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>Do you smoke?</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_smoke" value="1" class="selectgroup-input" @if($user->is_smoke) checked @endif>
                                                <span class="selectgroup-button selectgroup-button-icon">Yes</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_smoke" value="0" class="selectgroup-input" @if(!is_null($user->is_smoke) && !$user->is_smoke) checked @endif>
                                                <span class="selectgroup-button selectgroup-button-icon">No</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 col-12">
                                        <label>Do you have any known allergy?</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_known_allergy" onclick="$('.allergyDiv').removeClass('d-none')" value="1" class="selectgroup-input" @if($user->is_known_allergy) checked @endif>
                                                <span class="selectgroup-button selectgroup-button-icon">Yes</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_known_allergy" onclick="$('.allergyDiv').addClass('d-none')" value="0" class="selectgroup-input" @if(!is_null($user->is_known_allergy) && !$user->is_known_allergy) checked @endif>
                                                <span class="selectgroup-button selectgroup-button-icon">No</span>
                                            </label>
                                          </div>
                                    </div>
                                    <div class="form-group col-md-4 col-12 allergyDiv @if(!$user->is_known_allergy) d-none @endif">
                                        <label>What type of allergy you have?</label>
                                        <input type="text" name="allergy" class="form-control" id="allergy" value="{{ $user->allergy }}">
                                    </div>
                                @endif
                                @if(checkPermission(['clinic','hospital']))
                                <div class="form-group col-md-6 col-12">
                                    <label>Speciality<i class="asterisk">*</i></label>
                                    <select id="specialty_ids" name="specialty_ids[]" class="form-control select2" multiple>
                                        <option hidden></option>
                                        @foreach ($specialty as $key => $value)
                                        <option value="{{$key}}" @if(!empty($user->detail->specialty_ids))
                                            @if(in_array($key, $user->detail->specialty_ids)){{'selected'}}@endif
                                            @endif>
                                            {{$value}}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label id="specialty_ids-error" class="error" for="specialty_ids"></label>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label>Services<i class="asterisk">*</i></label>
                                    <select id="services" name="services[]" class="form-control select2" multiple>
                                        <option hidden></option>
                                        @foreach ($services as $key => $value)
                                        <option value="{{$key}}" @if(!empty($user->detail->services))
                                            @if(in_array($key, $user->detail->services)){{'selected'}}@endif
                                            @endif>
                                            {{$value}}
                                        </option>
                                        @endforeach
                                    </select>
                                    <label id="services-error" class="error" for="services"></label>
                                </div>
                                <div class="form-group col-md-3 col-12">
                                    <label>Website</label>
                                    <div class="input-group">
                                        <input type="url" name="detail[website]" class="form-control" placeholder="Enter Website" value="{{$user->detail->website}}">
                                    </div>
                                </div>
                                <div class="form-group col-md-3 col-12">
                                    <label>GSTIN</label>
                                    <div class="input-group">
                                        <input type="text" name="detail[gst_in]" class="form-control" placeholder="Enter GSTIN" value="{{$user->detail->gst_in}}">
                                    </div>
                                </div>
                                @endif
                                @if(checkPermission(['hospital']))
                                <div class="form-group col-md-3 col-12">
                                    <label>Bed</label>
                                    <div class="input-group">
                                        <input type="text" name="detail[bed]" class="form-control" placeholder="Enter total number of Bed" value="{{$user->detail->bed}}">
                                    </div>
                                </div>
                                @endif
                                @if(checkPermission(['hospital','diagnostics']))
                                <div class="form-group col-md-3 col-12">
                                    <label>Timing</label>
                                    <div class="input-group">
                                        <input type="text" name="detail[timing]" class="form-control" placeholder="Enter Timing" value="{{$user->detail->timing}}">
                                    </div>
                                </div>
                                @endif

                                <div class="form-group col-md-12 col-12">
                                    <label>About</label>
                                    <textarea class="form-control" type="text" name="detail[about]" placeholder="Write About Your Self" style="height: 75px;">{{$user->detail->about}}</textarea>
                                </div>
                                @if(checkPermission(['clinic','hospital','diagnostics']))
                                <input type="hidden" name="latitude" id="latitude" value="{{$user->latitude}}">
                                <input type="hidden" name="longitude" id="longitude" value="{{$user->longitude}}">

                                <div class="col-md-12 col-sm-12 mb-4">
                                    <div class="location-map" id="location-map">
                                        <div style="height: 400px;" id="map_canvas"></div>
                                    </div>
                                </div>
                                @endif
                                @if(checkPermission(['clinic','hospital','diagnostics']))
                                <div class="form-group col-md-12 col-12">
                                    <label>Gallery Photos</label>
                                    <div id="files" class="dropzone">
                                        <div class="dz-message">Drop image here or click to Upload</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" onclick="submitProfileForm($(this))">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.alphanum/1.0.24/jquery.alphanum.min.js"></script>
<script src="{{ asset('account/js/page/edit-profile.js')}}"></script>
<script type="text/javascript">
    var userProfile;
    var userModal = $('#userModal');
    var fileupload = $("#profile_picture");
    var filepreview = $("#previewPicture");
    //url
    var remove_picture_url = "{{route('account.remove-picture',[':slug'])}}";
    var changefield_url = "{{route('account.changefield')}}";
    var sendOtp_url = "{{route('account.send-otp')}}";
    var verifyOtp_url = "{{route('account.verify-otp')}}";
    var getUserGalleryDetails = "{{Route('account.show-profile-form')}}";
    var deleteUserGalleryFileUrl = "{{Route('account.user.gallery.file.delete',':slug')}}";
    var lati = parseFloat("{{$user->latitude}}");
    var long = parseFloat("{{$user->longitude}}");
    var googleMapApi = "{{ config('custom.google_map_api_key') }}";
</script>
@endsection