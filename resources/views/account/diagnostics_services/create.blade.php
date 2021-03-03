<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="diagnosticsServiceForm" action="{{route('account.diagnostics_services.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-2">
                        <label for="name">Name*</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter Service Name">
                        <span class="text-danger">
                            <strong id="name-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <label for="price">Price*</label>
                        <input type="number" name="price" class="form-control" id="price" placeholder="Enter Service price">
                        <span class="text-danger">
                            <strong id="price-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <label for="information">Information</label>
                        <textarea name="information" class="form-control" id="information" placeholder="Enter Service information (Max 40)" maxlength = "40"></textarea>
                        <span class="text-danger">
                            <strong id="information-error"></strong>
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