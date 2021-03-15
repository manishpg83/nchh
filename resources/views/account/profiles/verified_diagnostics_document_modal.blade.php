<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">Verified Document</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pb-0">
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
                    <h4>Diagnostics Center Proof</h4>
                </div>
                <div class="accordion-body collapse show" id="panel-body-2" data-parent="#accordion" style="">
                    <div class="row">
                        <div class="col-10">
                            <img alt="Diagnostics Center Proof" style="max-width: 150px;" src="{{ $user->detail->diagnostics_proof }}">
                        </div>
                        <div class="col-md-2">
                            <a href="{{ $user->detail->diagnostics_proof }}" target="_blank" class="text-right"><i class="fa fa-download"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>