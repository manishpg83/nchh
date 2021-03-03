var receiver, chatObject;
var receiver_id = '';
$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    socket.on('notifyTyping', function(res) {
        let getRecord = JSON.parse(res);
        if (sender_id == getRecord.receiver.id) {
            $document.find('#notifyTyping').html('<div class="chat_typing"></div>' + getRecord.sender.name + ' is typing...')
        }
        setTimeout(function() {
            $document.find('#notifyTyping').empty()
        }, 5000);
    });

    socket.on('receiveMessage', function(res) {

        var message_content = '';
        var name = receiver.name || "";
        var profile = receiver.profile_picture || "../public/images/default.png";
        if (res.receiverId == sender.id && res.senderId == receiver.id) {
            var content = res.messageText ? '<p>' + res.messageText + '</p>' : '<div class="fileBox"><img src="' + res.imageUrl + '" width="100"></img></div>';
            message_content += '<div class="incoming_msg">'
            message_content += '<div class="incoming_msg_img"> <img src="' + profile + '" alt="' + name + '" class="rounded-circle"> </div>'
            message_content += '<div class="received_msg">'
            message_content += '<div class="received_withd_msg">'
                /* if (res.imageUrl) {
                    message_content += '<img src="' + res.imageUrl + '"></img>'
                } else {
                    message_content += '<p>' + res.messageText + '</p>'
                } */

            message_content += content
            message_content += '<span class="time_date"> ' + moment(res.timestamp).format("h:mm A | MMM d ") + '</span>'
            message_content += '</div>'
            message_content += '</div>'
            message_content += '</div>'
            message_content += '</div>'

            playNewMessageAudio();
            $document.find('.msg_history').append(message_content);
            scrollToBottomFunc();
            console.log('message get');
            $document.find('#notifyTyping').empty()
        } else {
            playNewMessageNotificationAudio();
        }
    })

    if (typeof receiver !== "undefined") {
        openChat('#user_' + receiver.id, receiver.id);
    }

    $(document).on('keyup', '.write_msg', function(e) {
        if (e.keyCode == 13 && $(this).val() != '' && receiver != '') {
            sendMessage();
            e.preventDefault();
            return false;
        }
        if (e.keyCode !== 8) {
            let req = { sender: sender, receiver: receiver }
            socket.emit('notifyTyping', JSON.stringify(req));
        }

    })

});

function openChat(identity, $id) {
    $document.find('.userbox').removeClass('active-user');
    $(identity).addClass('active-user');
    $(identity).find('.pending').remove();
    if (typeof chatScreen !== "undefined") {
        var url = chatScreen.replace(':slug', $id);
        $.ajax({
            type: "GET",
            url: url,
            // cache: false,
            success: function(response) {
                receiver = response.result.receptorUser
                chatObject = response.result.chat
                $('#messages').html(response.result.html);
                loadHistory(response.result.chat.id);
            }
        });
    }
}

function loadHistory($id) {
    $.get(`${chatServerDomain}/chat/${$id}`, function(res) {
        let message_content = '';
        var profile = receiver.profile_picture || "../public/images/default.png";
        console.log(res.data);

        if (res.data) {
            $.each(res.data, function(key, row) {

                var content = row.messageText ? '<p>' + row.messageText + '</p><span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span>' : '<div class="image_container"><div class="fileBox"><img src="' + row.imageUrl + '" width="100"></img></div><span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span></div>';

                if (row.messageText || row.imageUrl) {

                    if (sender_id == row.senderId) {
                        message_content += '<div class="outgoing_msg">'
                        message_content += '<div class="sent_msg">'
                        message_content += content
                            /* message_content += '<p>' + row.messageText + '</p>'
                            message_content += '<span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span>'*/
                        message_content += '</div>'
                        message_content += '</div>'
                        message_content += '</div>'
                    } else {
                        message_content += '<div class="incoming_msg">'
                        message_content += '<div class="incoming_msg_img"> <img src="' + profile + '" alt="sunil" class="rounded-circle"> </div>'
                        message_content += '<div class="received_msg">'
                        message_content += '<div class="received_withd_msg">'
                        message_content += content
                            /* message_content += '<p>' + row.messageText + '</p>'
                            message_content += '<span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span>'*/
                        message_content += '</div>'
                        message_content += '</div>'
                        message_content += '</div>'
                        message_content += '</div>'
                    }
                }

            });
        }

        /* for (let index = 0; index < messages.length; index++) {
            if (sender_id == messages[index].senderId) {
                message_content += '<div class="outgoing_msg">'
                message_content += '<div class="sent_msg">'
                message_content += '<p>' + messages[index].messageText + '</p>'
                message_content += '<span class="time_date"> ' + moment(messages[index].timestamp).format("h:mm A | MMM d ") + '</span>'
                message_content += '</div>'
                message_content += '</div>'
                message_content += '</div>'
            } else {
                message_content += '<div class="incoming_msg">'
                message_content += '<div class="incoming_msg_img"> <img src="' + profile + '" alt="sunil" class="rounded-circle"> </div>'
                message_content += '<div class="received_msg">'
                message_content += '<div class="received_withd_msg">'
                message_content += '<p>' + messages[index].messageText + '</p>'
                message_content += '<span class="time_date"> ' + moment(messages[index].timestamp).format("h:mm A | MMM d ") + '</span>'
                message_content += '</div>'
                message_content += '</div>'
                message_content += '</div>'
                message_content += '</div>'
            }
        } */

        /* for (let index = 0; index < messages.length; index++) {
            var senderReceiverClass = (sender_id == messages[index].userId) ? 'sent' : 'received'
            message_content += '<li class="message clearfix">'
            message_content += '<div class="' + senderReceiverClass + '">'
            message_content += '<p>' + messages[index].text + '</p>'
            message_content += '<p>' + moment(messages[index].date).format("DD-MM-YY h:mma") + '</p>'
            message_content += '</div>'
            message_content += '</li>'
        } */
        $document.find('.msg_history').html(message_content);
        scrollToBottomFunc();
    });
}

