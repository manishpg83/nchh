<div class="modal-dialog " role="document" id="verification_detail_modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Verify your {{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="otpVerificationForm" action="{{ route('account.verify-otp') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body mb-0">
                <input type="hidden" name="field" class="form-control" value="{{$field}}">
                <input type="hidden" name="value" value="{{$value}}">
                <div class="row" id="otpbox">
                    <div class="col-md-12 col-12">
                        <p class="mb-2 message"></p>
                        <div class="form-group col-md-12 col-12 mb-0">
                            <input type="number" name="otp" class="form-control" value="" placeholder="Enter your valid otp.">
                            <a href="javascript:;" class="btn btn-warning mt-2" onclick="resendOtp()">Resend OTP</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" onclick="verifyOTP($(this))">Verify</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>