@extends('layouts.app')

@section('page_css')
<style type="text/css">
    .user_card {
        border: 0;
    }

    .displayinlineblock {
        display: inline-block;
        width: 100%;
        vertical-align: top;

    }

    .userprofile {
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

    .uderdetails {
        width: calc(100% - 100px);
        display: inline-block;
        padding-left: 15px
    }

    section.padding.bg-grey {
        background: #efefef;
    }

    .media_screen {
        border: 1px solid #c3c3c3;
        border-radius: 10px !important;
        padding-top: 50.25%;
        overflow: hidden;
        background:#fff;
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
        padding-top:5px;
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
        width: auto;
        /* width: auto !important; */
        position: absolute;
        height: 100%;
        left: 50% !important;
        transform: translateX(-50%) !important;
        top: 0 !important;
        object-fit: contain;
    }
    @media(min-width:991px){
        .shorting {
            max-width: 70% !important;
        }
    }
    
    .navbar.main-navbar{
        position:relative !important;
    }
    section.padding.bg-grey {
        padding: 15px 0 70px;
    }
    .media_screen .remoteWindow.big {
        display: block;
        position: absolute;
        top: 0 !important;
        left: 0 !important;
        height: 100% !important;
    }
    .media_screen video.localWindow.thumb {
        width: auto !important;
        position: absolute;
        height: 90px !important;
        object-fit: contain;
        bottom: 0 !important;
        right: 0 !important;
        top:auto  !important;
        left:auto !important;
        transform:translate(0 , 0) !important;
    }
    .media_screen .remoteWindow.big.thumb {
        width: auto !important;
        position: absolute;
        height: 90px !important;
        object-fit: contain;
        top:auto  !important;
        bottom: 0;
        right: 0;
        
        left:auto !important;
        transform:translate(0 , 0) !important;
    }
    .samebtm {
        background: #5f45bd;
        border: 0;
        border-radius: 5px !important;
        color: #fff !important;
        padding: 4px 9px !important;
    }
    .samebtm:disabled {
        background: gray;
        border: 0;
        border-radius: 5px !important;
        color: #fff !important;
        padding: 4px 9px !important;
        cursor: not-allowed;
    }
    button.btn.btn-outline-danger.btn-sm.redbutton {
        background: #ed3a3a;
        color: #ffff;
        border-radius: 5px;
        padding: 3px 15px;
    }
    .btn2 {
        background: #0f4782;
    }
    .btn3 {
        background: #007082;
    }
    .btn4 {
        background: #e79909;
    }
    .btn5 {
        background: #28a745;
    }
    .btn6{
        background:#ed3a3a;
    }

    .video-box .controls {
        position: absolute;
        bottom: 10px;
        margin: 0 auto;
        background: #fff;
        padding: 6px !important;
        border-radius: 10px;
        left: 50%;
        transform: translateX(-50%);
    }
</style>
@endsection

@section('content')
<section class="padding bg-grey">
    <div class="container">
        <div class="row">
    
            @if(!empty($receptorUser))
            <div class="col-md-12 col-sm-12 mb-3 width-down">
                <div class="contacts content-section">
                    <div class="make-me-sticky">

                        <div class="card user_card">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="displayinlineblock">
                                        <div class="userprofile ">
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
                                    <div class="card time_card mt-1">
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

            <div class="col-md-12 col-sm-12 video-box width-down" id="video">
                <div class="media_screen">
                    <video id="localScreen" class="localWindow" autoplay muted height="200px"></video>
                    <video id="RemoteScreen" class="remoteWindow" autoplay height="200px"></video>
                </div>

                <div class="controls">
                    <button value="Stop" class="btn btn-outline-secondary btn-sm samebtm btn1" onclick="startVideo()" id="startVideo"><i class="ion ion-eye"></i></button>
                    <button value="Stop" class="btn btn-outline-secondary btn-sm samebtm btn2" onclick="stopVideo()" id="stopVideo"><i class="ion ion-eye-disabled"></i></button>
                    <button value="mute" class="btn btn-outline-secondary btn-sm samebtm btn3" onclick="stopAudio()" id="stopAudio"><i class="ion ion-volume-mute"></i></button>
                    <button value="unmute" class="btn btn-outline-secondary btn-sm samebtm btn4" onclick="startAudio()" id="startAudio"><i class="ion ion-volume-high"></i></button>
                    <button onclick="call()" type="button" class="btn btn-outline-secondary btn-sm samebtm btn5" id="btnCall"><i class="ion ion-ios-telephone"></i> Call</button>
                    <button onclick="hangup()" type="button" class="btn btn-outline-danger btn-sm samebtm btn6" id="btnHangup" style="display: none;"><i class="ion ion-ios-telephone"></i> Hang Up</button>
                </div>
                
            </div>

        </div>
    </div>
</section>

<!--Chat popup start-->
<button class="chatbox-open chat-button">
    <i class="fa fa-comment fa-2x" aria-hidden="true"></i>
</button>
<button class="chatbox-close chat-button">
    <i class="fa fa-times fa-2x" aria-hidden="true"></i>
</button>

<section class="chatbox-panel chat_container" id="right_chat_container">
</section>

<!--Chat popup end-->
@endsection
@section('page_script')
<script src="{{ asset('js/page/video_with_chat.js')}}"></script>
<script src="{{ asset('js/page/private_video.js')}}"></script>
<!-- <script src="{{ asset('js/page/video.js')}}"></script> -->
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
    var startVideoBtn = $('#startVideo');
    var stopVideoBtn = $('#stopVideo');
    var stopAudioBtn = $('#stopAudio');
    var startAudioBtn = $('#startAudio');

    
    /* var callBtn = $('.callBtn');
    var hangupBtn = $('.hangupBtn');
    var controlsBtn = $('.controls'); */

    var socket = io.connect("{{config('services.chat.domain','http://localhost:3000')}}", {
        query: sender
    });

    $(".rating_box").starRating({
        starSize: 20,
        useFullStars: true,
        readOnly: true
    });

    /* JS comes here */
    // askForApproval();

    //countdown counter
    var call_disconnect = `{!!$call_disconnect!!}`;
    var totalSecond = "{{$totalSecond}}";
    var totalMinute = "{{$totalMinute}}" * 60;
    var time = parseInt(totalSecond) + parseInt(totalMinute);
    //var time = 20;
    var countdown = document.getElementById('countdown');
    var minutes, seconds;
    var chatHome = "{{route('chat.index')}}";
    setInterval(function() {
        if (time == 15) {
            console.log(time);
            $("#countdown").addClass('text-danger blink_me');
        }
        if (time <= 0) {
            countdown.innerHTML = 'Call End';
            setTimeout(function() {
                $(".type_msg").hide();
                $("#video").html(call_disconnect);
                hangup();
            }, 2000);
            setTimeout(function() {
                window.location.href = chatHome;
            }, 15000);
        } else {
            minutes = parseInt(time / 60);
            seconds = parseInt(time % 60);
            countdown.innerHTML = '' + minutes + ' : ' + seconds + '';
        }
        time -= 1;

    }, 1000);
</script>
<script type="text/javascript">
    $(".chatbox-open").click(() => {
        $(".chatbox-open").fadeOut();
        $(".chatbox-panel").fadeIn();
        $('.msg_history').animate({ scrollTop: $('.msg_history')[0].scrollHeight }, 1);
        $(".chatbox-panel").css({
            display: "flex"
        });

        $(".width-down").addClass("shorting");
    });

    $(document).on('click', '.chatbox-panel-close', function() {
        $(".chatbox-panel").fadeOut();
        $(".chatbox-open").fadeIn();
        $(".width-down").removeClass("shorting");
    })

    $(".btn2").hide();
    $(".btn4").hide();

    $(".btn1").click(function(){
        $(this).hide()
        $(".btn2").show();
    });
    $(".btn2").click(function(){
        $(this).hide()
        $(".btn1").show();
    });

    $(".btn3").click(function(){
        $(this).hide()
        $(".btn4").show();
    });
    $(".btn4").click(function(){
        $(this).hide()
        $(".btn3").show();
    });
    
</script>
@endsection