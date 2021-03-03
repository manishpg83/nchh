@extends('admin.layouts.master')

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
                    <form id="commissionSettingForm" action="{{route('admin.setting.commission.store')}}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-5 col-12">
                                    <div class="row" id="neucrad_commission">
                                        <div class=" form-group col-md-12 col-12">
                                            <label>NC Health Hub Commission* <small>(% Total Commission From Payment)</small></label>
                                            <input type="number" name="neucrad_commission" class="form-control" id="neucrad_commission" value="{{isset($user->commission->neucrad_commission) ? $user->commission->neucrad_commission : ''}}">
                                            @error('neucrad_commission')
                                            <label id="neucrad_commission-error" class="error" for="neucrad_commission">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 col-12">
                                    <div class="row" id="patient_agent">
                                        <div class=" form-group col-md-12 col-12">
                                            <label>Patient Agent*</label>
                                            <input type="number" name="patient_agent" class="form-control" id="patient_agent" value="{{isset($user->commission->patient_agent) ? $user->commission->patient_agent : ''}}">
                                            @error('patient_agent')
                                            <label id="patient_agent-error" class="error" for="patient_agent">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <div class="row" id="other_agent">
                                        <div class=" form-group col-md-12 col-12">
                                            <label>Other Agent* <small>(Doctor, Clinic, Hospital, Diagnostics)</small></label>
                                            <input type="number" name="other_agent" class="form-control" id="other_agent" value="{{isset($user->commission->other_agent) ? $user->commission->other_agent : ''}}">
                                            @error('other_agent')
                                            <label id="other_agent-error" class="error" for="other_agent">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <div class="row" id="neucrad">
                                        <div class=" form-group col-md-12 col-12">
                                            <label>NC Health Hub*</label>
                                            <input type="number" name="neucrad" class="form-control" id="neucrad" value="100" readonly>
                                            @error('neucrad')
                                            <label id="neucrad-error" class="error" for="neucrad">{{ $message }}</label>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-md">
                                        <tr class="border-bottom small">
                                            <th class="text-center">Patient Agent</th>
                                            <th class="text-center">Other Agent</th>
                                            <th class="text-center">NC Health Hub</th>
                                        </tr>
                                        <tr class="small">
                                            <td class="text-center" id="patient_case_1">{{isset($user->commission->patient_agent) ? $user->commission->patient_agent : 0}}</td>
                                            <td class="text-center" id="other_case_1">{{isset($user->commission->other_agent) ? $user->commission->other_agent : 0}}</td>
                                            <td class="text-center" id="case_1">{{$case_1}}</td>
                                        </tr>
                                        <tr class="small">
                                            <td class="text-center" id="patient_case_2">0</td>
                                            <td class="text-center" id="other_case_2">{{isset($user->commission->other_agent) ? $user->commission->other_agent : 0}}</td>
                                            <td class="text-center" id="case_2">{{$case_2}}</td>
                                        </tr>
                                        <tr class="small">
                                            <td class="text-center" id="patient_case_3">{{isset($user->commission->patient_agent) ? $user->commission->patient_agent : 0}}</td>
                                            <td class="text-center" id="other_case_3">0</td>
                                            <td class="text-center" id="case_3">{{$case_3}}</td>
                                        </tr>
                                        <tr class="small">
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">100</td>
                                        </tr>
                                    </table>
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
@section('page_script')
<script type="text/javascript">

$(document).ready(function() {
    console.log( "ready!" );
});
    var commissionSettingForm = $('#commissionSettingForm');
    var form = commissionSettingForm.validate({
        rules: {
            neucrad_commission: {
                required: true,
                max: 100,
                min: 0
            },
            patient_agent: {
                required: true,
                max: 100,
                min: 0
            },
            other_agent: {
                required: true,
                max: 100,
                min: 0
            },
            neucrad: {
                required: true,
                max: 100,
                min: 0
            },
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    $('input[name=patient_agent]').on("input", function() {
        var patient_agent = parseInt($('input[name=patient_agent]').val());
        var other_agent = parseInt($('input[name=other_agent]').val());
        if (patient_agent >= 0 && other_agent >= 0) {
            c('working');
            var neucrad = 100;
            var total = 0;
            var total = neucrad - (patient_agent + other_agent);
            var admin_case_1 = neucrad - (patient_agent + other_agent);
            var admin_case_2 = neucrad - patient_agent;
            var admin_case_3 = neucrad - other_agent;
            commissionSettingForm.find('input[name=neucrad]').val(total)

            commissionSettingForm.find('#patient_case_1').html(patient_agent)
            commissionSettingForm.find('#patient_case_3').html(patient_agent)
            commissionSettingForm.find('#other_case_1').html(other_agent)
            commissionSettingForm.find('#other_case_2').html(other_agent)
            commissionSettingForm.find('#case_1').html(admin_case_1)
            commissionSettingForm.find('#case_2').html(admin_case_2)
            commissionSettingForm.find('#case_3').html(admin_case_3)
        }
    })

    $('input[name=other_agent]').on("input", function() {
        var patient_agent = parseInt($('input[name=patient_agent]').val());
        var other_agent = parseInt($('input[name=other_agent]').val());
        if (patient_agent >= 0 && other_agent >= 0) {
            var neucrad = 100;
            var total = 0;
            var total = neucrad - (patient_agent + other_agent);
            var admin_case_1 = neucrad - (patient_agent + other_agent);
            var admin_case_2 = neucrad - patient_agent;
            var admin_case_3 = neucrad - other_agent;
            commissionSettingForm.find('input[name=neucrad]').val(total)

            commissionSettingForm.find('#patient_case_1').html(patient_agent)
            commissionSettingForm.find('#patient_case_3').html(patient_agent)
            commissionSettingForm.find('#other_case_1').html(other_agent)
            commissionSettingForm.find('#other_case_2').html(other_agent)
            commissionSettingForm.find('#case_1').html(admin_case_1)
            commissionSettingForm.find('#case_2').html(admin_case_2)
            commissionSettingForm.find('#case_3').html(admin_case_3)
        }
    })
</script>
@endsection