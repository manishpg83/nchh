<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="healthfeedForm" action="{{route('admin.healthfeed.update',$healthfeed->id)}}" method="post"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="status" value="{{$healthfeed->status}}">

            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-6 mb-2">
                        <label>Health Feed Category</label>
                        <select class="form-control" name="category_ids" data-live-search="true"
                            data-style="bg-white rounded-pill px-4 py-3 shadow-sm " onchange="showOtherCategory()">
                            <option value="">Select Category</option>
                            @foreach ($healthfeed_category as $key => $value)
                            <option value="{{$key}}" @if($key==$healthfeed->
                                category_ids){{'selected'}}@endif>{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <div class="d-none showOtherCategoryDiv">
                            <label>Other Category</label>
                            <input type="text" class="form-control" name="other_category" value="{{ $healthfeed->other_category }}">
                        </div>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="title">Health Feed Title</label>
                        <input type="text" name="title" value="{{$healthfeed->title}}" class="form-control" id="title"
                            placeholder="Enter healthfeed title">
                        <span class="text-danger">
                            <strong id="title-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="video_url">Video URL</label>
                        <input type="text" name="video_url" class="form-control" id="video_url"
                            placeholder="Enter video url" value="{{$healthfeed->video_url}}">
                        <span class="text-danger">
                            <strong id="video_url-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <label for="cover_photo">Image </label>
                        <input type="file" name="cover_photo" class="form-control" id="cover_photo"
                            placeholder="upload profile picture">
                        <span class="text-danger">
                            <strong id="cover_photo-error"></strong>
                        </span>
                        <div id="imagePreview">
                            <img src="{{$healthfeed->cover_photo}}" class="imagePreview thumbnail w-100 pt-2"
                                id="preview" />
                        </div>
                    </div>
                    <div class="col-sm-12 mb-2">
                        <label for="content">Content</label>
                        <textarea class="form-control summernote" name="content"
                            placeholder="Write related and effective course content in required details.">{{$healthfeed->content}}</textarea>
                        <span class="text-danger">
                            <strong id="content-error"></strong>
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