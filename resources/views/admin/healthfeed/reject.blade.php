<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header pt-2 pb-2">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
        </div>
        <form id="healthfeedForm" action="{{ Route('admin.healthfeed.change.status') }}" method="post"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="card-body">
                    <input type="hidden" name="status" value="2">
                    <input type="hidden" name="id" value="{{$healthfeed->id}}">
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="feedback_message">Feedback Message</label>
                            <input type="text" name="feedback_message" class="form-control" value=""
                                id="feedback_message" placeholder="Enter feedback message">
                            <span class="text-danger">
                                <strong id="feedback_message-error"></strong>
                            </span>
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