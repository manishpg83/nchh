@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="col-sm-12">
                        @if(session()->get('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                        @endif
                    </div>
                    <!-- <div>
                        <a href="{{route('account.practice.create')}}" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4"><i class="far fa-edit"></i>
                            Add</a>
                    </div> -->
                    <div class="card-body">

                        <form id="appointmentFilter">
                            <div class="row table-filter mb-2">
                                <div class="col-sm-12 col-xl-3 m-b-30">
                                    <label for="title" class="font-weight-bold">Search by type</label>
                                    <select id="type" name="appointment_type" class="form-control">
                                        <option value="all">All</option>
                                        <option value="online">Online</option>
                                        <option value="inperson">InPerson</option>
                                    </select>
                                </div>
                                <div class="col-sm-12 col-xl-3 m-b-30">
                                    <label for="title">&nbsp;</label>
                                    <input type="search" name="search" class="form-control" placeholder="Search by keyword">
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="appointmentListTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Doctor/Center</th>
                                        <th>Patient</th>
                                        <th>Appointment Date</th>
                                        <th>Appointment Time</th>
                                        <th>Appointment Type</th>
                                        <th>Address</th>
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
</section>

<!-- Modal -->
<div class="modal fade p-0" id="appointmentDetailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/appointment.js')}}"></script>
<script type="text/javascript">
    var appointmentListTable;
    var appointmentFilter = $('#appointmentFilter');
    var appointmentDetailModal = $('#appointmentDetailModal');
    var appointmentList = "{{Route('myAppointment')}}";
    var appointmentCancelUrl = "{{Route('appointment.cancel',':slug')}}";
    var appointmentViewUrl = "{{Route('appointment.view',':slug')}}";
</script>
@endsection