var receiver, chatObject, activeUsers, chatWindowID;
/* var callRef = database.ref('calls/history');
var localWindow = document.getElementById("localScreen");
var remoteWindow = document.getElementById("RemoteScreen"); */
var localWindow, remoteWindow;
var localStream;
var event_candidate;
$document = $(document);
$document.ready(function () {
    $.ajaxSetup({
        headers: header
    });
    syncFirebaseActivity(0);
    syncSocketActivity(0);
    init_connection();
    showMyFace()
    syncActivity();
    resetCall();

    $document.on('click', '.thumb', function () {
        localWindow.classList.toggle("thumb");
        remoteWindow.classList.toggle("thumb");
        if ($(this).hasClass('localWindow')) {
            $(localWindow).draggable("destroy")
            $(remoteWindow).draggable({ containment: "parent" });
        } else {
            $(localWindow).draggable({ containment: "parent" });
            $(remoteWindow).draggable("destroy")
        }
    })

    $('.click_advance').click(function () {
        $("i", this).toggleClass("ion-ios-videocam-outline ion-ios-videocam-off-outline");
    });

    // c(navigator.userAgent.toLowerCase());
    var isWeb = /mozilla|applewebkit|chrome|safari/i.test(navigator.userAgent.toLowerCase());
    if (isWeb) {
        c('Is a web device. ' + navigator.userAgent.toLowerCase());
    }
});

var pc;
var yourId = sender.id;
var deviceType = '';
var is_receiver_available = false;
var is_incoming_call = false;

function init_connection() {

    localWindow = document.getElementById("localScreen");
    remoteWindow = document.getElementById("RemoteScreen");

    var mediaConstraints = {
        optional: [{RtpDataChannels: true}]
    };

    var servers = {
        'iceServers': [
            { 'urls': 'stun:stun.services.mozilla.com' },
            { 'urls': 'stun:stun.l.google.com:19302' },
        ],
        /* TcpCandidatePolicy: "enabled",
        ContinualGatheringPolicy: "gathering" */
    };

    pc = new RTCPeerConnection(servers, mediaConstraints);

    pc.onicecandidate = (event => {
        event.candidate ? storeData(yourId, receiver.id, JSON.stringify({ 'ice': event.candidate })) : console.log("Sent All Ice")
    });
    pc.onaddstream = (event => {
        remoteWindow.srcObject = event.stream;
    });

    pc.addEventListener("iceconnectionstatechange", event => {
        c(pc.iceConnectionState);
        if (pc.iceConnectionState === "failed") {
            /* possibly reconfigure the connection in some way here */
            /* then request ICE restart */
            pc.restartIce();
        }
        if (pc.iceConnectionState === "disconnected") {
            deviceType = '';
            hangup();
        }
    });

    /* navigator.mediaDevices.getUserMedia({ audio: false, video: true })
        .then(gotLocalMediaStream).catch(function (err) {
            let error_msg = '';
            if (err.name == "NotFoundError" || err.name == "DevicesNotFoundError") {
                error_msg = 'Requested device not found'
            } else if (err.name == "NotReadableError" || err.name == "TrackStartError") {
                error_msg = 'webcam or mic are already in use '
            } else if (err.name == "OverconstrainedError" || err.name == "ConstraintNotSatisfiedError") {
                error_msg = 'constraints can not be satisfied by avb. devices '
            } else if (err.name == "NotAllowedError" || err.name == "PermissionDeniedError") {
                error_msg = 'permission denied in browser '
            } else if (err.name == "TypeError" || err.name == "TypeError") {
                error_msg = 'empty constraints object '
            } else {
                error_msg = 'Something went wrong'
            }
            toastrAlert('error', 'Warning', error_msg);
        }) */
}

function storeData(senderId, receiverId, data) {
    let tempData = JSON.parse(data);
    c('Store Data: Get Ice');
    c(tempData.ice);
    if (typeof tempData.ice !== "undefined") {
        c('Requested device type: ' + deviceType);
        if (tempData.ice.sdpMid === "0" && deviceType === "mobile") {
            tempData.ice.sdpMid = (tempData.ice.sdpMid == "0") ? "audio" : "video";
        } else if (tempData.ice.sdpMid === "audio") {
            // tempData.ice.sdpMid = "0";
        }
        c('response of audio video');
        c(tempData.ice.sdpMid);
        database.ref('calls/' + receiverId + '/data').update(tempData);
    }
}

