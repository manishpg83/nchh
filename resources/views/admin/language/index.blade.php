@extends('admin.layouts.master')

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
                        <a href="#" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4" onclick="addLanguage()"><i class="far fa-edit"></i>
                            Add</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="languageTable" width="100%">
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
<div class="modal fade" id="languageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('page_script')
<script src="{{ asset('admin/js/language.js')}}"></script>
<script type="text/javascript">
    var languageTable;
    var languageModal = $('#languageModal');
    var languageForm;

    //url
    var getLanguageList = "{{Route('admin.languages.index')}}";
    var addLanguageUrl = "{{route('admin.languages.create')}}";
    var editLanguageUrl = "{{route('admin.languages.edit',[':slug'])}}";
    var deleteLanguageUrl = "{{route('admin.languages.destroy',[':slug'])}}";
</script>
@endsection