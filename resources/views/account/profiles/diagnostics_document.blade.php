<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="diagnosticsProfileForm" action="{{route('account.diagnostics.profile.document.verification.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-6 mb-2">
                        <label for="name">Identity Proof*</label>
                        <input type="file" name="identity_proof" class="form-control" id="identity_proof" placeholder="Select Identity proof">
                        <span class="text-danger">
                            <strong id="identity_proof-error"></strong>
                        </span>
                        <div id="imagePreview">
                            @if(!empty($user->detail->identity_proof) && $user->detail->identity_proof_name != 'no_image.png')
                            <img src="{{$user->detail->identity_proof}}" class="imagePreview thumbnail w-50 pt-2" id="preview" />
                            @else
                            <img src="{{asset('../storage/app/document/no_image.png')}}" class="imagePreview thumbnail w-50 pt-2" id="preview" />
                            @endif
                            
                        </div>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="name">Diagnostics Center Proof*</label>
                        <input type="file" name="diagnostics_proof" class="form-control" id="diagnostics_proof" placeholder="Select Diagnostics proof">
                        <span class="text-danger">
                            <strong id="diagnostics_proof-error"></strong>
                        </span>
                        <div id="imagePreview">
                            @if(!empty($user->detail->diagnostics_proof) && $user->detail->diagnostics_proof_name != 'no_image.png')
                            <img src="{{$user->detail->diagnostics_proof}}" class="imagePreview thumbnail w-50 pt-2" id="diagnostics-preview" />
                            @else
                            <img src="{{asset('../storage/app/document/no_image.png')}}" class="imagePreview thumbnail w-50 pt-2" id="diagnostics-preview" />
                            @endif
                            
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