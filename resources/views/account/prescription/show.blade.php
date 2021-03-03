@extends('account.layouts.master')

@section('content')
<section class="section medical_record_container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item"><a href="{{route('account.prescription.index')}}">Prescription Request</a></div>
            <div class="breadcrumb-item">{{$name}}</div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-6">
                <div class="card profile-widget">
                    <div class="profile-widget-header mb-0">
                        <img alt="image" src="{{asset('images/default.png')}}" class="rounded-circle profile-widget-picture">
                    </div>
                    <div class="profile-widget-description pb-1 pt-0">
                        <div class="profile-widget-name"> Patient
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div> {{$prescriptions->appointment->patient_name}}
                            </div>
                        </div>
                        <div class="author-box-job"><i class="fas fa-phone round-icon"></i>
                            {{$prescriptions->appointment->patient_phone}}</div>
                        <div class="author-box-job"><i class="far fa-envelope round-icon"></i>
                            {{$prescriptions->appointment->patient_email}}</div>
                            <div class="author-box-job"><i class="fas fa-map-marker-alt round-icon"></i>
                            {{ucwords($prescriptions->appointment->patient->locality)}}, {{ucwords($prescriptions->appointment->patient->city)}}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-lg-6">
                <div class="card profile-widget">
                    <div class="profile-widget-header mb-0">
                        <img alt="image" src="{{$prescriptions->appointment->doctor->profile_picture}}" class="rounded-circle profile-widget-picture">
                    </div>
                    <div class="profile-widget-description pb-1 pt-0">
                        <div class="profile-widget-name"> Doctor
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div> {{$prescriptions->appointment->doctor->name}}
                            </div>
                        </div>
                        <div class="author-box-job"><i class="fas fa-phone round-icon"></i>
                        +{{$prescriptions->appointment->doctor->dialcode}} {{$prescriptions->appointment->doctor->phone}}</div>
                        <div class="author-box-job"><i class="far fa-envelope round-icon"></i>
                            {{$prescriptions->appointment->doctor->email}}</div>
                        <div class="author-box-job"><i class="fas fa-map-marker-alt round-icon"></i>
                            {{ucwords($prescriptions->appointment->practice->locality)}}, {{ucwords($prescriptions->appointment->practice->city)}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Prescription</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">DRUG</th>
                                    <th scope="col">FREQUENCY</th>
                                    <th scope="col">DURATION</th>
                                    <th scope="col">INSTRUCTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!$prescriptions->appointment->prescriptions->isEmpty())
                                @foreach($prescriptions->appointment->prescriptions as $p)
                                <tr>
                                    <td>{{$p->drug}}</td>
                                    <td>{{$p->frequency}}</td>
                                    <td>{{$p->duration}} day(s)</td>
                                    <td>{{$p->intake}}, {{$p->intake_instruction}}</td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="prescriptionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/prescription.js')}}"></script>
<script type="text/javascript">

</script>
@endsection