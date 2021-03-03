<div id="video_consutant_modal" class="modal-dialog-full-width modal-dialog modal-fluid" role="document">
    <div class="modal-content modal-content-full-width">
        <!---<div class="modal-header">
            <h5 class="modal-title" id="modellabel">Call</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>--->
        <div class="modal-body pb-0">
            <div class="row">
                <div class="col-md-8">
                    <div class="">
                        <div class="row p-2">

                            @if(!empty($receptorUser))
                            <div class="col-md-12 col-sm-12 mb-3 width-down">
                                <div class="contacts content-section">
                                    <div class="make-me-sticky">

                                        <div class="card user_card">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="displayinlineblock">
                                                        <div class="userprofile">
                                                            <img src="{{$receptorUser->profile_picture}}" alt="profile_picture" style="max-width: 100%; border-radius: 5px !important;">
                                                        </div>
                                                        <div class="uderdetails">
                                                            <h5 class="card-title mb-1">{{$receptorUser->name}}</h5>
                                                            @if(isset($receptorUser->detail->degree))
                                                            <div class="font-13px text-dark w-100">{{$receptorUser->detail->degree}}
                                                                @if(isset($receptorUser->detail->specialty_name))
                                                                - <span style="color:blue;">{{$receptorUser->detail->specialty_name}}</span>
                                                                @endif
                                                            </div>
                                                            @endif
                                                            @if(isset($receptorUser->detail->experience))
                                                            <div class="font-12px text-dark w-100">{{$receptorUser->detail->experience}} {{$receptorUser->detail->experience > 1 ? 'Years of experience overall' : 'Year of experience overall' }}</div>
                                                            @endif
                                                            <!--<div id="avg_rating_box" class="rating_box mt-1" data-rating="{{$receptorUser->average_rating}}"></div>-->
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card time_card mt-1 mr-md-1">
                                                        <h5 class="text-info text-center font-weight pt-2">Remaining Time</h5>
                                                        <div class="text-center pt-1">
                                                            <h4 id="countdown"></h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="col-sm-12 video-box" id="video">
                                <div class="media_screen ">
                                    <video id="localScreen" class="localWindow" autoplay muted height="400"></video>
                                    <video id="RemoteScreen" class="remoteWindow" autoplay height="400"></video>
                                </div>
                                <div class="controls mt-2">
                                    <button value="Stop" class="btn btn-outline-secondary btn-sm samebtm btn1" onclick="startVideo()" id="startVideo"><i class="ion ion-eye"></i></button>
                                    <button value="Stop" class="btn btn-outline-secondary btn-sm samebtm btn2" onclick="stopVideo()" id="stopVideo"><i class="ion ion-eye-disabled"></i></button>
                                    <button value="mute" class="btn btn-outline-secondary btn-sm samebtm btn3" onclick="stopAudio()" id="stopAudio"><i class="ion ion-volume-mute"></i></button>
                                    <button value="unmute" class="btn btn-outline-secondary btn-sm samebtm btn4" onclick="startAudio()" id="startAudio"><i class="ion ion-volume-high"></i></button>
                                    <!-- <button onclick="showFriendsFace()" type="button" class="btn btn-outline-secondary btn-sm" id="btnCall"><i class="ion ion-ios-telephone"></i></button> -->
                                    <button onclick="callHangup()" type="button" class="btn btn-outline-danger btn-sm samebtm btn6" id="btnHangup"><i class="ion ion-ios-telephone"></i> Hang Up</button>
                                </div>
                                
                            </div>

                            
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <section class="chatbox-panel chat_container" id="right_chat_container" style="display: block;">
                    </section>
                </div>
            </div>

        </div>
        <!--  <div class="modal-footer-full-width modal-footer">
            <button type="button" class="btn btn-danger btn-md btn-rounded" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-md btn-rounded">Save changes</button>
        </div> -->
    </div>
</div>
</div>
