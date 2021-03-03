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
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="hospitalTable" width="100%">
                                <thead>
                                    <tr>
                                    <th>Id</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Doctors</th>
                                        <th>Locality</th>
                                        <th>Specialty</th>
                                        <th>Services</th>
                                        <th>Gallery</th>
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
var hospitalTable;
var dataTableModal = $('#dataTableModal');

//url
var getHospitalList = "{{Route('admin.getHospital')}}";
</script>
@endsection