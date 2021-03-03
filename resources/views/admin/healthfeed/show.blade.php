<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
        </div>
        <div class="modal-body">
            <div class="card m-0">
                <div class="card-body neucrad_wizard">

                    <h5 class="text-left">{{$healthfeed->title}}</h2>
                        <div class="row mt-4">
                            <div class="col-sm-5">
                                <img class="img-fluid circle" src="{{$healthfeed->cover_photo}}" alt="bg-img">
                            </div>
                            <div class="col-sm-7">
                                <div class="mt-2">
                                    <div class="media">
                                        <a class="media-left media-middle" href="#">
                                            <img class="media-object img-60 rounded-circle mr-3"
                                                src="{{$healthfeed->user->profile_picture}}"
                                                alt="{{$healthfeed->user->name}}">
                                        </a>
                                        <div class="media-body media-middle">
                                            <div class="company-name">
                                                <h6 class="m-0">{{$healthfeed->user->name}}</h6>
                                                <small>{{$healthfeed->health_feed__date}}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($healthfeed->video_url)
                        <hr>
                        <div class="row mt-4">
                            {!! $healthfeed->html_video_url !!}
                        </div>
                        @endif
                        <hr>
                        <div class="row mt-4">
                            {!!$healthfeed->content!!}
                        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>