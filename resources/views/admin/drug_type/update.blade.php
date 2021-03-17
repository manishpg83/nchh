<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="typeForm" action="{{route('admin.drug-types.update',$type->id)}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-2">
                        <label for="title">Type*</label>
                        <input type="text" name="name" value="{{$type->name}}" class="form-control" id="name" placeholder="Enter Drug Type">
                        <span class="text-danger">
                            <strong id="title-error"></strong>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-submit"><i id="loader" class=""></i>Update</button>
                <button type="button" class="btn btn-secondary close-button" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>