@extends('admin.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('admin.dashboard')}}">Dashboard</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <!-- <h2 class="section-title">Great Progress!</h2>
<p class="section-lead">
Your profile is just few steps away from going live.
</p>
-->
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="doctorVerificationTable"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Profile</th>
                                        <th>Mobile</th>
                                        <th>Location</th>
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


<!-- Modal -->
<div class="modal fade" id="dataTableModal" role="dialog"></div>
@endsection
@section('page_script')
<script type="text/javascript" src="{{asset('admin/js/datatable.js')}}"></script>
<script type="text/javascript">
var doctorVerificationTable;
var dataTableModal = $('#dataTableModal');

var getDoctorVerificationList = "{{Route('admin.doctor.profile.verification')}}";
var getDoctorDetailUrl = "{{Route('admin.check.doctor.detail',[':slug'])}}";
var verifyDoctorDetailUrl = "{{Route('admin.doctor.profile.verify')}}";
</script>
@endsection