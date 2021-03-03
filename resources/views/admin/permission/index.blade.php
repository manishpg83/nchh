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
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <form id="permissionForm" action="{{route('admin.permission.set')}}" enctype="multipart/form-data" method="post">
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Select Role</label>
                                        <select class="form-control select2" name="role_id">
                                            @if(!empty($role))
                                            @foreach($role as $r)
                                            <option value="{{$r->id}}">{{$r->name}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                 <div class="form-group row" id="permission_container">
                                    {!!$html!!}
                                </div>
                                <button type="submit" class="btn btn-icon icon-left btn-primary"><i class="fas fa-user-lock"></i>Set Permission</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('page_script')
<script src="{{ asset('admin/js/permission.js')}}"></script>
<script type="text/javascript">
     
    var container = $(document).find('#permission_container');
    /*Url List*/
    var loadRoutesList = "{{Route('admin.permission.route.index')}}";
</script>
@endsection