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
        @if(!empty($user->detail->identity_proof) && $user->detail->identity_proof_name != 'no_image.png')
        <h2 class="section-title">
            {!! getUserProfileStatus($user->as_diagnostics_verified) !!}
        </h2>
        <p class="section-lead"></p>
        @else
        <h2 class="section-title">
            Hello {{$user->name}}! <br><span class="section-lead">Lets create your dedicated profile.
        </h2>
        <p class="section-lead">
            Your profile is just one steps away from going live.
        </p>
        @endif
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12 col-sm-12 col-lg-7 p-0">
                            <ul class="list-unstyled list-unstyled-border list-unstyled-noborder primarybox">
                                <li class="media mt-4">
                                    <img alt="image" class="mr-3 rounded-circle" width="70" src="{{asset('account/img/avatar/avatar-2.png')}}">
                                    <div class="media-body">
                                        <div class="media-title mb-1">Profile Document Verifications</div>
                                        <div class="media-description text-muted">Identity proof, Diagnostics Center Registration Document</div>
                                        <div class="media-links">
                                            @if($user->as_diagnostics_verified == 2)
                                            <a href="javascript:;" class="text-warning" data-toggle="tooltip" data-original-title="View Details" onclick="viewdiagnosticsverifieddocument()"><i class="far fa-check-circle"></i> Verified</a>
                                            @else
                                            @if(!empty($user->detail->identity_proof) && $user->detail->identity_proof_name != 'no_image.png' && !empty($user->detail->diagnostics_proof) && $user->detail->diagnostics_proof_name != 'no_image.png')
                                            <a href="javascript:;" id="diagnosticsProfileDocument" class="btn btn-sm btn-warning text-white" onclick="loadDiagnosticsProfileVerificationModal()">Change</a>
                                            @else
                                            <a href="javascript:;" id="diagnosticsProfileDocument" class="btn btn-sm btn-success text-white" onclick="loadDiagnosticsProfileVerificationModal()">Add</a>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                </li>
                                <hr>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="diagnosticsProfileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/diagnostics_profile.js')}}"></script>
<script type="text/javascript">
    var diagnosticsProfileForm;
    var diagnosticsProfileModal = $('#diagnosticsProfileModal');
    var uploadDocumentFormURL = "{{route('account.diagnostics.profile.details.show')}}";
    var sendDiagnosticsVerificationUrl = "{{route('account.diagnostics.profile.document.verification.store')}}";
</script>
@endsection