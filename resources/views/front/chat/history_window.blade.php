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
</div>