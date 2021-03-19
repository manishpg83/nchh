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
                        <a href="#" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4" onclick="addUnit()"><i class="far fa-edit"></i>
                            Add</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="unitTable" width="100%">
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
<div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('admin/js/drug_unit.js')}}"></script>
<script type="text/javascript">
    var unitTable;
    var unitModal = $('#unitModal');
    var unitForm;

    //url
    var getUnitList = "{{Route('account.drug-units.index')}}";
    var addUnitUrl = "{{route('account.drug-units.create')}}";
    var editUnitUrl = "{{route('account.drug-units.edit',[':slug'])}}";
    var deleteUnitUrl = "{{route('account.drug-units.destroy',[':slug'])}}";
</script>
@endsection