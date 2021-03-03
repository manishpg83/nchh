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
    /* socket.on('activeUsers', function(res) {
        if ($('.userbox').hasClass('online')) {
            $('.userbox').removeClass('online').addClass('offline');
            $('.userbox .status').html('offline');
        }
        activeUsers = res
        $.each(res, function(key, row) {
            $document.find('.user_' + row.id).addClass('online')
            $document.find('.user_' + row.id + ' ' + '.status').html('online');
        })
    }); */

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

        /* setTimeout(function() {
            $document.find('#notifyTyping').empty()
        }, 5000); */
    });

    socket.on("syncMessageCounter", function(res) {
        // console.log('check open chat receiver ' + receiver.id);
        /* console.log('check response sender ' + res.senderId);
        console.log('check response receiver ' + res.receiverId); */

        database
            .ref("unreadCounter/user_" + res.receiverId)
            .child(res.senderId)
            .set(firebase.database.ServerValue.increment(1));
        /* if (res.receiverId == sender.id) {

            console.log('Only access the responsible receiver');

            if (typeof receiver == "undefined") {
                console.log('Not Open any window');
                database
                    .ref('unreadCounter/user_' + res.receiverId)
                    .child(res.senderId)
                    .set(firebase.database.ServerValue.increment(1))
            }

            if (typeof receiver !== "undefined" && receiver.id != res.senderId && res.senderId != sender.id) {
                console.log('open only targer user');
                database
                    .ref('unreadCounter/user_' + res.receiverId)
                    .child(res.senderId)
                    .set(firebase.database.ServerValue.increment(1))
            }
        } */
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

                /* message_content += '<div class="incoming_msg">'
                message_content += '<div class="incoming_msg_img"> <img src="' + profile + '" alt="' + name + '" class="rounded-circle"> </div>'
                message_content += '<div class="received_msg">'
                message_content += '<div class="received_withd_msg">'
                message_content += content
                message_content += '<span class="time_date"> ' + moment(res.timestamp).format("h:mm A | MMM d ") + '</span>'
                message_content += '</div>'
                message_content += '</div>'
                message_content += '</div>'
                message_content += '</div>' */

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

function openChat(identity, $id) {
    $document.find(".userbox").removeClass("active-user");
    $(identity).addClass("active-user");
    $(identity)
        .find(".pending")
        .remove();
    if (typeof chatScreen !== "undefined") {
        var url = chatScreen.replace(":slug", $id);
        $.ajax({
            type: "GET",
            url: url,
            // cache: false,
            success: function(response) {
                receiver = response.result.receptorUser;
                chatObject = response.result.chat;
                chatWindowID = $id;
                $("#messages").html(response.result.html);
                syncActiveUser();
                syncMessageCounter(receiver.id);
                /* Keep user is online */
                /* if (activeUsers) {
                    $.map(activeUsers, function(item, i) {
                        if (item.id == receiver.id) {
                            $document.find('.user_' + receiver.id).addClass('online')
                            $document.find('.user_' + receiver.id + ' ' + '.status').html('online');
                        }
                    })
                } */
                /* load user history */
                loadHistory(response.result.chat.id);
                syncMessage(response.result.chat.id);
            }
        });
    }
}

function loadHistory($id) {
    $.get(`${chatServerDomain}/chat/${$id}`, function(res) {
        let message_content = "";
        var profile =
            receiver.profile_picture || "../public/images/default.png";
        if (res.data) {
            $.each(res.data, function(key, row) {
                var content = row.messageText ?
                    "<p>" +
                    row.messageText +
                    '</p><span class="time_date"> ' +
                    moment(row.timestamp).format("h:mm A | MMM d ") +
                    "</span>" :
                    '<div class="image_container"><div class="fileBox"><img src="' +
                    row.fileUrl +
                    '" width="100"></img></div><span class="time_date"> ' +
                    moment(row.timestamp).format("h:mm A | MMM d ") +
                    "</span></div>";

                if (row.messageText || row.fileUrl) {
                    if (sender_id == row.senderId) {
                        message_content += renderMessage(row, "outgoing");

                        // message_content += '<div class="outgoing_msg">'
                        // message_content += '<div class="sent_msg">'
                        // message_content += content
                        //     /* message_content += '<p>' + row.messageText + '</p>'
                        //     message_content += '<span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span>'*/
                        // message_content += '</div>'
                        // message_content += '</div>'
                        // message_content += '</div>'
                    } else {
                        message_content += renderMessage(row, "incoming");
                        // message_content += '<div class="incoming_msg">'
                        // message_content += '<div class="incoming_msg_img"> <img src="' + profile + '" alt="sunil" class="rounded-circle"> </div>'
                        // message_content += '<div class="received_msg">'
                        // message_content += '<div class="received_withd_msg">'
                        // message_content += content
                        //     /* message_content += '<p>' + row.messageText + '</p>'
                        //     message_content += '<span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span>'*/
                        // message_content += '</div>'
                        // message_content += '</div>'
                        // message_content += '</div>'
                        // message_content += '</div>'
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
        $document.find(".msg_history").html(message_content);
        scrollToBottomFunc();
        setTimeout(() => {
            lazyLoading();
            imagePopup();
        }, 1000);
    });
}

function renderMessage(row, type, is_uploding = 0) {
    let html = "";
    let middleContent = "";
    let innerHtml = "";
    let is_message = row.messageText ? 1 : 0;
    var profile = receiver.profile_picture || "../public/images/default.png";

    if (is_message) {
        /* let decryptText = CryptoJS.AES.decrypt(
            row.messageText,
            "%n&c!h*e!a^l@t(h~h)u%b$"
        ).toString(CryptoJS.enc.Utf8);
        let messageText = JSON.parse(decryptText).text
            ? JSON.parse(decryptText).text
            : ""; */
        let messageText = row.messageText;
        innerHtml +=
            "<p>" +
            messageText +
            '</p><span class="time_date"> ' +
            moment(row.timestamp).format("h:mm A | MMM d ") +
            "</span>";
    } else {
        let imageTypes = ["jpg", "jpeg", "png", "svg", "tif", "tiff"];

        /* Image not allowed */
        if (!imageTypes.includes(row.fileType)) {
            /* c(row.fileType);
            return false; */
            switch (row.fileType) {
                case "pdf":
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/pdf.png" width="100"></div>';
                    break;
                case "ppt":
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/ppt.png" width="100"></div>';
                    break;
                case "pptx":
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/pptx.png" width="100"></div>';
                    break;
                case "txt":
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/txt.png" width="100"></div>';
                    break;
                case "xls":
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/xls.png" width="100"></div>';
                    break;
                case "xml":
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/xml.png" width="100"></div>';
                case "zip":
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/zip.png" width="100"></div>';
                    break;
                default:
                    middleContent +=
                        '<div class="first"><div class="fileLogo"><img src="' +
                        asset_url +
                        'images/file_icon/all.png" width="100"></div>';
            }
            middleContent += '<span class="name">' + row.fileName + "</span>";
            if (is_uploding) {
                middleContent += '<div class="image_upload_spinner"></div>';
            }
            middleContent += "</div>";
        }

        if (middleContent) {
            innerHtml += '<div class="file_container">';
            innerHtml += '<div class="main">';
            innerHtml += middleContent;
            innerHtml +=
                '<a href="javascript:;" class="downLink" download><i class="ion-ios-download-outline" aria-hidden="true" onclick="downloadFile(\'' +
                row.fileName +
                "','" +
                row.fileUrl +
                "')\"></i></a>";
            innerHtml += "</div>";
            innerHtml +=
                '<span class="time_date"> ' +
                moment(row.timestamp).format("h:mm A | MMM d ") +
                "</span></div>";
            innerHtml += "</div>";
            innerHtml += "</div>";
        } else {
            /* for image */
            innerHtml += '<div class="file_container">';
            innerHtml +=
                '<div class="chocolat-image" href="' +
                row.fileUrl +
                '" title="' +
                row.fileName +
                '">';
            innerHtml += '<div class="main">';
            innerHtml +=
                '<div class="first image_file"><img src="' + row.fileUrl + '">';
            if (is_uploding) {
                innerHtml += '<div class="image_upload_spinner"></div>';
            }
            innerHtml += "</div>";
            innerHtml +=
                '<a href="javascript:;" class="downLink" download><i class="ion-ios-download-outline" aria-hidden="true" onclick="downloadFile(\'' +
                row.fileName +
                "','" +
                row.fileUrl +
                "')\"></i></a>";
            innerHtml += "</div>";
            innerHtml +=
                '<span class="time_date"> ' +
                moment(row.timestamp).format("h:mm A | MMM d ") +
                "</span></div>";
            innerHtml += "</div>";
            innerHtml += "</div>";

            /* innerHtml += '<div class="image_container">'
            innerHtml += '<div class="fileBox"><img src="' + row.fileUrl + '" width="100"></img>'
            innerHtml += '<a href="' + row.fileUrl + '" class="downLink" download><i class="ion-ios-download-outline" aria-hidden="true"></i></a>'
            innerHtml += '</div><span class="time_date"> ' + moment(row.timestamp).format("h:mm A | MMM d ") + '</span></div>' */
        }
    }

    if (type == "outgoing") {
        html += '<div class="outgoing_msg">';
        html += '<div class="sent_msg">';
        html += innerHtml;
        html += "</div>";
        html += "</div>";
        html += "</div>";
    } else {
        html += '<div class="incoming_msg">';
        html +=
            '<div class="incoming_msg_img"> <img src="' +
            profile +
            '" alt="sunil" class="rounded-circle"> </div>';
        html += '<div class="received_msg">';
        html += '<div class="received_withd_msg">';
        html += innerHtml;
        html += "</div>";
        html += "</div>";
        html += "</div>";
        html += "</div>";
    }
    return html;
}

function downloadFile(name, url) {
    var xhr = new XMLHttpRequest();
    xhr.responseType = "blob";
    xhr.onload = function(event) {
        var blob = xhr.response;
        // const blob = new Blob([blob], { type: 'application/octet-stream' });
        this.fileUrl = window.URL.createObjectURL(blob);

        const anchors = document.createElement("a");
        // anchors.setAttribute('href', data.url);
        anchors.setAttribute("href", this.fileUrl);
        anchors.setAttribute("download", name);
        // anchors.innerText = "";
        anchors.click();
    };
    xhr.open("GET", url);
    xhr.send();
}

function sendMessage(identity = "") {
    let message_content = "";
    let text = $document.find(".write_msg").val();
    if (text.replace(/\s/g, "").length > 0) {
        let encryptText = CryptoJS.AES.encrypt(
            JSON.stringify({ text }),
            "%n&c!h*e!a^l@t(h~h)u%b$"
        ).toString();
        let message = {
            senderId: sender.id,
            receiverId: receiver.id,
            messageText: text,
            fileName: "",
            fileUrl: "",
            fileType: "",
            is_read: 0,
            timestamp: moment().format()
        };
        let data = {
            chatId: chatObject.id,
            message: message
        };

        message_content += '<div class="outgoing_msg">';
        message_content += '<div class="sent_msg">';
        message_content += "<p>" + text + "</p>";
        message_content +=
            '<span class="time_date"> ' +
            moment(message.timestamp).format("h:mm A | MMM d ") +
            "</span>";
        message_content += "</div>";
        message_content += "</div>";
        message_content += "</div>";
        $document.find(".msg_history").append(message_content);
        scrollToBottomFunc();
        $document.find(".write_msg").val("");
        socket.emit("sendMessage", JSON.stringify(data));
    }
}

function loadFile(event) {
    event.preventDefault();
    var uploadDiv = "";
    var is_image = 0;
    var file = event.target.files[0];
    let extension = file.name.split(".").pop();
    let random = Math.random()
        .toString(36)
        .substring(2, 1000);
    var fileName = random + "." + extension;

    if (event.target.files && event.target.files[0]) {
        var FileSize = event.target.files[0].size / 1024 / 1024; // in MB
        if (FileSize > 5) {
            // alert('File size exceeds 2 MB');
            toastrAlert(
                "error",
                "",
                "File too Big, please select a file less than 4 MB."
            );
            return false;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            // c(message_content.find('image_file'));
            // message_content.attr('src', e.target.result);
            let request_data = {
                senderId: sender.id,
                receiverId: receiver.id,
                messageText: "",
                fileName: file.name ? file.name : "",
                fileUrl: e.target.result,
                fileType: extension,
                timestamp: moment().format()
            };
            uploadDiv = renderMessage(request_data, "outgoing", 1);
            $document.find(".msg_history").append(uploadDiv);
            scrollToBottomFunc();
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    // return false;

    /* if (event.target.files && event.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e, message_content) {
            message_content = '';
            message_content += '<div class="outgoing_msg">'
            message_content += '<div class="sent_msg">'
            message_content += '<div class="image_container"><div class="fileBox"><img src="' + e.target.result + '" width="100"></img><div class="image_upload_spinner"></div></div><span class="time_date"> ' + moment().format("h:mm A | MMM d ") + '</span></div>'
            message_content += '</div>'
            message_content += '</div>'
            message_content += '</div>'
            $document.find('.msg_history').append(message_content);
            scrollToBottomFunc();
        }
        reader.readAsDataURL(event.target.files[0]); // convert to base64 string

    } */

    // Clear the selection in the file picker input.
    $document.find("#controllerForm")[0].reset();

    // Check if the file is an image.

    if (!file.type.match("image.*")) {
        is_image = 1;
    }

    var filePath = "chat/" + chatObject.id + "/" + fileName;
    var storageRef = storage.ref(filePath);
    var uploadTask = storageRef.put(file);
    uploadTask.on(
        "state_changed",
        function progress(snapshot) {
            var progress =
                (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
            console.log("Upload is " + progress + "% done");
            $document
                .find(".image_upload_spinner")
                .html(Math.round(progress) + "%");
            switch (snapshot.state) {
                case firebase.storage.TaskState.PAUSED: // or 'paused'
                    console.log("Upload is paused");
                    break;
                case firebase.storage.TaskState.RUNNING: // or 'running'
                    console.log("Upload is running");
                    break;
            }
        },
        function(error) {
            // Handle unsuccessful uploads
        },
        function() {
            // Handle successful uploads on complete
            // For instance, get the download URL: https://firebasestorage.googleapis.com/...
            uploadTask.snapshot.ref
                .getDownloadURL()
                .then(function(downloadURL) {
                    console.log("File available at", downloadURL);
                    let message = {
                        senderId: sender.id,
                        receiverId: receiver.id,
                        messageText: "",
                        fileName: file.name ? file.name : "",
                        fileUrl: downloadURL ? downloadURL : "",
                        fileType: extension,
                        is_read: 0,
                        timestamp: moment().format()
                    };
                    let data = {
                        chatId: chatObject.id,
                        message: message
                    };
                    socket.emit("sendMessage", JSON.stringify(data));
                    $document.find(".image_upload_spinner").remove();
                });
        }
    );

    return false;
}

function loadFileBK(event) {
    event.preventDefault();
    var file = event.target.files[0];

    // Clear the selection in the file picker input.
    $document.find("#controllerForm")[0].reset();

    // Check if the file is an image.
    if (!file.type.match("image.*")) {
        toastrAlert("error", "", "You can only share images.");
        return;
    }

    formData = new FormData();
    formData.append("senderId", sender.id);
    formData.append("receiverId", receiver.id);
    formData.append("chatId", chatObject.id);
    formData.append("timestamp", moment().format());
    formData.append("image", file);

    $.ajax({
            url: `${chatServerDomain}/uploadFile`,
            method: "post",
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new XMLHttpRequest();

                // Add progress event listener to the upload.
                xhr.upload.addEventListener("progress", function(event) {
                    var progressBar = $(".progress-bar");

                    if (event.lengthComputable) {
                        var percent = (event.loaded / event.total) * 100;
                        progressBar.width(percent + "%");

                        if (percent === 100) {
                            progressBar.removeClass("active");
                        }
                    }
                });

                return xhr;
            }
        })
        .done(function(res) {
            formData.forEach(function(val, key, fD) {
                formData.delete(key);
            });
            /* On image send append image box as sender */
            message_content = "";
            if (sender_id == res.data.senderId) {
                message_content += '<div class="outgoing_msg">';
                message_content += '<div class="sent_msg">';
                message_content +=
                    '<div class="image_container"><div class="fileBox"><img src="' +
                    res.data.fileUrl +
                    '" width="100"></img></div><span class="time_date"> ' +
                    moment(res.data.timestamp).format("h:mm A | MMM d ") +
                    "</span></div>";
                message_content += "</div>";
                message_content += "</div>";
                message_content += "</div>";
                $document.find(".msg_history").append(message_content);
                scrollToBottomFunc();
            }
        })
        .fail(function(xhr, status) {});
}

function syncActiveUser() {
    let dbUserObject = firebase.database().ref("users");
    dbUserObject.on("value", snapshot => {
        // console.log(snapshot.val());

        snapshot.forEach(function(childSnapshot) {
            var key = childSnapshot.key;
            var childData = childSnapshot.val();
            if (childData.online) {
                $document.find(".user_" + childData.id).addClass("online");
                $document
                    .find(".user_" + childData.id + " " + ".status")
                    .html("online");
            } else {
                $document.find(".user_" + childData.id).removeClass("online");
                $document
                    .find(".user_" + childData.id + " " + ".status")
                    .html("offline");
            }

            /* if(typeof childData.unread_count !== "undefined"){
                childData.unread_count > 0 ? $document.find('.user_' + childData.id + ' ' + '.unread_count').html(childData.unread_count).show() : $document.find('.user_' + childData.id + ' ' + '.unread_count').html(0).hide()
            } */

            /* messages.push({
                userId: childData.userId,
                text: childData.text,
                date: childData.date,
            }) */
        });
    });
}

function syncMessageCounter(is_read = 0) {
    var messageCountRef = firebase
        .database()
        .ref("unreadCounter/user_" + sender_id);

    if (is_read) {
        messageCountRef.child(is_read).set(0);
    }

    messageCountRef.on("value", snapshot => {
        snapshot.forEach(function(childSnapshot) {
            var user_id = childSnapshot.key;
            var count = childSnapshot.val();
            /* c('user_id : ' + user_id + ' | count : ' + count);
            c(receiver); */
            if (typeof receiver == "undefined") {
                count > 0 ?
                    $document
                    .find(".user_" + user_id + " " + ".unread_count")
                    .html(count)
                    .show() :
                    $document
                    .find(".user_" + user_id + " " + ".unread_count")
                    .html(0)
                    .hide();
            } else if (
                typeof receiver !== "undefined" &&
                receiver.id != user_id
            ) {
                count > 0 ?
                    $document
                    .find(".user_" + user_id + " " + ".unread_count")
                    .html(count)
                    .show() :
                    $document
                    .find(".user_" + user_id + " " + ".unread_count")
                    .html(0)
                    .hide();
            } else if (
                typeof receiver !== "undefined" &&
                receiver.id == user_id
            ) {
                $document
                    .find(".user_" + user_id + " " + ".unread_count")
                    .html(0)
                    .hide();
            }
        });
    });
}

function syncMessage(id) {
    var messageRef = firebase.database().ref("chats/" + id);

    messageRef.on("value", snapshot => {
        snapshot.forEach(function(childSnapshot) {
            var user_id = childSnapshot.key;
            var count = childSnapshot.val();
            /* c('user_id : ' + user_id + ' | count : ' + count);
            c(receiver); */
            c("this is new chat");
            c(count);
        });
    });
}

function getUserDetail(id) {
    var userRef = firebase.database().ref("users/" + sender_id);
    let value = "";
    userRef.on("value", snapshot => {
        value = snapshot.val();
    });
    return value;
}

function lazyLoading() {
    $(".lazy").Lazy();
}

function imagePopup() {
    document.addEventListener("DOMContentLoaded", function(event) {
        Chocolat(document.querySelectorAll(".chocolat-image"));
    });

    Chocolat(document.querySelectorAll(".chocolat-image"));
}

/* function notifyTyping(identity) {
    console.log($(identity).keycode);
    let req = { sender: sender, receiver: receiver }
    socket.emit('notifyTyping', JSON.stringify(req));
} */

// make a function to scroll down auto
function scrollToBottomFunc() {
    if ($(".msg_history").length > 0) {
        $(".msg_history").animate({
                scrollTop: $(".msg_history").get(0).scrollHeight
            },
            "slow"
        );
    }
}

function playNewMessageAudio() {
    console.log("new message");
    var playNewMessagePromise = new Audio(
        "https://notificationsounds.com/soundfiles/8b16ebc056e613024c057be590b542eb/file-sounds-1113-unconvinced.mp3"
    ).play();

    if (playNewMessagePromise !== undefined) {
        playNewMessagePromise
            .then(_ => {
                // Automatic playback started!
                // Show playing UI.
            })
            .catch(error => {
                // Auto-play was prevented
                // Show paused UI.
            });
    }
}

// Function to play a audio when new message arrives on selected chatbox
function playNewMessageNotificationAudio() {
    var playPromise = new Audio(
        "https://notificationsounds.com/soundfiles/dd458505749b2941217ddd59394240e8/file-sounds-1111-to-the-point.mp3"
    ).play();

    if (playPromise !== undefined) {
        playPromise
            .then(_ => {
                // Automatic playback started!
                // Show playing UI.
            })
            .catch(error => {
                // Auto-play was prevented
                // Show paused UI.
            });
    }
}

function askForNotificationApproval(title, body) {
    if (Notification.permission === "granted") {
        createNotification(body, title, asset_url + "/images/logo/nc_one.png");
        /* createNotification('Wow! This is great', 'created by @study.tonight', 'https://www.studytonight.com/css/resource.v2/icons/studytonight/st-icon-dark.png'); */
    } else {
        Notification.requestPermission(permission => {
            if (permission === "granted") {
                createNotification(
                    body,
                    title,
                    asset_url + "/images/logo/nc_one.png"
                );
                /* createNotification('Wow! This is great', 'created by @study.tonight', 'https://www.studytonight.com/css/resource.v2/icons/studytonight/st-icon-dark.png'); */
            }
        });
    }
}