function syncActivity() {

    let callObject = firebase.database().ref('calls/' + sender.id);
    callObject.on('value', snapshot => {
        var child = snapshot.val();
        c('Update my body');
        c(child)
        if (child != null) {

            if (child.status === false && typeof child.id !== "undefined" && child.data == null) {
                c('Answer the call');

                if (is_incoming_call == false) {
                    hangoutsAudioElement.play();
                    $.confirm({
                        theme: 'supervan',
                        title: child.name ? child.name : '',
                        content: 'is calling you...',
                        buttons: {
                            Accept: function () {
                                // here the button key 'hey' will be used as the text.
                                database.ref('calls/' + yourId).update({ 'status': true });
                                hangoutsAudioElement.pause();
                            },
                            Reject: {
                                text: 'Reject', // With spaces and symbols
                                action: function () {
                                    is_incoming_call = false;
                                    firebase.database().ref('calls/' + yourId).set({ 'status': false });
                                    firebase.database().ref('calls/' + child.id).set({ 'status': false });
                                    firebase.database().ref('users/' + yourId).update({ 'available': true });
                                    firebase.database().ref('users/' + child.id).update({ 'available': true });
                                    hangoutsAudioElement.pause();
                                }
                            }
                        }
                    });
                    is_incoming_call = true;
                }
                // incomingCallPopup.show();
                /* var r = confirm("Answer the call?");
                if (r == true) {
                    database.ref('calls/' + yourId).update({ 'status': true });
                } */
            }

            if (child.status == true && child.data) {

                /* var value = snapshot.val(); */
                var msg = child.data ? child.data : '';
                var sender = child.id;
                var receiver = snapshot.key;

                if (msg.ice != undefined) {
                    c('get ice from:= ' + child.id);
                    c(msg);
                    // msg.ice.sdpMid == "0" ? 'audio' : 'video';
                    pc.addIceCandidate(new RTCIceCandidate(msg.ice)).catch(e => {
                        console.log("Failure during addIceCandidate(): " + e.name);
                    });

                } else if (msg.sdp.type.toLowerCase() == "offer" && child.status === true) {
                    c('receive offer');
                    c(msg.sdp);

                    pc.setRemoteDescription(new RTCSessionDescription(msg.sdp))
                        .then(() => pc.createAnswer())
                        .then(answer => pc.setLocalDescription(answer))
                        .then(() => {
                            let data = JSON.stringify({ 'sdp': pc.localDescription });
                            database.ref('calls/' + sender).update({ 'data': JSON.parse(data) });
                            $(localWindow).addClass('thumb').draggable({ containment: "parent" });
                            $(remoteWindow).addClass('big').show();
                            hangupBtn.show();
                            callBtn.hide();
                            startVideoBtn.hide();
                            stopVideoBtn.show();
                            startAudioBtn.hide();
                            stopAudioBtn.show();
                        });
                } else if (msg.sdp.type.toLowerCase() == "answer") {
                    pc.setRemoteDescription(new RTCSessionDescription(msg.sdp));
                    c('given answer by receiver: ' + receiver + ' and their sdp is:')
                    /* database.ref('calls/' + receiver).update({ 'status': true });
                    database.ref('users/' + sender).update({ 'call_status': 'active', 'available': true });
                    database.ref('users/' + receiver).update({ 'call_status': 'active', 'available': true }); */
                    hangupBtn.show();
                    callBtn.hide();
                    startVideoBtn.hide();
                    stopVideoBtn.show();
                    startAudioBtn.hide();
                    stopAudioBtn.show();
                    $(localWindow).addClass('thumb').draggable({ containment: "parent" });
                    $(remoteWindow).addClass('big').show();;
                }
            }

            if (child.status == false) {
                // is_incoming_call = false;
            }
        }
    })

    let receiverCallListener = firebase.database().ref('calls/' + receiver.id);
    receiverCallListener.on('value', snapshot => {
        var child = snapshot.val();
        if (child != null) {
            // c("Receptor body of id := " + snapshot.key);
            // c(child);

            if (child.status === true && typeof child.id !== "undefined" && typeof child.data == "undefined") {
                /* var r = confirm("Accept the call?");
                if (r == true) {
                    deviceType = child.device;
                    showFriendsFace();
                } */

                if (child.id == yourId) {
                    callingAudioElement.pause();
                    deviceType = child.device;
                    showFriendsFace();
                    $.confirm({
                        title: '',
                        content: 'Call pickup',
                        type: 'green',
                        typeAnimated: true,
                        autoClose: 'cancelAction|1000',
                        escapeKey: 'cancelAction',
                        buttons: {
                            cancelAction: {
                                text: 'Close',
                                action: function () {

                                }
                            }
                        }
                    });
                }
            }
        }
    });

    let receiverListener = firebase.database().ref('users/' + receiver.id);
    receiverListener.on('value', snapshot => {
        var child = snapshot.val();
        if (child != null) {
            is_receiver_available = child.available;
        }
    });
}

function showMyFace() {
    navigator.mediaDevices.getUserMedia({ audio: true, video: true })
        .then(gotLocalMediaStream).catch(function (err) {
            let error_msg = '';
            if (err.name == "NotFoundError" || err.name == "DevicesNotFoundError") {
                error_msg = 'Requested device not found'
                /* required track is missing */
            } else if (err.name == "NotReadableError" || err.name == "TrackStartError") {
                error_msg = 'webcam or mic are already in use '
            } else if (err.name == "OverconstrainedError" || err.name == "ConstraintNotSatisfiedError") {
                error_msg = 'constraints can not be satisfied by avb. devices '
            } else if (err.name == "NotAllowedError" || err.name == "PermissionDeniedError") {
                error_msg = 'permission denied in browser '
            } else if (err.name == "TypeError" || err.name == "TypeError") {
                error_msg = 'empty constraints object '
            } else {
                error_msg = 'Something went wrong'
            }
            toastrAlert('error', 'Warning', error_msg);
        })
}

