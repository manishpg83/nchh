@extends('layouts.app')

@section('page_css')
<style type="text/css">
    /*body {
        position: fixed;
        width: 100%;
    }*/
    .user_card{
        border: 0;
    }
    .displayinlineblock{
        display:inline-block;
        width:100%;
        vertical-align:top;
        
    }
    .userprofile{
        width: 90px;
        display: inline-block;
        padding: 0 0 0 0;
        vertical-align: top;
    }
    .chat_container .contacts {
        height: auto !important;
        border-radius: 10px !important;
        padding: 15px;
    }
    .chat_container img {
        max-width: 100%;
        border-radius: 5px !important;
    }
    .uderdetails{width: calc(100% - 100px); display:inline-block; padding-left:15px} 
    
    section.padding.bg-grey{background:#efefef;}
    .media_screen{
        border: 1px solid #c3c3c3;
        border-radius: 10px !important;
        padding-top: 56.25%;
        overflow:hidden;
    }
    .time_card h5 {
        color: #5f45bd !important;
        text-transform: uppercase;
        margin-bottom: 3px;
        padding-top: 15px !important;
    }
    .uderdetails h5.card-title {
        font-size: 20px !important;
        text-transform: uppercase;
        color: #000;
        margin-bottom: 7px !important;
    }
    .uderdetails .text-dark {
        font-size: 14px;
        margin-bottom: 4px;
        color: #656565 !important;
    }
    .card.time_card {
        background: #f0ecff;
        border: 0;
    }
    .media_screen video.localWindow {
        width: auto !important;
        position: absolute;
        height: 100%;
        left: 50%;
        transform: translateX(-50%);
        top: 0;
        object-fit: contain;
    }
</style>
@endsection

@section('content')
<section class="padding bg-grey">
    <div class="container chat_container">
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    @if(!empty($receptorUser))
                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="contacts content-section">
                            <div class="make-me-sticky">

                                <div class="card user_card">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="displayinlineblock">
                                                <div class="userprofile ">
                                                    <img src="{{$receptorUser->profile_picture}}" alt="profile_picture" >
                                                </div>
                                                <div class="uderdetails">
                                                    <h5 class="card-title mb-1">{{$receptorUser->name}}</h5>
                                                    @if(isset($receptorUser->detail->degree))
                                                    <div class="font-13px text-dark w-100">{{$receptorUser->detail->degree}}</div>
                                                    @endif
                                                    @if(isset($receptorUser->detail->specialty_name))
                                                    <div class="font-13px text-dark w-100">{{$receptorUser->detail->specialty_name}}</div>
                                                    @endif
                                                    @if(isset($receptorUser->detail->experience))
                                                    <div class="font-12px text-dark w-100">{{$receptorUser->detail->experience}} {{$receptorUser->detail->experience > 1 ? 'Years of experience overall' : 'Year of experience overall' }}</div>
                                                    @endif
                                                    <div id="avg_rating_box" class="rating_box mt-1" data-rating="{{$receptorUser->average_rating}}"></div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card time_card">
                                                <h5 class="text-info text-center font-weight pt-2">Remaining Time</h5>
                                                <div class="text-center pt-1 pb-2">
                                                    <h4>4:60</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>

                                

                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-12 col-sm-12 video-box " id="video">
                        <div class="media_screen">
                            <video id="localScreen" class="localWindow" autoplay muted height="200px"></video>
                            <video id="RemoteScreen" class="remoteWindow" autoplay height="200px"></video>
                        </div>

                        <div class="controls">
                            <button value="Stop" class="btn btn-outline-secondary btn-sm" onclick="startVideo()" id="startVideo"><i class="ion ion-eye"></i></button>
                            <button value="Stop" class="btn btn-outline-secondary btn-sm" onclick="stopVideo()" id="stopVideo"><i class="ion ion-eye-disabled"></i></button>
                            <button value="mute" class="btn btn-outline-secondary btn-sm" onclick="stopAudio()" id="stopAudio"><i class="ion ion-volume-mute"></i></button>
                            <button value="unmute" class="btn btn-outline-secondary btn-sm" onclick="startAudio()" id="startAudio"><i class="ion ion-volume-high"></i></button>
                            <!-- <button value="Stop" class="btn btn-sm click_advance" onclick="toggleVideo()" id="startVideo">
                                <img src="{{asset('images/controls/videocam-off-outline.svg')}}"></img>
                            </button> -->
                            <button onclick="call()" type="button" class="btn btn-outline-secondary btn-sm" id="btnCall"><i class="ion ion-ios-telephone"></i></button>
                            <button onclick="hangup()" type="button" class="btn btn-outline-danger btn-sm" id="btnHangup" style="display: none;"><i class="ion ion-ios-telephone"></i></button>
                            <button onclick="resetCall()" type="button" class="btn btn-outline-danger btn-sm">RESET</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_script')
<!-- <script src="{{ asset('js/page/chat.js')}}"></script> -->
<script src="{{ asset('js/page/private_video.js')}}"></script>
<script type="text/javascript">
    var sender_id = "{{Auth::id()}}"
    var sender = JSON.parse(`{!! Auth::user()->toJson() !!}`);
    "@if(!empty($receptorUser))"
    var receiver = JSON.parse(`{!! $receptorUser->toJson() !!}`);
    "@endif"
    var storeMessage = "{{route('message.store')}}";
    var chatScreen = "{{route('chat.private.window.open',':slug')}}"
    var callBtn = $('#btnCall');
    var hangupBtn = $('#btnHangup');
    var controlsBtn = $('.controls');

    /* var incoming_call_package = JSON.parse(localStorage.getItem('incoming_call_package'));
    c(incoming_call_package); */
</script>
@endsection