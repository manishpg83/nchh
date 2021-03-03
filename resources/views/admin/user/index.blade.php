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
                        <div class="form-group row mb-4">
                            <label class="col-form-label text-md-right col-12 col-md-9 col-lg-9">User Role</label>
                            <div class="col-sm-12 col-md-3">
                                <select name='role' onchange="javascript:this.form.submit();" class="form-control">
                                    <option value="">All</option>
                                    @foreach ($roles as $key => $r)
                                    <option value="{{$key}}">{{$r}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="userTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>email</th>
                                        <th>Role</th>
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
<div class="modal fade" id="userModal" role="dialog"></div>
@endsection
@section('page_script')
<script type="text/javascript" src="{{asset('admin/js/user.js')}}"></script>
<script type="text/javascript">
var userTable;
var userForm;
var userModal = $('#userModal');

var fileupload;
var filepreview;

//url
var remove_picture_url = "{{route('admin.remove-picture',[':slug'])}}";

var getUserList = "{{Route('admin.user.index')}}";
var viewFullUser = "{{route('admin.user.show',[':slug'])}}";
var editUserUrl = "{{route('admin.user.edit',[':slug'])}}";
var deleteUserUrl = "{{route('admin.user.destroy',[':slug'])}}";
</script>
@endsection