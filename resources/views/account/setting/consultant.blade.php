@extends('account.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="javascript:;">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{route('account.setting.index')}}">Settings</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">All About Consultant Settings</h2>
        <p class="section-lead">
            You can adjust Consultant settings here.
        </p>

        <div class="row">
            <div class="col-md-12 col-12">
                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="card">
                    <form id="consultantSettingForm" action="{{route('account.setting.consultant.store')}}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                @if(isAuthorize('account.setting.consultant') && checkPermission(['doctor']))
                                <div class="form-group col-md-4 col-12">
                                    <label>Consultant As*</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="consultant_as" value="ONLINE" class="selectgroup-input" @if(!empty($user->setting->consultant_as) && $user->setting->consultant_as == "ONLINE"){{'checked'}}@else{{'checked'}}@endif>
                                            <span class="selectgroup-button">ONLINE</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="consultant_as" value="INPERSON" class="selectgroup-input" @if(!empty($user->setting->consultant_as) && $user->setting->consultant_as == "INPERSON"){{'checked'}}@endif>
                                            <span class="selectgroup-button">IN PERSON</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="consultant_as" value="BOTH" class="selectgroup-input" @if(!empty($user->setting->consultant_as) && $user->setting->consultant_as == "BOTH"){{'checked'}}@endif>
                                            <span class="selectgroup-button">BOTH</span>
                                        </label>
                                    </div>
                                    @error('consultant_as')
                                    <label id="consultant_as-error" class="error" for="consultant_as">{{ $message }}</label>
                                    @enderror
                                </div>
                                @endif
                                <div class="form-group col-md-4 col-12">
                                    <label>Consultant Duration*</label>
                                    <div class="selectgroup w-100">
                                        @if(isAuthorize('account.setting.consultant') && checkPermission(['doctor']))
                                        @if(config('view.Consultant_Duration'))
                                        @foreach(config('view.Consultant_Duration') as $key => $value)
                                        <label class="selectgroup-item">
                                            <input type="radio" name="consultant_duration" value="{{$value}}" class="selectgroup-input" @if(!empty($user->setting->consultant_duration) && $user->setting->consultant_duration == $value){{'checked'}}@endif>
                                            <span class="selectgroup-button">{{$value}} MINUTES</span>
                                        </label>
                                        @endforeach
                                        @endif
                                        @else
                                        @if(config('view.center_Duration'))
                                        @foreach(config('view.center_Duration') as $key => $value)
                                        <label class="selectgroup-item">
                                            <input type="radio" name="consultant_duration" value="{{$value}}" class="selectgroup-input" @if(!empty($user->setting->consultant_duration) && $user->setting->consultant_duration == $value){{'checked'}}@endif>
                                            <span class="selectgroup-button">{{$value}} MINUTES</span>
                                        </label>
                                        @endforeach
                                        @endif
                                        @endif
                                    </div>
                                    @error('consultant_duration')
                                    <label id="consultant_duration-error" class="error" for="consultant_duration">{{ $message }}</label>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label>Consultant Availability*</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="availability" value="1" class="selectgroup-input" @if(isset($user->setting->availability)){{'checked'}}@else{{'checked'}}@endif>
                                            <span class="selectgroup-button">YES</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="availability" value="0" class="selectgroup-input" @if(isset($user->setting->availability) && $user->setting->availability == 0){{'checked'}}@endif>
                                            <span class="selectgroup-button">NO</span>
                                        </label>
                                    </div>
                                    @error('availability')
                                    <label id="availability-error" class="error" for="availability">{{ $message }}</label>
                                    @enderror
                                </div>
                                @if(isAuthorize('account.setting.consultant') && checkPermission(['diagnostics']))
                                <div class="form-group col-md-4 col-12">
                                    <label>Sample Pickup Service*</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="is_sample_pickup" value="1" class="selectgroup-input" @if(isset($user->setting->is_sample_pickup)){{'checked'}}@else{{'checked'}}@endif>
                                            <span class="selectgroup-button">YES</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="is_sample_pickup" value="0" class="selectgroup-input" @if(isset($user->setting->is_sample_pickup) && $user->setting->is_sample_pickup == 0){{'checked'}}@endif>
                                            <span class="selectgroup-button">NO</span>
                                        </label>
                                    </div>
                                    @error('is_sample_pickup')
                                    <label id="is_sample_pickup-error" class="error" for="is_sample_pickup">{{ $message }}</label>
                                    @enderror
                                </div>
                                @endif
                            </div>
                            <div class="row">
                                @if(isAuthorize('account.setting.consultant') && checkPermission(['doctor']))
                                <div class="form-group col-md-4 col-12">
                                    <label>Clinic / Hospital / Pharmacy can add me as staff?*</label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="do_service_at_other_establishment" value="1" class="selectgroup-input" @if(isset($user->setting->do_service_at_other_establishment)){{'checked'}}@else{{'checked'}}@endif>
                                            <span class="selectgroup-button">YES</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="do_service_at_other_establishment" value="0" class="selectgroup-input" @if(isset($user->setting->do_service_at_other_establishment) && $user->setting->do_service_at_other_establishment == 0){{'checked'}}@endif>
                                            <span class="selectgroup-button">NO</span>
                                        </label>
                                    </div>
                                    @error('do_service_at_other_establishment')
                                    <label id="do_service_at_other_establishment-error" class="error" for="do_service_at_other_establishment">{{ $message }}</label>
                                    @enderror
                                </div>
                                @endif
                                <div class="form-group col-md-8 col-12">
                                    <div class="row" id="date_box" @if(isset($user->setting->availability) && $user->setting->availability == 1) style="display: none;" @endif>
                                        <div class=" form-group col-md-6 col-12">
                                            <label>Unavailability Start Date*</label>
                                            <input type="date" name="unavailability_start_date" class="form-control" id="unavailability_start_date" value="{{isset($user->setting->unavailability_start_date) ? $user->setting->unavailability_start_date : ''}}" data-date="yyyy-mm-dd" min="{{today()->format('Y-m-d')}}">
                                            @error('unavailability_start_date')
                                            <label id="unavailability_start_date-error" class="error" for="unavailability_start_date">{{ $message }}</label>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6 col-12">
                                            <label>Unavailability End Date*</label>
                                            <input type="date" name="unavailability_end_date" class="form-control" id="unavailability_end_date" value="{{isset($user->setting->unavailability_end_date) ? $user->setting->unavailability_end_date : '' }}" data-date="yyyy-mm-dd" min="{{today()->format('Y-m-d')}}">
                                            @error('unavailability_end_date')
                                            <label id="unavailability_end_date-error" class="error" for="unavailability_end_date">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <div class="row" id="pickup_charge" @if(isset($user->setting->is_sample_pickup) && $user->setting->is_sample_pickup == 0) style="display: none;" @endif>
                                        <div class=" form-group col-md-12 col-12">
                                            <label>Sample Collection Charge*</label>
                                            <input type="number" name="sample_pickup_charge" class="form-control" id="sample_pickup_charge" value="{{isset($user->setting->sample_pickup_charge) ? $user->setting->sample_pickup_charge : ''}}">
                                            @error('sample_pickup_charge')
                                            <label id="sample_pickup_charge-error" class="error" for="sample_pickup_charge">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-whitesmoke text-md-right">
                                <button type="submit" class="btn btn-success btn-submit"><i id="loader" class=""></i>Save
                                    Change</button>
                                <button type="reset" class="btn btn-secondary close-button">Reset</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
</section>
@endsection
@section('scripts')
<script type="text/javascript">
    var consultantSettingForm = $('#consultantSettingForm');
    var form = consultantSettingForm.validate({
        rules: {
            consultant_as: {
                required: true
            },
            consultant_duration: {
                required: true
            },
            availability: {
                required: true
            },
            unavailability_start_date: {
                validDate: true,
                dateBefore: '#unavailability_end_date',
                required: true
            },
            unavailability_end_date: {
                validDate: true,
                required: true,
                dateAfter: '#unavailability_start_date'
            },
            is_sample_pickup: {
                required: true
            },
            sample_pickup_charge: {
                required: true,
                min:1
            },
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    $('input[name=availability]').change(function() {
        if ($(this).val() == 0) {
            consultantSettingForm.find('#date_box').fadeIn();
        } else {
            consultantSettingForm.find('#date_box').fadeOut();
        }
        form.resetForm();
    })

    $('input[name=is_sample_pickup]').change(function() {
        if ($(this).val() == 1) {
            consultantSettingForm.find('#pickup_charge').fadeIn();
        } else {
            consultantSettingForm.find('#pickup_charge').fadeOut();
        }
        form.resetForm();
    })
</script>
@endsection