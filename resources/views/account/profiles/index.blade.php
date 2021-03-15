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
        @if(!empty($user->detail->medical_registration_proof) && $user->detail->medical_registration_proof != '')
        <h2 class="section-title">
            {!! getUserProfileStatus($user->as_doctor_verified) !!}
        </h2>
        <p class="section-lead"></p>
        @else
        <h2 class="section-title">
            Hello {{$user->name}}! <br><span class="section-lead">Lets create your dedicated profile.
        </h2>
        <p class="section-lead">
            Your profile is just few steps away from going live.
        </p>
        @endif
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12 col-sm-12 col-lg-7 p-0">
                            <ul class="list-unstyled list-unstyled-border list-unstyled-noborder primarybox">
                                <li class="media mt-4">
                                    <img alt="image" class="mr-3 rounded-circle" width="70" src="{{asset('account/img/avatar/avatar-1.png')}}">
                                    <div class="media-body">
                                        <div class="media-title mb-1">Profile Details</div>
                                        <div class="media-description text-muted">Doctor’s basic details, medical
                                            registration, education qualification, establishment details etc.</div>
                                        <div class="media-links">
                                            @if($user->as_doctor_verified != 2)
                                            <a href="javascript:;" id="profileDetail" class="btn btn-sm {{$step != 1 ? 'btn-success' : 'btn-warning' }} text-white" onclick="loadProfileDetailsModal()">{{($step != 1) ? 'Change' : 'Add' }}</a>
                                            @else
                                                <a href="javascript:;" class="text-warning" data-toggle="tooltip" data-original-title="View Details" onclick="viewdoctorProfile()"><i class="far fa-check-circle"></i> Verified</a>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                                <hr>

                                <li class="media mt-4">
                                    <img alt="image" class="mr-3 rounded-circle" width="70" src="{{asset('account/img/avatar/avatar-2.png')}}">
                                    <div class="media-body">
                                        <div class="media-title mb-1">Profile Document Verification</div>
                                        <div class="media-description text-muted">Doctor identity proof, registration proof, establishment ownership proof etc.</div>
                                        <div class="media-links">
                                            @if($step != 1)
                                            @if($user->as_doctor_verified == 2)
                                            <span class="text-warning"><i class="far fa-check-circle"></i> Verified</span>
                                            @else
                                            <a href="javascript:;" id="profileDocument" class="btn btn-sm {{($step != 2) ? 'btn-success':'btn-warning'}} text-white" onclick="loadProfileVerificationModal()">{{ ($step != 2) ? 'Change' : 'Add' }}</a>
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
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/profile.js')}}"></script>
<script type="text/javascript">
    var userProfile;
    var profileModal = $('#profileModal');

    var userTimings = '{!! isset($user->timing->schedule) ? $user->timing->schedule : "" !!}';
    // console.log(userTimings);
    /* Routes */
    var getUser = "{{route('account.getUser')}}";
    var showProfileDetails_url = "{{route('account.profile.details.show')}}";
    var showProfileDocumentVerification_url = "{{route('account.profile.document.verification.show')}}";
    var storeProfileDocumentVerification_url = "{{route('account.profile.document.verification.store')}}";
    var showProfileEstablishment_url = "{{route('account.profile.establishment.show')}}";
    var storeEstablishmentDetails_url = "{{route('account.profile.establishment.details.store')}}";
    var storeEstablishmentTimings_url = "{{route('account.profile.establishment.timings.store')}}";
</script>
@endsection