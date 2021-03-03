<div class="modal-dialog modal-md" role="document" id="verification_detail_modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Verify your {{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="card m-0">
                <div class="card-body neucrad_wizard_document">
                    <form id="uploadDocumentForm" action="#" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <h3>
                            <div class="wizard">
                                <div class="wizard-step">
                                    <div class="wizard-step-icon">
                                        <i class="far fa-id-badge"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Identity Proof
                                    </div>
                                </div>
                            </div>
                        </h3>
                        <fieldset>
                            <div id="uploadIdentity" class="dropzone">
                                <div class="dz-message">Drop image here or click to Upload</div>
                            </div>
                        </fieldset>

                        <h3>
                            <div class="wizard">
                                <div class="wizard-step">
                                    <div class="wizard-step-icon">
                                        <i class="fas fa-file-medical"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Medical Registration
                                    </div>
                                </div>
                            </div>
                        </h3>
                        <fieldset>
                            <div id="uploadMedical" class="dropzone">
                                <div class="dz-message">Drop image here or click to Upload</div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>