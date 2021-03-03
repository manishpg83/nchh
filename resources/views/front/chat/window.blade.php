<header class="chatbox-panel__header user_{{$receptorUser->id}}">
    <aside style="flex:3">
        <!-- <i class="fa fa-user-circle fa-3x chatbox-popup__avatar" aria-hidden="true"></i> -->
        <div class="media">
            <div class="media-left">
                <img src="{{$receptorUser->profile_picture}}" alt="" class="chat-image">
                <span class="status_icon"></span>
            </div>
        </div>
    </aside>
    <aside style="flex:6">
        <h1 class="chat-title">{{ $receptorUser->name }}</h1> <span class="status small">offline</span>
    </aside>
    <aside style="flex:3;text-align:right;">
        <button class="chatbox-panel-close"><i class="fa fa-times" aria-hidden="true"></i></button>
    </aside>
</header>

<main class="chatbox-panel__main chocolat-parent" style="flex:1">
    <div class="mesgs col-12 pl-0 pr-0">
        <div class="msg_history"></div>
        <div id="notifyTyping"></div>
        <!-- <div class="type_msg">
            <div class="input_msg_write chat_controls">
                <input type="text" class="write_msg" placeholder="Type a message" />
                <form id="controllerForm" action="#" method="POST" enctype="multipart/form-data">
                    <div class="upload-btn-wrapper">
                        <button class="send_image"><i class="ion-image" aria-hidden="true"></i></button>
                        <input type="file" name="image_field" id="image_field" onchange="loadFile(event)" value="" />
                    </div>
                </form>

                <button class="msg_send_btn" type="button" onclick="sendMessage(this);"><i class="ion-ios-paperplane" aria-hidden="true"></i></button>
            </div>
        </div> -->
    </div>
</main>
<footer class="chatbox-panel__footer type_msg">
    <div class="input_msg_write chat_controls">
        <form id="controllerForm" action="#" method="POST" enctype="multipart/form-data">
            <div class="upload-btn-wrapper">
                <!-- <button class="send_image"><i class="ion-image" aria-hidden="true"></i></button> -->
                <input type="file" name="image_field" id="image_field" onchange="loadFile(event)" value="" />
            </div>
        </form>
    </div>
    <aside style="flex:1;color:#888;text-align:center;" onclick="openfileDialog(this);">
        <!-- <i class="fa fa-camera" aria-hidden="true"></i> -->
        <i class="ion-image" aria-hidden="true"></i>
    </aside>
    <aside style="flex:10">
        <textarea type="text" class="write_msg" placeholder="Type your message here..." autofocus></textarea>
    </aside>
    <aside style="flex:1;color:#888;text-align:center;" class="" type="button" onclick="sendMessage(this);">
        <i class="fa fa-paper-plane" aria-hidden="true"></i>
    </aside>
</footer>