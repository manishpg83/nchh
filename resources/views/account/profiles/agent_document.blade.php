<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="agentProfileForm" action="{{route('account.agent.profile.document.verification.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-2">
                        <label for="name">Identity Proof*</label>
                        <input type="file" name="identity_proof" class="form-control" id="identity_proof" placeholder="Select Identity proof">
                        <span class="text-danger">
                            <strong id="identity_proof-error"></strong>
                        </span>
                        <div id="imagePreview">
                            <img src="{{asset('../storage/app/document/no_image.png')}}"
                                class="imagePreview thumbnail w-50 pt-2" id="preview" />
                        </div>
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