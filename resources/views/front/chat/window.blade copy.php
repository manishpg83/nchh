<div class="msg_head userbox user_{{$receptorUser->id}} m-0">
    <div class="media">
        <div class="media-left">
            <img id="previewImage" src="">
            <img src="{{$receptorUser->profile_picture}}" alt="" class="chat-image">
            <span class="status_icon"></span>
        </div>
        <div class="media-right">{{ $receptorUser->name }}
            <div class="status">offline</div>
        </div>
    </div>
</div>

<div class="mesgs col-12 pl-0 pr-0">
    <div class="msg_history"></div>
    <div id="notifyTyping"></div>
    <div class="type_msg">
        <div class="input_msg_write chat_controls">
            <input type="text" class="write_msg" placeholder="Type a message" />
            <!-- onkeyup="notifyTyping(this);" -->
            <form id="controllerForm" action="#" method="POST" enctype="multipart/form-data">
                <div class="upload-btn-wrapper">
                    <button class="send_image"><i class="ion-image" aria-hidden="true"></i></button>
                    <input type="file" name="image_field" id="image_field" onchange="loadFile(event)" value="" />
                    <!-- onclick="handleClick(event)" -->
                </div>
            </form>

            <button class="msg_send_btn" type="button" onclick="sendMessage(this);"><i class="ion-ios-paperplane" aria-hidden="true"></i></button>
        </div>
    </div>
</div>