function call() {
    c("is_receiver_available");
    c(is_receiver_available);
    if (is_receiver_available) {
        database.ref('calls/' + receiver.id).set({ 'id': yourId, 'name': receiver.name, 'device': 'web', 'status': false });
        database.ref('calls/' + sender.id).update({ 'status': true })
        callingAudioElement.play();
    } else {
        toastrAlert('info', 'Info', 'User are currently busy with another. please try after sometime.');
    }

}

function hangup() {
    pc.close();
    pc = null;
    is_incoming_call = false;
    $(localWindow).removeClass('thumb').draggable("destroy")
    $(remoteWindow).html('').css({ 'display': 'none' });
    firebase.database().ref('calls/' + my_id).set({ 'status': false });
    firebase.database().ref('calls/' + receiver.id).set({ 'status': false });
    firebase.database().ref('users/' + my_id).update({ 'available': true });
    firebase.database().ref('users/' + receiver.id).update({ 'available': true });
    $document.find('#btnCall').show();
    $document.find('#btnHangup').hide();
    init_connection();
    showMyFace()
    /* location.reload(); */
}

function showFriendsFace() {
    // {iceRestart: true}
    pc.createOffer()
        .then(offer => pc.setLocalDescription(offer))
        .then(() => {
            /* storeData(yourId, receiver.id, JSON.stringify({ 'sdp': pc.localDescription })) */
            let sdp = JSON.stringify({ 'sdp': pc.localDescription });
            database.ref('calls/' + receiver.id).update({ 'data': JSON.parse(sdp) });
            database.ref('users/' + my_id).update({ 'available': false });
            database.ref('users/' + receiver.id).update({ 'available': false });
            // database.ref('users/' + receiver.id).update({ 'video_call': { 'status': 'incoming', 'from': sender.id } });
            // database.ref('calls/' + sender.id).update({ 'status': true })
        });
}

function readMessage(data) {
    // c("logged user: " + yourId + ', Receiver user: ' + data.val().receiver);
    // c("Sender user: " + data.val().sender + ', Receiver user: ' + data.val().receiver);
    // var msg = JSON.parse(data.val());
    c('readMessage');
    /* c(data.val().sender);
    c(data.val().message); */
    var msg = data.val().data;
    // var msg = JSON.parse(data.val().message);
    var sender = data.val().sender;
    var receiver = data.val().receiver;
    if (yourId == receiver) {

        /* c("Sender user: " + data.val().sender + ', Receiver user: ' + data.val().receiver);
        c('SDP type: ');
        c(msg.sdp); */

        if (msg.ice != undefined) {
            /* c('number of ice candidate');
            c(msg); */
            database.ref('calls/' + yourId).child('data').update(msg);
            database.ref('calls/' + sender).child('data').update(msg);
            pc.addIceCandidate(new RTCIceCandidate(msg.ice));
        } else if (msg.sdp.type == "offer") {
            /* c('Receive Offer');
            c("logged user: " + yourId + ', Receiver user: ' + data.val().receiver);
            c("Sender user: " + data.val().sender + ', Receiver user: ' + data.val().receiver);
            c(data.val()); */
            // var r = confirm("Answer call?");
            // if (r == true) {
            pc.setRemoteDescription(new RTCSessionDescription(msg.sdp))
                .then(() => pc.createAnswer())
                .then(answer => pc.setLocalDescription(answer))
                .then(() => {
                    storeData(yourId, sender, JSON.stringify({ 'sdp': pc.localDescription }))
                    let data = JSON.stringify({ 'sdp': pc.localDescription });
                    database.ref('calls/' + receiver).update({ 'data': JSON.parse(data), 'status': true });
                    database.ref('calls/' + sender).update({ 'data': msg });
                });
            // } else {
            //     c("Rejected the call by receiver" + receiver);
            // }
        } else if (msg.sdp.type == "answer") {
            pc.setRemoteDescription(new RTCSessionDescription(msg.sdp));
            c('given answer by receiver: ' + receiver)
            database.ref('calls/' + receiver).update({ 'status': true });
            database.ref('calls/' + sender).child('data').update({ 'sdp': msg.sdp });
            hangupBtn.show();
        }
    }
};

function resetCall() {
    database.ref('calls/' + sender.id).set({ 'status': false });
    database.ref('calls/' + receiver.id).set({ 'status': false });
}

function gotLocalMediaStream(mediaStream) {
    localWindow.srcObject = mediaStream;
    localStream = mediaStream;
    pc.addStream(mediaStream)
    controlsBtn.css({ 'display': 'block' })
}