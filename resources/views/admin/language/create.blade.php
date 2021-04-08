<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="languageForm" action="{{route('admin.languages.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-2">
                        <label for="title">Language*</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter Language">
                        <span class="text-danger">
                            <strong id="title-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <label for="title">Language Sort Name*</label>
                        <input type="text" name="short_name" class="form-control" id="short_name" placeholder="Enter Language Short Name">
                        <span class="text-danger">
                            <strong id="title-error"></strong>
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