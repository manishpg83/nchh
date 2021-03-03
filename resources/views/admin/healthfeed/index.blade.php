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
                    <div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="healthfeedTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Title</th>
                                        <th>Image</th>
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

<!-- Modal -->
<div class="modal fade" id="healthfeedModal" role="dialog"></div>
@endsection
@section('page_script')
<script src="{{ asset('admin/js/healthfeed.js')}}"></script>
<script type="text/javascript">
var healthfeedTable;
var healthfeedForm;
var healthfeedModal = $('#healthfeedModal');

var getHealthFeedList = "{{Route('admin.healthfeed.index')}}";
var viewFullHealthFeed = "{{route('admin.healthfeed.show',[':slug'])}}";
</script>
@endsection