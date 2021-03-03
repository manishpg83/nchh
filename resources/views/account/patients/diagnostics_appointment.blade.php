@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item"><a href="{{route('account.patients.index')}}">Patients</a></div>
            <div class="breadcrumb-item">{{$name}}</div>
            <div class="breadcrumb-item active">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="appointmentTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Patient Name</th>
                                        <th>Patient Contact</th>
                                        <th>Services</th>
                                        <th>Estimated Price</th>
                                        <th>Appointment Date</th> 
                                        <th>Action</th> 
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

@endsection
@section('scripts')
<script src="{{ asset('account/js/page/patient.js')}}"></script>
<script type="text/javascript">
var appointmentTable;

//url 
var getPatientDiagnosticsAppointmentUrl = "{{route('account.patients.diagnostics.appointment', [$id,$name_slug])}}";
</script>
@endsection