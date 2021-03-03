var receiver, chatObject, activeUsers, chatWindowID;
var callRef = database.ref('calls/history');
var localWindow = document.getElementById("localScreen");
var remoteWindow = document.getElementById("RemoteScreen");
var localStream;
var event_candidate;
$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });
    syncFirebaseActivity(0);
    init_connection();
    showMyFace()
    syncActivity();
    resetCall();

    $document.on('click', '.thumb', function() {
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

    $('.click_advance').click(function() {
        $("i", this).toggleClass("ion-ios-videocam-outline ion-ios-videocam-off-outline");
    });

});

var pc;
var yourId = sender.id;
// var yourId = Math.floor(Math.random() * 1000000000);

function init_connection() {
    var servers = {
        'iceServers': [
        { 'urls': 'stun:stun.services.mozilla.com' },
        { 'urls': 'stun:stun.l.google.com:19302' },
            /* {
                'urls': 'turn:74.207.248.179',
                credential: 'hfuEpkdY837',
                username: 'root'
            } */
            /* { 'urls': 'turn:numb.viagenie.ca', 'credential': 'webrtc', 'username': 'websitebeaver@mail.com' } */
            ],
            TcpCandidatePolicy: "enabled",
            ContinualGatheringPolicy: "gathering"
        // BundlePolicy: "MAXBUNDLE",
        // RtcpMuxPolicy: "REQUIRE",
        // KeyType: "ECDSA"
    };

    pc = new RTCPeerConnection(servers);

    pc.onicecandidate = (event => {
        event.candidate ? storeData(yourId, receiver.id, JSON.stringify({ 'ice': event.candidate })) : console.log("Sent All Ice")
    });
    pc.onaddstream = (event => {
        remoteWindow.srcObject = event.stream;
    });

    pc.addEventListener("iceconnectionstatechange", event => {
        if (pc.iceConnectionState === "failed") {
            /* possibly reconfigure the connection in some way here */
            /* then request ICE restart */
            pc.restartIce();
        }
    });
}



function storeData(senderId, receiverId, data) {
    let tempData = JSON.parse(data);
    // c("call by sender : Sender user: " + senderId + ', Receiver user: ' + receiverId);
    c('Store Data: Get Ice');
    c(tempData.ice);
    if (typeof tempData.ice !== "undefined") {
        // tempData.ice.sdpMid = (tempData.ice.sdpMid == "audio" || tempData.ice.sdpMid == "audio") ? "0" : "1";
        var msg = database.ref('calls/' + receiverId + '/data').update(tempData);
    } else {
        // var msg = database.ref('calls/' + receiverId).child('data').update(JSON.parse(data));
    }
    // var msg = callRef.push({ sender: senderId, receiver: receiverId, message: JSON.parse(data) });
    // msg.remove();
}

// callRef.on('child_added', readMessage);

function syncActivity() {
    let callObject = firebase.database().ref('calls/' + sender.id);
    callObject.on('value', snapshot => {

        // console.log(snapshot.val());
        // if (snapshot.val() != null && snapshot.val().id && snapshot.val().data == null) {
        //     var conf = confirm("Answer call?");
        //     if (conf == true) {
        //         database.ref('calls/' + yourId).update({ 'status': true });
        //     } else {
        //         c("Rejected the call by receiver" + receiver);
        //     }
        // }

        if (snapshot.val() != null && snapshot.val().data) {

            var value = snapshot.val();
            var msg = snapshot.val().data ? snapshot.val().data : '';
            var sender = snapshot.val().id;
            var receiver = snapshot.key;
            /* c('sender: ' + sender + ' : receiver: ' + snapshot.key); */

            if (msg.ice != undefined) {
                c('number of ice candidate');
                c(msg);
                // msg.ice.sdpMid = (msg.ice.sdpMid == '1') ? "video" : "audio";
                // database.ref('calls/' + yourId).child('data').update(msg);
                // database.ref('calls/' + sender).child('data').update(msg); 

                pc.addIceCandidate(new RTCIceCandidate(msg.ice)).catch(e => {
                    console.log("Failure during addIceCandidate(): " + e.name);
                });

            } else if (msg.sdp.type.toLowerCase() == "offer" && value.status === false) {
                var r = confirm("Answer call?");
                if (r == true) {
                    pc.setRemoteDescription(new RTCSessionDescription(msg.sdp))
                    .then(() => pc.createAnswer())
                    .then(answer => pc.setLocalDescription(answer))
                    .then(() => {
                            // storeData(yourId, sender, JSON.stringify({ 'sdp': pc.localDescription }))
                            let data = JSON.stringify({ 'sdp': pc.localDescription });
                            database.ref('calls/' + sender).update({ 'data': JSON.parse(data) });
                            database.ref('calls/' + receiver).update({ 'status': true });
                            $(localWindow).addClass('thumb').draggable({ containment: "parent" });
                            $(remoteWindow).addClass('big');
                        });
                } else {
                    c("Rejected the call by receiver" + receiver);
                }
            } else if (msg.sdp.type.toLowerCase() == "answer") {
                pc.setRemoteDescription(new RTCSessionDescription(msg.sdp));
                c('given answer by receiver: ' + receiver + ' and their sdp is:')
                database.ref('calls/' + receiver).update({ 'status': true });
                // database.ref('calls/' + sender).child('data').update({ 'sdp': msg.sdp });
                hangupBtn.show();
                callBtn.hide();
                $(localWindow).addClass('thumb').draggable({ containment: "parent" });
                $(remoteWindow).addClass('big');
            }
        }

        if (snapshot.val() != null && !snapshot.val().status === false) {
            c('disconnect the call');
            /* $(remoteWindow).html('').css({ 'display': 'none' });
            hangupBtn.hide();
            callBtn.show(); */
        }
    })
}

