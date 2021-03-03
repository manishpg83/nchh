<div class="modal-dialog modal-xl" role="document" id="viewuser">
    <div class="modal-content">
        <div class="modal-header pt-2 pb-2">
            <h5 class="modal-title" id="modellabel">{{$user->name}}</h5>
        </div>
        <div class="card">
            <div class="modal-body">
                <form method="post" action="{{ route('admin.user.update',$user->id) }}" id="userForm">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="card-body">
                        <button type="submit" class="btn btn-primary float-right"
                            onclick="submitUserProfileForm($(this))">Save
                            Changes</button>
                        <div class="row">
                            <div class="col-md-5 col-12">
                                <div class="card profile-widget">
                                    <div class="profile-widget-header">
                                        <img alt="image" src="{{$user->profile_picture}}"
                                            class="rounded-circle profile-widget-picture" id="previewPicture">
                                        <input type="file" name="profile_picture" class="mt-2" id="profile_picture"
                                            style="display: none">
                                        <div class="profile-widget-items">
                                            <div class="profile-widget-item">
                                                <div class="profile-widget-item-label mb-2">Pick a photo from your
                                                    computer</div>
                                                <div class="profile-widget-item-value badges" id="reloadProfile">
                                                    @if($user->image_name == "default.png")
                                                    <a href="javascript:;" class="nav-link badge badge-primary"
                                                        onclick="browsePicture()">Add Photo</a>
                                                    @else
                                                    <a href="javascript:;" class="nav-link badge badge-primary"
                                                        onclick="browsePicture()">Edit</a>
                                                    <a href="javascript:;" class="nav-link badge badge-light"
                                                        onclick="removeUserPicture('{{$user->id}}',this)">Remove</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-12">
                                <label>Name*</label>
                                <input type="text" class="form-control" name="name" value="{{$user->name}}">
                            </div>
                        </div>

                        <div class="row primarybox">
                            <div class="form-group col-md-4 col-12">
                                <label>Phone number*</label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="Enter your phone number" value="{{$user->phone}}" disabled="disabled">
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>Email address*</label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="Enter your email address" disabled="disabled" value="{{$user->email}}">
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <div class="selectgroup selectgroup-pills">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="gender" id="gender" value="Male"
                                                class="selectgroup-input" @if($user->gender ==
                                            "Male"){{'checked=""'}}@endif>
                                            <span class="selectgroup-button selectgroup-button-icon">Male</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="gender" id="gender" value="Female"
                                                class="selectgroup-input" @if($user->gender ==
                                            "Female"){{'checked=""'}}@endif>
                                            <span class="selectgroup-button selectgroup-button-icon">Female</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="gender" id="gender" value="Other"
                                                class="selectgroup-input" @if($user->gender ==
                                            "Other"){{'checked=""'}}@endif>
                                            <span class="selectgroup-button selectgroup-button-icon">Other</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>Date of birth</label>
                                <input type="date" name="dob" class="form-control" value="{{$user->dob}}"
                                    data-date="yyyy-mm-dd">
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>Blood group</label>
                                <select id="blood_group" name="blood_group" class="form-control">
                                    <option hidden></option>
                                    @if(config('view.Bloodgroup'))
                                    @foreach(config('view.Bloodgroup') as $group)
                                    <option value="{{$group}}" @if($user->blood_group ==
                                        $group){{'selected'}}@endif>{{$group}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>Timezone</label>
                                {!!$timezonelist!!}
                            </div>
                        </div>
                        <div class="card-header pl-0">
                            <h4>Address</h4>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4 col-12">
                                <label>House No./ Street Name</label>
                                <input type="text" name="address" class="form-control" value="{{$user->address}}">
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>City</label>
                                <input type="text" name="city" class="form-control" value="{{$user->city}}">
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>State</label>
                                <input type="text" name="state" class="form-control" value="{{$user->state}}">
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>Country</label>
                                <select id="country" name="country" class="form-control select2">
                                    <option hidden></option>
                                    @foreach ($country ?? '' as $key => $value)
                                    <option value="{{$value}}" @if($value==$user->
                                        country){{'selected'}}@endif
                                        >{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="{{$user->pincode}}">
                            </div>
                        </div>
                        @if(checkPermission(['clinic']))
                        <div class="card-header pl-0">
                            <h4>Other Information</h4>
                        </div>
                        <div class="row other_information_card">
                            <div class="form-group col-md-4 col-12">
                                <label>Speciality</label>
                                <select id="specialty_ids" name="specialty_ids[]" class="form-control select2" multiple>
                                    <option hidden></option>
                                    @foreach ($specialty as $key => $value)
                                    <option value="{{$key}}" @if(!empty($user->detail->specialty_ids))
                                        @if(in_array($key, $user->detail->specialty_ids)){{'selected'}}@endif @endif>
                                        {{$value}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>Website</label>
                                <div class="input-group">
                                    <input type="url" name="website" class="form-control" placeholder="Enter Wbsite"
                                        value="{{$user->detail->website}}">
                                </div>
                            </div>
                            <div class="form-group col-md-4 col-12">
                                <label>GSTIN</label>
                                <div class="input-group">
                                    <input type="text" name="gst_in" class="form-control" placeholder="Enter GSTIN"
                                        value="{{$user->detail->gst_in}}">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>