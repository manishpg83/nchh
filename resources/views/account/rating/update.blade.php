<div class="modal-dialog modal-md modal-dialog-right-bottom" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="ratingForm" action="{{route('account.rating.update',$rating->id)}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="rating" id="rating">
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-2">
                        <label for="rating">Give Your Rating*</label>
                        <div class="edit-rating"></div>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <label for="review">Review</label>
                        <textarea class="form-control p-0" name="review" id="review">{{$rating->review}}</textarea>
                        <span class="text-danger">
                            <strong id="review-error"></strong>
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