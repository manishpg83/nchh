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
        <!-- <h2 class="section-title">Great Progress!</h2>
        <p class="section-lead">
        Your profile is just few steps away from going live.
        </p>
        -->
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div>
                        <a href="#" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4" onclick="addHealthFeed()"><i class="far fa-edit"></i>
                            Add</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="healthfeedTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Title</th>
                                        <th>Category</th>
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
<div class="modal fade" id="healthfeedModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/healthfeed.js')}}"></script>
<script type="text/javascript">
    var healthfeedTable;
    var healthfeedModal = $('#healthfeedModal');
    var healthfeedForm;

    //url
    var getHealthFeedList = "{{Route('account.healthfeed.index')}}";
    var viewFullHealthFeed = "{{route('account.healthfeed.show',[':slug'])}}";
    var editHealthFeedUrl = "{{route('account.healthfeed.edit',[':slug'])}}";
    var deleteHealthFeedUrl = "{{route('account.healthfeed.destroy',[':slug'])}}";
    var addHealthFeedUrl = "{{route('account.healthfeed.create')}}";
</script>
@endsection