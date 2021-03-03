var receiver, chatObject, activeUsers, chatWindowID;
$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    syncActiveUser();
    syncMessageCounter();
    syncMessage();
    lazyLoading();
    socket.on("notifyTyping", function(res) {
        let getRecord = JSON.parse(res);
        if (
            typeof receiver !== "undefined" &&
            getRecord.receiver.id == sender.id &&
            getRecord.sender.id == receiver.id
        ) {
            $document
                .find("#notifyTyping")
                .html(
                    '<div class="chat_typing"></div>' +
                    getRecord.sender.name +
                    " is typing..."
                );
            if (!getRecord.is_value) {
                $document.find("#notifyTyping").empty();
            }
        }
    });

    socket.on("syncMessageCounter", function(res) {
        database
            .ref("unreadCounter/user_" + res.receiverId)
            .child(res.senderId)
            .set(firebase.database.ServerValue.increment(1));
    });

    socket.on("receiveMessage", function(res) {
        if (typeof receiver !== "undefined") {
            var message_content = "";
            var name = receiver.name || "";
            var profile =
                receiver.profile_picture || "../public/images/default.png";
            if (res.receiverId == sender.id && res.senderId == receiver.id) {
                var content = res.messageText ?
                    "<p>" + res.messageText + "</p>" :
                    '<div class="fileBox"><img src="' +
                    res.fileUrl +
                    '" width="100"></img></div>';
                message_content = renderMessage(res, "incoming_msg");

                playNewMessageAudio();
                $document.find(".msg_history").append(message_content);
                scrollToBottomFunc();
                lazyLoading();
                imagePopup();
                $document.find("#notifyTyping").empty();
            } else {
                playNewMessageNotificationAudio();
            }
        }

        if (sender.id == res.receiverId && res.messageText) {
            if (
                typeof receiver !== "undefined" &&
                receiver.id == res.senderId
            ) {
                /* No send */
            } else {
                let user = getUserDetail(res.senderId);
                let title = user.name ? "Send by @" + user.name : "";
                /* let decryptText = CryptoJS.AES.decrypt(res.messageText, "%n&c!h*e!a^l@t(h~h)u%b$").toString(CryptoJS.enc.Utf8);
                let messageText = JSON.parse(decryptText).text ? JSON.parse(decryptText).text : ''; */
                let messageText = res.messageText;
                askForNotificationApproval(title, messageText);
            }
        }
    });

    if (typeof receiver !== "undefined") {
        openChat("#user_" + receiver.id, receiver.id);
    }

    $(document).on("keyup", ".write_msg", function(e) {
        if (e.keyCode == 13 && $(this).val() != "" && receiver != "") {
            sendMessage();
            e.preventDefault();
            return false;
        }
        if (e.keyCode !== 8) {
            let is_value = $(this).val() ? 1 : 0;
            let req = {
                sender: sender,
                receiver: receiver,
                is_value: is_value
            };
            socket.emit("notifyTyping", JSON.stringify(req));
        }
        if ($(this).val() == "") {
            let req = { sender: sender, receiver: receiver, is_value: 0 };
            socket.emit("notifyTyping", JSON.stringify(req));
        }
    });
});

/* function openChat(identity, $id) {
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
                chatWindowID = $id
                $('#messages').html(response.result.html);
                $('#right_chat_container').html(response.result.html);
                syncActiveUser();
                syncMessageCounter(receiver.id);
                loadHistory(response.result.chat.id);
                syncMessage(response.result.chat.id);
            }
        });
    }
} */