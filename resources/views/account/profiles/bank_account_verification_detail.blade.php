<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-3">
                        <label for="bank_name">Bank Name*</label>
                        <input type="text" class="form-control" value="{{isset($user->bankDetail)? $user->bankDetail->bank_name : ''}}" readonly>
                    </div>
                    <div class="col-sm-4 mb-3">
                        <label for="account_number">Account Number*</label>
                        <input type="text" class="form-control" value="{{isset($user->bankDetail)? $user->bankDetail->account_number : ''}}" readonly>
                     </div>

                    <div class="col-sm-4 mb-3">
                        <label for="ifsc_code">IFSC Code*</label>
                        <input type="text" class="form-control" style="text-transform:uppercase" value="{{isset($user->bankDetail)? $user->bankDetail->ifsc_code : ''}}" readonly>
                      </div>
                    <div class="col-sm-4 mb-3">
                        <label for="account_type">Account Type</label>
                        <input type="text" class="form-control" value="{{isset($user->bankDetail)? $user->bankDetail->account_type : ''}}" readonly>
                       </div>
                    <div class="col-sm-12 mb-3">
                        <label for="beneficiary_name">Beneficiary Name*</label>
                        <input type="text" class="form-control" value="{{isset($user->bankDetail)? $user->bankDetail->beneficiary_name : ''}}" readonly>
                       </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-button" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>