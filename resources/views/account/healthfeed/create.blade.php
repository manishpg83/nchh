<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="healthfeedForm" action="{{route('account.healthfeed.store')}}" method="post"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-6 mb-2">
                        <label>Health Feed Category</label>
                        <select class="form-control" name="category_ids" data-live-search="true"
                            data-style="bg-white rounded-pill px-4 py-3 shadow-sm " onchange="showOtherCategory()">
                            <option value="">Select Category</option>
                            @foreach ($healthfeed_category as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <div class="d-none showOtherCategoryDiv">
                            <label>Other Category</label>
                            <input type="text" class="form-control" name="other_category">
                        </div>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="title">Health Feed Title</label>
                        <input type="text" name="title" class="form-control" id="title"
                            placeholder="Enter Health Feed Title">
                        <span class="text-danger">
                            <strong id="title-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="video_url">Video URL</label>
                        <input type="text" name="video_url" class="form-control" id="video_url"
                            placeholder="Enter video url">
                        <span class="text-danger">
                            <strong id="video_url-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="cover_photo">Cover Photo </label>
                        <input type="file" name="cover_photo" class="form-control" id="cover_photo"
                            placeholder="upload profile picture">
                        <span class="text-danger">
                            <strong id="cover_photo-error"></strong>
                        </span>
                        <div id="imagePreview">
                            <img src="{{asset('../storage/app/healthfeed/default.png')}}"
                                class="imagePreview thumbnail w-50 pt-2" id="preview" />
                        </div>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <label for="content">Content</label>
                        <textarea class="form-control summernote" name="content" id="content"
                            placeholder="Write related and effective course content in required details."></textarea>
                        <span class="text-danger">
                            <strong id="content-error"></strong>
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