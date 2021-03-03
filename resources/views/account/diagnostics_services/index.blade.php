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
                    <div>
                        <a href="javascript:;" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4" onclick="addDiagnosticsService()"><i class="far fa-edit"></i>
                            Add</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="diagnosticsServiceTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Information</th>
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
<div class="modal fade" id="diagnosticsServiceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/diagnostics_service.js')}}"></script>
<script type="text/javascript">
    var diagnosticsServiceTable;
    var diagnosticsServiceModal = $('#diagnosticsServiceModal');
    var diagnosticsServiceForm;

    //url
    var getDiagnosticsServiceList = "{{Route('account.diagnostics_services.index')}}";
    var addDiagnosticsServiceUrl = "{{route('account.diagnostics_services.create')}}";
    var editDiagnosticsServiceUrl = "{{route('account.diagnostics_services.edit',[':slug'])}}";
    var deleteDiagnosticsServiceUrl = "{{route('account.diagnostics_services.destroy',[':slug'])}}";
</script>
@endsection