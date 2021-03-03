<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="fileForm" action="{{route('account.appointment.files.store',$appointment_id)}}" method="post"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="form-group col-md-12 col-12">
                        <div id="files" class="dropzone">
                            <div class="dz-message">Drop image here or click to Upload</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-submit" onclick="submitFiles()"><i id="loader"
                        class=""></i>Submit</button>
                <button type="button" class="btn btn-secondary close-button" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>