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
        @if(!empty($user->bankDetail->account_number))
        <h2 class="section-title">
            {!! getUserProfileStatus($user->is_bank_verified) !!}
        </h2>
        <p class="section-lead"></p>
        @else
        <h2 class="section-title">
            Hello {{$user->name}}! <br><span class="section-lead">Lets verify your bank details.
        </h2>
        <p class="section-lead">
            Your profile is just one steps away from going verified.
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
                                        <div class="media-title mb-1">Bank Account Verifications</div>
                                        <div class="media-description text-muted">PLease before verifying your bank account you must have a bank passbook.</div>
                                        <div class="media-links">
                                            @if($user->is_bank_verified == 2)
                                            <a href="javascript:;" class="text-warning" data-toggle="tooltip" data-original-title="View Details" onclick="viewBankDetail()"><i class="far fa-check-circle"></i> Verified</a>
                                            @else
                                            @if(!empty($user->bankDetail->account_number))
                                            <a href="javascript:;" id="bankAccountDetail" class="btn btn-sm btn-warning text-white" onclick="loadBankAccountVerificationModal()">Change</a>
                                            @else
                                            <a href="javascript:;" id="bankAccountDetail" class="btn btn-sm btn-success text-white" onclick="loadBankAccountVerificationModal()">Add</a>
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
<div class="modal fade" id="accountVerificationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/bank_account_verification.js')}}"></script>
<script type="text/javascript">
    var accountVerificationForm;
    var accountVerificationModal = $('#accountVerificationModal');
    var uploadBankDetailsFormURL = "{{route('account.user.bank.account.details.show')}}"; 
    var viewBankDetailsURL = "{{route('account.user.bank.account.details')}}"; 
</script>
@endsection