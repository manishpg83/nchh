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
                        <form id="appointmentFilter">
                            <div class="row table-filter mb-2">
                                <div class="col-sm-12 col-xl-3 m-b-30">
                                    <label for="title" class="font-weight-bold">Search by type</label>
                                    <select id="type" name="appointment_type" class="form-control">
                                        <option value="all">All</option>
                                        <option value="create">Booked</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="completed">Completed</option>
                                        <option value="inperson">InPerson</option>
                                        <option value="online">Online</option>
                                    </select>
                                </div>
                                <div class="col-sm-12 col-xl-3 m-b-30">
                                    <label for="title">&nbsp;</label>
                                    <input type="search" name="search" class="form-control" placeholder="Search by keyword">
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="appointmentTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Patient Name</th>
                                        <th>Patient Contact</th>
                                        <th>Appointment Date</th>
                                        <th>Clinic</th>
                                        <th>Status</th>
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

var appointmentFilter = $('#appointmentFilter');
//url 
var getPatientAppointmentUrl = "{{route('account.patients.appointment', [$id,$name_slug])}}";
</script>
@endsection