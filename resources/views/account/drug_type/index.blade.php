@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div>
                        <a href="#" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4" onclick="addType()"><i class="far fa-edit"></i>
                            Add</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="typeTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
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
<div class="modal fade" id="typeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('admin/js/drug_type.js')}}"></script>
<script type="text/javascript">
    var typeTable;
    var typeModal = $('#typeModal');
    var typeForm;

    //url
    var getTypeList = "{{Route('account.drug-types.index')}}";
    var addTypeUrl = "{{route('account.drug-types.create')}}";
    var editTypeUrl = "{{route('account.drug-types.edit',[':slug'])}}";
    var deleteTypeUrl = "{{route('account.drug-types.destroy',[':slug'])}}";
</script>
@endsection