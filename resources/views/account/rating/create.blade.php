<div class="modal-dialog modal-md modal-dialog-right-bottom" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="ratingForm" action="{{route('account.rating.store')}}" method="post" enctype="multipart/form-data">
            @csrf
             <input type="hidden" name="rating" id="rating">
             <input type="hidden" name="rateable_id" value="{{$rateable_id}}">
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-2">
                        <label for="rating">Give Your Rating*</label>
                        <div class="add-rating"></div>
                        <span id="rating-error" class="error" for="rating"></span>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <label for="review">Review</label>
                        <textarea class="form-control p-0" name="review" id="review" placeholder="Write your review"></textarea>
                        <span class="text-danger">
                            <strong id="review-error"></strong>
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