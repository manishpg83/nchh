@extends('account.layouts.master')

@section('content')
<section class="section medical_record_container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item"><a href="{{route('account.patients.index')}}">Patients</a></div>
            <div class="breadcrumb-item">{{$name}}</div>
            <div class="breadcrumb-item"><a href="{{route('account.patients.appointment', [$id,$name_slug])}}">Appointment</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-7">
                <div class="card author-box card-primary">
                    <div class="card-body">
                        <div class="author-box-left">
                            <img alt="image" src="{{asset('images/default.png')}}" class="rounded-circle author-box-picture">
                            <div class="clearfix"></div>
                        </div>
                        <div class="author-box-details">
                            <div class="author-box-name">
                                <a href="javascript:;">{{$appointment->patient_name}}</a>
                            </div>
                            <div class="author-box-job"><i class="fas fa-phone small"></i>
                                {{$appointment->patient_phone}}</div>
                            <div class="author-box-job"><i class="far fa-envelope"></i>
                                {{$appointment->patient_email}}</div>
                            <div class="author-box-job"><i class="fas fa-map-marker-alt"></i>
                                {!!ucwords($appointment->patient->full_address)!!}</div>
                            <div class="author-box-description">
                                <p class="mb-0">
                                    <h6 class="mb-0">{{date('d M, Y h:i a', strtotime($appointment->start_time) )}}
                                    </h6>
                                    <p class="text-success mb-0"> {{$appointment->appointment_type}}</p>
                                    <p class="text-info mb-0">
                                        {!!getAppointmentStatus($appointment->status)!!}
                                        @if($appointment->is_sample_pickup && $appointment->is_sample_pickup == 1)
                                        <span class="badge badge-pill badge-info">Sample Pickup From Home</span>
                                        @endif
                                    </p>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-lg-5">
                <div class="card profile-widget">
                    @if(checkPermission(['doctor','manager']))
                    <div class="profile-widget-header mb-0">
                        @if($appointment->practice->doctor_id == $appointment->practice->added_by)
                        <img alt="image" src="{{$appointment->practice->logo}}" class="rounded-circle profile-widget-picture">
                        @else
                        <img alt="image" src="{{$appointment->practice->addedBy->profile_picture}}" class="rounded-circle profile-widget-picture">
                        @endif
                    </div>
                    <div class="profile-widget-description pb-0 pt-0">
                        <div class="profile-widget-name">Appointment At
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div> {{$appointment->practice->name}}
                            </div>
                        </div>
                        <div class="author-box-job"><i class="fas fa-phone small"></i>
                            {{$appointment->practice->phone}}</div>
                        <div class="author-box-job"><i class="far fa-envelope"></i>
                            {{$appointment->practice->email}}</div>
                        <div class="author-box-job"><i class="fas fa-map-marker-alt"></i>
                            {{ucwords($appointment->practice->locality)}}, {{ucwords($appointment->practice->city)}}</div>
                    </div>
                    @endif
                    @if(checkPermission(['clinic','hospital']))
                    <div class="profile-widget-header mb-0">
                        <img alt="image" src="{{$appointment->doctor->profile_picture}}" class="rounded-circle profile-widget-picture">
                    </div>
                    <div class="profile-widget-description pb-0 pt-0">
                        <div class="profile-widget-name"> Appointment With
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div> {{$appointment->doctor->name}}
                            </div>
                        </div>
                        <div class="author-box-job"><i class="fas fa-phone small"></i>
                            {{$appointment->doctor->phone}}</div>
                        <div class="author-box-job"><i class="far fa-envelope"></i>
                            {{$appointment->doctor->email}}</div>
                        <div class="author-box-job"><i class="fas fa-map-marker-alt"></i>
                            {{ucwords($appointment->practice->locality)}}, {{ucwords($appointment->practice->city)}}</div>
                    </div>
                    @endif
                    @if(checkPermission(['diagnostics']))
                    <div class="profile-widget-header mb-0">
                        <img alt="image" src="{{$appointment->diagnostics->profile_picture}}" class="rounded-circle profile-widget-picture">
                    </div>
                    <div class="profile-widget-description pb-0 pt-0">
                        <div class="profile-widget-name"> Appointment At
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div> {{$appointment->diagnostics->name}}
                            </div>
                        </div>
                        <div class="author-box-job"><i class="fas fa-phone small"></i>
                            {{$appointment->diagnostics->phone}}</div>
                        <div class="author-box-job"><i class="far fa-envelope"></i>
                            {{$appointment->diagnostics->email}}</div>
                        <div class="author-box-job"><i class="fas fa-map-marker-alt"></i>
                            {{ucwords($appointment->practice->locality)}}, {{ucwords($appointment->practice->city)}}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="activities">
                    <div class="activity">
                        <div class="activity-detail w-100" id="tempId">
                            <div class="row" id="appointmentPrescription">
                                @if(checkPermission(['diagnostics']))
                                @include('account.patients.appointment_services')
                                @else
                                @include('account.patients.appointment_prescription')
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="activities">
                    <div class="activity">
                        <div class="activity-detail w-100">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <span class="bullet"></span>
                                    <span class="text-job">Files</span>
                                    <div class="float-right dropdown dropleft">
                                        <a href="javascript:;" class="dropdown-item has-icon" onclick="getAppointmentFile()">
                                            @if(!$appointment->files->isEmpty()) <i class="far fa-edit"></i> Edit @else
                                            <i class="fas fa-plus"></i> Add @endif</a>
                                    </div>
                                </div>
                                <div class="col-12" id="appointmentFile">
                                    @include('account.patients.appointment_file')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/patient.js')}}"></script>
<script type="text/javascript">
    $(".select2").select2();
    var prescriptionTable;
    var fileForm;
    var fileModal = $('#fileModal');
    var prescriptionForm;
    var appointment_id = "{{$appointment_id}}";
    var getPrescriptionList = "{{route('account.patients.appointment.detail', [$id,$name_slug,$appointment_id])}}";
    var getAppointmentFileUrl = "{{route('account.patients.appointment.file',[$appointment_id])}}";
    var deleteAppointmentFileUrl = "{{route('account.appointment.file.delete',':slug')}}";
    var editPrescriptionUrl = "{{route('account.prescription.edit',[$appointment_id])}}";
    var appendPrescriptionUrl = "{{route('account.prescription.append',[':slug',$appointment_id])}}";
    var addDrugUrl = "{{route('account.drug.create')}}";
    var sendToPharmacyUrl = "{{route('account.send.prescription')}}";
</script>
@endsection