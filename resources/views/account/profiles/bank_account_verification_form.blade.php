<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="accountVerificationForm" action="{{route('account.user.bank.account.details.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-3">
                        <label for="bank_name">Bank Name*</label>
                        <input type="text" name="bank_name" class="form-control" id="bank_name" placeholder="Enter Bank Name" value="{{isset($user->bankDetail)? $user->bankDetail->bank_name : ''}}">
                        <span class="text-danger">
                            <strong id="bank_name-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="account_number">Account Number*</label>
                        <input type="text" autocomplete="off" name="account_number" class="form-control" id="account_number" placeholder="Enter Account Number" value="{{isset($user->bankDetail)? $user->bankDetail->account_number : ''}}">
                        <span class="text-danger">
                            <strong id="account_number-error"></strong>
                        </span>
                    </div>

                    <div class="col-sm-6 mb-3">
                        <label for="confirm_account_number">Confirm Account Number*</label>
                        <input type="password" autocomplete="off" name="confirm_account_number" class="form-control" id="confirm_account_number" placeholder="Re-Enter Account Number">
                        <span class="text-danger">
                            <strong id="confirm_account_number-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="ifsc_code">IFSC Code*</label>
                        <input type="text" name="ifsc_code" class="form-control" id="ifsc_code" placeholder="Enter IFSC Code" style="text-transform:uppercase" value="{{isset($user->bankDetail)? $user->bankDetail->ifsc_code : ''}}">
                        <span class="text-danger">
                            <strong id="ifsc_code-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label for="account_type">Account Type</label>
                        <input type="text" name="account_type" class="form-control" id="account_type" placeholder="Enter Account Type (Savings  / Current)"  value="{{isset($user->bankDetail)? $user->bankDetail->account_type : ''}}">
                        <span class="text-danger">
                            <strong id="account_type-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <label for="beneficiary_name">Beneficiary Name*</label>
                        <input type="text" name="beneficiary_name" class="form-control" id="beneficiary_name" placeholder="Enter Beneficiary Name" value="{{isset($user->bankDetail)? $user->bankDetail->beneficiary_name : ''}}">
                        <span class="text-danger">
                            <strong id="beneficiary_name-error"></strong>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-submit"><i id="loader" class=""></i>Submit</button>
                <button type="button" class="btn btn-secondary close-button" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>