function showMyFace() {
    navigator.mediaDevices.getUserMedia({ audio: false, video: true })
        // .then(stream => localWindow.srcObject = stream)
        .then(gotLocalMediaStream).catch(function(err) {
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
        // .then(stream => pc.addStream(stream));
        /* .then(stream => {
            c(stream);
            localStream = stream
        }); */

    // database.ref('calls/user_' + sender.id).set({ 'status': false });
}

function showFriendsFace() {
    pc.createOffer()
    .then(offer => pc.setLocalDescription(offer))
    .then(() => {
        let sdp = JSON.stringify({ 'sdp': pc.localDescription });
        storeData(yourId, receiver.id, JSON.stringify({ 'sdp': pc.localDescription }))
        database.ref('calls/' + receiver.id).set({ 'id': yourId, 'data': JSON.parse(sdp), 'status': false });
        database.ref('calls/' + sender.id).update({ 'status': true })
    });
}

// function showFriendsFace() {
//     pc.createOffer()
//     .then(answer => pc.setLocalDescription(answer))
//     .then(() => {
//         let sdp = JSON.stringify({ 'sdp': pc.localDescription });
//         storeData(yourId, receiver.id, JSON.stringify({ 'sdp': pc.localDescription }))
//         database.ref('calls/' + receiver.id).set({ 'id': yourId, 'data': JSON.parse(sdp), 'status': false });
//         database.ref('calls/' + sender.id).update({ 'status': true })
//     });
// }


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
            var r = confirm("Answer call?");
            if (r == true) {
                pc.setRemoteDescription(new RTCSessionDescription(msg.sdp))
                .then(() => pc.createAnswer())
                .then(answer => pc.setLocalDescription(answer))
                .then(() => {
                    storeData(yourId, sender, JSON.stringify({ 'sdp': pc.localDescription }))
                    let data = JSON.stringify({ 'sdp': pc.localDescription });
                    database.ref('calls/' + receiver).update({ 'data': JSON.parse(data), 'status': true });
                    database.ref('calls/' + sender).update({ 'data': msg });
                });
            } else {
                c("Rejected the call by receiver" + receiver);
            }
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

stopVideo = function() {
    localStream.getVideoTracks()[0].enabled = false;
}

startVideo = function() {
    localStream.getVideoTracks()[0].enabled = true;
}

stopAudio = function() {
    localStream.getAudioTracks()[0].enabled = false;
}

startAudio = function() {
    localStream.getAudioTracks()[0].enabled = true;
}

callHangup = function() {
    /* localstream.getTracks()[0].stop();
    localstream = null; */
    database.ref('calls/' + sender.id).set({ 'status': false });
    database.ref('calls/' + receiver.id).set({ 'status': false });
    pc.close();
    $(remoteWindow).html('').css({ 'display': 'none' });
    $(localWindow).removeClass('thumb').draggable("destroy")
    hangupBtn.hide();
    callBtn.show();
    init_connection();
}

function hangupAction() {
    pc.close();
}

function gotLocalMediaStream(mediaStream) {
    localWindow.srcObject = mediaStream;
    pc.addStream(mediaStream)
    localStream = mediaStream;
    controlsBtn.css({ 'display': 'block' })
}