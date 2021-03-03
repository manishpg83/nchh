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
                     <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered nowrap" id="ratingTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Rating</th>
                                        <th>Review</th>
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
<div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/rating.js')}}"></script>
<script type="text/javascript">
    var ratingTable;
    var ratingModal = $('#ratingModal');
    var ratingForm;

    //url
    var getRatingList = "{{Route('account.rating.index')}}";
    var editRatingUrl = "{{route('account.rating.edit',[':slug'])}}";
    var deleteRatingUrl = "{{route('account.rating.destroy',[':slug'])}}";
</script>
@endsection