var receiver_id = '';
$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    $('.user').click(function() {
        $('.user').removeClass('active-user');
        $(this).addClass('active-user');
        $(this).find('.pending').remove();
        receiver_id = $(this).attr('id');
        var url = chatScreen.replace(':slug', receiver_id);
        $.ajax({
            type: "get",
            url: url, // need to create this route
            data: "",
            cache: false,
            success: function(data) {
                $('#messages').html(data);
                scrollToBottomFunc();
            }
        });
    });

    $(document).on('keyup', '.input-text input', function(e) {
        var message = $(this).val();
        var receiver_id = $(this).attr("id");
        // check if enter key is pressed and message is not null also receiver is selected
        if (e.keyCode == 13 && message != '' && receiver_id != '' && typeof storeMessage !== 'undefined') {
            $(this).val(''); // while pressed enter text box will be empty
            var datastr = "to=" + receiver_id + "&message=" + message;
            $.ajax({
                headers: header,
                type: "POST",
                dataType: "json",
                url: storeMessage,
                data: datastr,
                success: function(data) {
                    $('.messages').append('<li class="message clearfix"><div class="sent"><p class="small">' + message + '</p><p class="date small">' + data.text.date + '</p></div></li>');
                    socket.emit('sendChatToServer', data.text);
                },
                error: function(jqXHR, status, err) {},
                complete: function() {
                    scrollToBottomFunc();
                }
            })
        }
    });

    socket.on('serverChatToClient', function(message) {
        if (message.receiver == my_id && message.sender == receiver_id) {
            playNewMessageAudio();
            $('.messages').append('<li class="message clearfix"><div class="received"><p class="small">' + message.message + '</p><p class="date small">' + message.date + '</p></div></li>');
        } else {
            playNewMessageNotificationAudio();
        }
        scrollToBottomFunc();
    });

    socket.on('notifyTyping', function(sender, sender_name, recipient) {
        if (my_id == recipient) {
            $('#notifyTyping').text(sender_name + ' is typing...');
        }
        scrollToBottomFunc();
        setTimeout(function() { $('#notifyTyping').text(''); }, 5000);
    });
});

// make a function to scroll down auto
function scrollToBottomFunc() {
    $('.message-wrapper').animate({
        scrollTop: $('.message-wrapper').get(0).scrollHeight
    }, 50);
}

function playNewMessageAudio() {
    (new Audio('https://notificationsounds.com/soundfiles/8b16ebc056e613024c057be590b542eb/file-sounds-1113-unconvinced.mp3')).play();
}

// Function to play a audio when new message arrives on selected chatbox
function playNewMessageNotificationAudio() {
    (new Audio('https://notificationsounds.com/soundfiles/dd458505749b2941217ddd59394240e8/file-sounds-1111-to-the-point.mp3')).play();
}

function notifyTyping(name) {
    socket.emit('notifyTyping', my_id, name, receiver_id);
}