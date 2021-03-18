<div class="modal-dialog modal-md" role="document" id="verification_detail_modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Verified Document</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="card m-0">
                <div class="card-body">
                    <div id="accordion">
                        <div class="accordion">
                            <div class="accordion-header" aria-expanded="true">
                                <h4>Identity Proof</h4>
                            </div>
                            <div class="accordion-body collapse show" id="panel-body-1" data-parent="#accordion" style="">
                                <div class="row">
                                    <div class="col-10">
                                        <img alt="Identity Proof" style="max-width: 150px;" src="{{ $user->detail->identity_proof }}">
                                    </div>
                                    <div class="col-md-2">
                                        <a href="{{ $user->detail->identity_proof }}" target="_blank" class="text-right"><i class="fa fa-download"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion">
                            <div class="accordion-header" aria-expanded="true">
                                <h4>Medical Registration</h4>
                            </div>
                            <div class="accordion-body collapse show" id="panel-body-2" data-parent="#accordion" style="">
                                <div class="row">
                                    <div class="col-10">
                                        <img alt="Medical Registraion" style="max-width: 150px;" src="{{ $user->detail->medical_registration_proof }}">
                                    </div>
                                    <div class="col-md-2">
                                        <a href="{{ $user->detail->medical_registration_proof }}" target="_blank" class="text-right"><i class="fa fa-download"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <label></label>
                </div>
            </div>
        </div>

    </div>
</div>