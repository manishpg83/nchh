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
                    <!-- <div>
                        <a href="{{route('account.practice.create')}}" class="btn btn-icon icon-left btn-primary float-right mt-3 mr-4"><i class="far fa-edit"></i>
                            Add</a>
                    </div> -->
                    <div class="card-body">
                        <form id="InviteForm" action="{{route('account.agent.invite.user.store')}}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group row mb-0">

                                <div class="col-md-12 col-sm-12 mb-4">
                                    <label for="subject">Subject<span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control" id="subject" placeholder="Enter Subject Line..">
                                    @error('subject')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $subject }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="col-md-12 col-sm-12 mb-4">
                                    <label for="recipients">Recipients<span class="text-danger">*</span>
                                        <small>(Enter multiple emails)</small>
                                    </label>
                                    <input type="text" name="recipient_emails" class="form-control" id="recipient_emails" placeholder="Enter List Of Recepient...">
                                </div>

                                <div class="col-md-12 col-sm-12 mb-4">
                                    <label for="content">Content<span class="text-danger">*</span></label>
                                    <textarea name="content" class="form-control summernote" id="content" placeholder="Enter Content..."></textarea>
                                    @error('content')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $content }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="col-md-12 col-sm-12 mb-4">
                                    <button type="submit" class="btn btn-primary" id="btn_submit">Send</button>
                                </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<!-- <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div> -->
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/invite_manager.js')}}"></script>
<script src="{{ asset('account/js/multiemail.js')}}"></script>
<script type="text/javascript">
    var InviteForm = $('#InviteForm');
    var is_email_exist_url = "{{route('account.email.isExist')}}";
</script>
@endsection