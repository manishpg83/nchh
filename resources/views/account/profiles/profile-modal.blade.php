<div class="modal-dialog modal-lg" role="document" id="verification_detail_modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Verify your {{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="card m-0">
                <div class="card-body neucrad_wizard">
                    <form id="profileForm" action="{{ route('account.edit-doctor-profile',[$user->id]) }}" method="post">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <h3>
                            <div class="wizard">
                                <div class="wizard-step">
                                    <div class="wizard-step-icon">
                                        <i class="far fa-user"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        User Account
                                    </div>
                                </div>
                            </div>
                        </h3>
                        <fieldset>
                            <div class="wizard-content mt-2">
                                <div class="wizard-pane">
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="name" value="{{$user->name}}" class="form-control required">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Specialty</label>
                                        <div class="col-sm-9">
                                            <select id="specialty_ids" name="specialty_ids[]" class="form-control select2 required" style="width: 100%">
                                                <option hidden value=""></option>
                                                @foreach ($specialist as $key => $value)
                                                <option value="{{$key}}" @if(!empty($user->detail->specialty_ids))
                                                    @if(in_array($key, $user->detail->specialty_ids)){{'selected'}}@endif
                                                    @endif>
                                                    {{$value}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 text-md-right text-left mt-2">Gender</label>
                                        <div class="col-sm-9">
                                            <div class="selectgroup w-100">
                                                <label class="selectgroup-item">
                                                    <input type="radio" name="gender" value="Male" class="selectgroup-input" @if($user->gender ==
                                                    'Male'){{'checked'}} @else {{'checked'}} @endif>
                                                    <span class="selectgroup-button">Male</span>
                                                </label>
                                                <label class="selectgroup-item">
                                                    <input type="radio" name="gender" value="Female" class="selectgroup-input" @if($user->gender ==
                                                    'Female'){{'checked'}}@endif>
                                                    <span class="selectgroup-button">Female</span>
                                                </label>
                                                <label class="selectgroup-item">
                                                    <input type="radio" name="gender" value="Other" class="selectgroup-input" @if($user->gender ==
                                                    'Other'){{'checked'}}@endif>
                                                    <span class="selectgroup-button">Other</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <h3>
                            <div class="wizard">
                                <div class="wizard-step">
                                    <div class="wizard-step-icon">
                                        <i class="fas fa-file-medical"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Medical Registration
                                    </div>
                                </div>
                            </div>
                        </h3>
                        <fieldset>
                            <div class="wizard-content mt-2">
                                <div class="wizard-pane">
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Registration number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="detail[registration_number]" class="form-control required" value="{{$user->detail->registration_number}}">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Registration Year</label>
                                        <div class="col-sm-9">
                                            <select class="form-control required" name="detail[registration_year]">
                                                <option value="">Select one</option>
                                                @if(config('view.Year'))
                                                @foreach(config('view.Year') as $year)
                                                <option value="{{$year}}" @if($year==$user->
                                                    detail->registration_year){{'selected'}}@endif>{{$year}}
                                                </option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Liecence number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="detail[liecence_number]" class="form-control required" value="{{$user->detail->liecence_number}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <h3>
                            <div class="wizard">
                                <div class="wizard-step">
                                    <div class="wizard-step-icon">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Education Qualification
                                    </div>
                                </div>
                            </div>
                        </h3>
                        <fieldset>
                            <div class="wizard-content mt-2">
                                <div class="wizard-pane">
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Degree</label>
                                        <div class="col-sm-9">
                                            <select class="form-control required" name="detail[degree]">
                                                <option value="">Select one</option>
                                                @if(config('view.Degree'))
                                                @foreach(config('view.Degree') as $degree)
                                                <option value="{{$degree}}" @if($degree==$user->
                                                    detail->degree){{'selected'}}@endif>{{$degree}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">College/Institute</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="detail[collage_or_institute]" class="form-control required" value="{{$user->detail->collage_or_institute}}">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Year Of completion</label>
                                        <div class="col-sm-9">
                                            <select class="form-control required" name="detail[year_of_completion]">
                                                <option value="">Select one</option>
                                                @if(config('view.Year'))
                                                @foreach(config('view.Year') as $year)
                                                <option value="{{$year}}" @if($year==$user->
                                                    detail->year_of_completion){{'selected'}}@endif>{{$year}}
                                                </option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Year of experience</label>
                                        <div class="col-sm-9">
                                            <input type="number" name="detail[experience]" class="form-control required" value="{{$user->detail->experience}}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-2"></div>
                                        <div class="col-lg-10 col-md-6">
                                            <div class="custom-control custom-checkbox terms_and_condition-error">
                                                <input type="checkbox" name="terms_and_condition" class="custom-control-input required" id="terms_and_condition">
                                                <label class="custom-control-label" for="terms_and_condition">I agree with the terms and conditions</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                </div>
                </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
</div>