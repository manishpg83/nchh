@extends('layouts.app')

@section('page_css')
<!-- <style type="text/css">
    body {
        position: fixed;
        width: 100%;
    }
</style> -->
@endsection

@section('content')
<section class="padding bg-grey">
    <div class="container chat_container">
        <div class="row">

            @if(!empty($users))
            <div class="col-md-3 col-sm-12">
                <div class="contacts">
                    <div class="make-me-sticky p-0">
                        <ul class="list-group">
                            @foreach($users as $user)
                            <li class="userbox user_{{$user->id}} list-group-item d-flex align-items-center p-2" onclick="openChat(this,'{{$user->id}}')">
                                <div class="media">
                                    <div class="media-left">
                                        <img src="{{$user->profile_picture}}" alt="" class="chat-image">
                                        <span class="status_icon"></span>
                                    </div>
                                </div>
                                <span class="name ml-1">{{ $user->name }}</span>
                                <!-- <img src="{{$user->profile_picture}}" alt="" class="chat-image"> -->
                                <span class="badge badge-secondary badge-pill unread_count">0</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-sm-12 chat-box @if(!empty($users)) col-md-9 @else col-md-12 @endif" id="messages">
                <div class="m-auto text-center p-5 border">
                    <img src="{{asset('images/live-chat-logo.png')}}" class="" width="100">
                    @if(checkPermission(['doctor']))
                    <h5 class="mt-3">Start Chat</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_script')
<script src="{{ asset('js/page/chat.js')}}"></script>
<script type="text/javascript">
    var sender_id = "{{Auth::id()}}"
    var sender = JSON.parse('{!! Auth::user()->toJson() !!}');
    '@if(!empty($receptorUser))'
    var receiver = JSON.parse('{!! $receptorUser->toJson() !!}');
    '@endif'
    var storeMessage = "{{route('message.store')}}";
    var chatScreen = "{{route('chat.window.open',':slug')}}"
    var socket = io.connect("{{config('services.chat.domain','http://localhost:3000')}}", {
        query: sender
    });
</script>

<!-- <script src="https://www.gstatic.com/firebasejs/7.22.1/firebase-app.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/7.22.1/firebase-storage.min.js" integrity="sha512-GuMCyR1LXS+xovLmB5P/JzsOPo542879WQEYSkudnyeCX1LnG2ZpMFGG4IpMaJPCoDawmOSMo2/+30TZsjGE/Q==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/firebase/7.22.1/firebase-database.min.js" integrity="sha512-9pcV+9tJDsn39SvC/t4Jd8N3k8bt58aYbkL6izei0DKZdR3RPp5HSHC0Up7tSpTIftBQb32zhFCYI4gb3WLvmQ==" crossorigin="anonymous"></script>
<script src="https://www.gstatic.com/firebasejs/7.22.1/firebase-analytics.js"></script> -->
<script>
    /* var firebaseConfig = {
        apiKey: "AIzaSyBoyanen_bS8Y2izhDq8R2KrpBJ9enfMQo",
        authDomain: "neucrad-b797d.firebaseapp.com",
        databaseURL: "https://neucrad-b797d.firebaseio.com",
        projectId: "neucrad-b797d",
        storageBucket: "neucrad-b797d.appspot.com",
        messagingSenderId: "734640782362",
        appId: "1:734640782362:web:5d2919da1fb402cfc425a3",
        measurementId: "G-C33943CNQ9"
    };
    firebase.initializeApp(firebaseConfig);
    // firebase.analytics();
    var database = firebase.database();
    var storage = firebase.storage();
    var starCountRef = firebase.database().ref('chats/25'); */

    /* starCountRef.on('value', function(snapshot) {
        var message_content = "";
        var profile = receiver.profile_picture || "../public/images/default.png";
        if (snapshot.val()) {
            $.each(snapshot.val(), function(key, row) {
                console.log('row');
                console.log(row);
                var content = row.messageText ? '<p>' + row.messageText + '</p><span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span>' : '<div class="image_container"><div class="fileBox"><img src="' + row.imageUrl + '" width="100"></img></div><span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span></div>';

                if (row.messageText || row.imageUrl) {

                    if (sender_id == row.senderId) {
                        message_content += '<div class="outgoing_msg">'
                        message_content += '<div class="sent_msg">'
                        message_content += content
                        message_content += '</div>'
                        message_content += '</div>'
                        message_content += '</div>'
                    } else {
                        message_content += '<div class="incoming_msg">'
                        message_content += '<div class="incoming_msg_img"> <img src="' + profile + '" alt="sunil" class="rounded-circle"> </div>'
                        message_content += '<div class="received_msg">'
                        message_content += '<div class="received_withd_msg">'
                        message_content += content
                        message_content += '</div>'
                        message_content += '</div>'
                        message_content += '</div>'
                        message_content += '</div>'
                    }
                }

            });
            $document.find('.msg_history').html(message_content);
            scrollToBottomFunc();
        }
    }); */
</script>
@endsection