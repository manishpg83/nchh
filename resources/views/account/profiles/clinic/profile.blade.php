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
        @if(count($user->clinicDocuments) > 0)
        <h2 class="section-title">
            {!! getUserProfileStatus($user->as_clinic_verified) !!}
            @if($user->as_clinic_verified == 3)
                <a href="javascript:void(0)" onclick="openRejectionReason(`{{ $user->clinic_rejection_reason }}`)">Click here for reason</a>
            @endif
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
                                        <div class="media-description text-muted">Document Registration</div>
                                        <div class="media-links">
                                            @if($user->as_clinic_verified == 2)
                                                <a href="javascript:void(0);" class="text-warning" data-toggle="tooltip" data-original-title="View Details" onclick="viewClinicverifieddocument()"><i class="far fa-check-circle"></i> Verified</a>
                                            @else
                                                @if(count($user->clinicDocuments) > 0)
                                                    <a href="javascript:;" id="clinicProfileDocument" class="btn btn-sm btn-warning text-white" onclick="loadClinicProfileVerificationModal()">Change</a>
                                                @else
                                                    <a href="javascript:;" id="clinicProfileDocument" class="btn btn-sm btn-success text-white" onclick="loadClinicProfileVerificationModal()">Add</a>
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
<div class="modal fade" id="clinicProfileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
<div class="modal fade" tabindex="-1" role="dialog" id="showRejectionReson" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">           
            <div class="modal-header">             
                <h5 class="modal-title">Reason</h5>             
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">               
                    <span aria-hidden="true">×</span>             
                </button>           
            </div>           
            <div class="modal-body">           
                <p class="rejection_reason"></p>
            </div>                    
        </div>       
    </div>    
</div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/clinic_profile.js')}}"></script>
<script type="text/javascript">
    var clinicProfileForm;
    var clinicProfileModal = $('#clinicProfileModal');
    var uploadDocumentFormURL = "{{route('account.clinic.profile.show')}}";
    var sendClinicVerificationUrl = "{{route('account.clinic.profile.store')}}";
</script>
@endsection