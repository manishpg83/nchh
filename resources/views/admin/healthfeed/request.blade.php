@extends('admin.layouts.master')

@section('content')
<section class="section user-profile-container">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('admin.dashboard.index')}}">Dashboard</a></div>
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
                            <table class="table table-striped table-bordered nowrap" id="healthfeedRequestTable"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Title</th>
                                        <th>Image</th>
                                        <th>Request</th>
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

<div class="modal fade healthfeedModal" id="large-Modal" tabindex="-1" role="dialog"></div>
@endsection
@section('page_script')
<script src="{{ asset('admin/js/healthfeed_reject.js')}}" type="text/javascript"></script>
<script type="text/javascript">
var $document = $(document);
var healthfeedRequestTable;
var healthfeedForm;
var healthfeedModal = $('.healthfeedModal');

var getHealthFeedRequestUrl = "{{ Route('admin.healthfeed.healthfeed-verification') }}";
var rejectHealthFeedUrl = "{{route('admin.healthfeed.reject',[':slug'])}}";
var changeStatusUrl = "{{ Route('admin.healthfeed.change.status') }}";
</script>
@endsection