function sendMessage(identity = '') {
    let message_content = '';
    let text = $document.find('.write_msg').val();
    if (text) {
        let message = {
            senderId: sender.id,
            receiverId: receiver.id,
            messageText: text,
            timestamp: moment().format()
        }
        let data = {
            chatId: chatObject.id,
            message: message
        }

        message_content += '<div class="outgoing_msg">'
        message_content += '<div class="sent_msg">'
        message_content += '<p>' + text + '</p>'
        message_content += '<span class="time_date"> ' + moment(message.timestamp).format("h:mm A | MMM d ") + '</span>'
        message_content += '</div>'
        message_content += '</div>'
        message_content += '</div>'
        $document.find('.msg_history').append(message_content);
        scrollToBottomFunc();
        $document.find('.write_msg').val('');
        socket.emit('sendMessage', JSON.stringify(data));
    }
}

function loadFile(event) {
    event.preventDefault();
    var file = event.target.files[0];

    // Clear the selection in the file picker input.
    $document.find('#controllerForm')[0].reset();

    // Check if the file is an image.
    if (!file.type.match('image.*')) {
        toastrAlert('error', '', 'You can only share images.');
        return;
    }

    formData = new FormData();
    formData.append('senderId', sender.id);
    formData.append('receiverId', receiver.id);
    formData.append('chatId', chatObject.id);
    formData.append('timestamp', moment().format());
    formData.append('image', file);

    $.ajax({
        url: `${chatServerDomain}/uploadFile`,
        method: 'post',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new XMLHttpRequest();

            // Add progress event listener to the upload.
            xhr.upload.addEventListener('progress', function(event) {
                var progressBar = $('.progress-bar');

                if (event.lengthComputable) {
                    var percent = (event.loaded / event.total) * 100;
                    progressBar.width(percent + '%');

                    if (percent === 100) {
                        progressBar.removeClass('active');
                    }
                }
            });

            return xhr;
        }
    }).done(function(res) {

        formData.forEach(function(val, key, fD) {
            formData.delete(key)
        });
        /* On image send append image box as sender */
        message_content = '';
        if (sender_id == res.data.senderId) {
            message_content += '<div class="outgoing_msg">'
            message_content += '<div class="sent_msg">'
            message_content += '<div class="image_container"><div class="fileBox"><img src="' + res.data.imageUrl + '" width="100"></img></div><span class="time_date"> ' + moment(res.data.timestamp).format("h:mm A | MMM d ") + '</span></div>'
            message_content += '</div>'
            message_content += '</div>'
            message_content += '</div>'
            $document.find('.msg_history').append(message_content);
            scrollToBottomFunc();
        }
    }).fail(function(xhr, status) {});
};

/* function notifyTyping(identity) {
    console.log($(identity).keycode);
    let req = { sender: sender, receiver: receiver }
    socket.emit('notifyTyping', JSON.stringify(req));
} */

// make a function to scroll down auto
function scrollToBottomFunc() {
    $('.msg_history').animate({
        scrollTop: $('.msg_history').get(0).scrollHeight
    }, 'slow');
}

function playNewMessageAudio() {
    console.log('new message')
    var playNewMessagePromise = (new Audio('https://notificationsounds.com/soundfiles/8b16ebc056e613024c057be590b542eb/file-sounds-1113-unconvinced.mp3')).play();

    if (playNewMessagePromise !== undefined) {
        playNewMessagePromise.then(_ => {
            // Automatic playback started!
            // Show playing UI.
        }).catch(error => {
            // Auto-play was prevented
            // Show paused UI.
        });
    }
}

// Function to play a audio when new message arrives on selected chatbox
function playNewMessageNotificationAudio() {
    console.log('new message notification')
    var playPromise = (new Audio('https://notificationsounds.com/soundfiles/dd458505749b2941217ddd59394240e8/file-sounds-1111-to-the-point.mp3')).play();

    if (playPromise !== undefined) {
        playPromise.then(_ => {
            // Automatic playback started!
            // Show playing UI.
        }).catch(error => {
            // Auto-play was prevented
            // Show paused UI.
        });
    }
}

function handleClick(event) {
    const { target = {} } = event || {};
    target.value = "";
};