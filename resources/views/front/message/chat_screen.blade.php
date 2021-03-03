<div class="input-text">
    <p class="name"><img src="{{$receiver_user->profile_picture}}" alt="" class="chat-image">
        {{ $receiver_user->name }}</p>
</div>
<div class="message-wrapper">
    <ul class="messages">
        @foreach($messages as $message)
        <li class="message clearfix">
            <div class="{{ ($message->from == Auth::id()) ? 'sent' : 'received' }}">
                <p class="small">{{ $message->message }}</p>
                <p class="date small">{{ date('d M y, h:i a', strtotime($message->created_at)) }}</p>
            </div>
        </li>
        @endforeach
    </ul>
    <span id="notifyTyping"></span>
</div>
<div class="input-text">
    <input type="text" placeholder="Enter message here...." id="{{$receiver_user->id}}" name="message"
        class="submit input" onkeyup="notifyTyping('{{Auth::user()->name}}');">
</div>