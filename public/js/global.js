$document = $(document);
var chatObject, activeUsers, chatWindowID;
var hangoutsAudioElement, callingAudioElement;
var intlPhone, iti;
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    init_authpage();
    detectLocation();
    init_inquiry_form();
    init_tooltip();
    loadAudioTune();
    syncFirebaseActivity();
    syncSocketActivity();
});

//image preview on change
var image_preview = function(input, block) {
    var fileTypes = ["jpg", "jpeg", "png"];
    var extension = input.files[0].name
        .split(".")
        .pop()
        .toLowerCase(); /*se preia extensia*/
    var isSuccess = fileTypes.indexOf(extension) > -1; /*se verifica extensia*/

    if (isSuccess) {
        var reader = new FileReader();

        reader.onload = function(e) {
            $("#imagePreview").show();
            block.attr("src", e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        alert("Something was wrong !.");
    }
};

$(document).on("change", "#profile_picture", function() {
    image_preview(this, $(".imagePreview"));
});

$(document).on("change", "#image", function() {
    image_preview(this, $(".imagePreview"));
});

/* Custom Validation Method */
$.validator.addMethod(
    "valid_password",
    function(value, element) {
        return (
            this.optional(element) ||
            /^(?=.*[A-Z][a-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/.test(
                value
            )
        );
    },
    "Password must contain minimum 8 characters with at least 1 lowercase, 1 uppercase, 1 number and 1 special character."
);

$.validator.addMethod(
    "valid_phone",
    function(value, element) {
        /*return this.optional(element) || value.length >= 10;*/
        console.log(iti.isValidNumber());
        if (value.trim()) {
            return this.optional(element) || iti.isValidNumber();
        }
    },
    "Please enter a valid mobile number"
);

$.validator.addMethod(
    "validDate",
    function(value, element) {
        return this.optional(element) || /^\d{4}-\d{2}-\d{2}$/.test(value);
    },
    "Please provide a date in the dd-mm-yyyy format"
);

$.validator.addMethod(
    "dateBefore",
    function(value, element, params) {
        // if end date is valid, validate it as well
        var end = $(params);
        if (!end.data("validation.running")) {
            $(element).data("validation.running", true);
            setTimeout(
                $.proxy(function() {
                    this.element(end);
                }, this),
                0
            );
            // Ensure clearing the 'flag' happens after the validation of 'end' to prevent endless looping
            setTimeout(function() {
                $(element).data("validation.running", false);
            }, 0);
        }
        return (
            this.optional(element) ||
            this.optional(end[0]) ||
            new Date(value) < new Date(end.val())
        );
    },
    "Must be before corresponding end date"
);

$.validator.addMethod(
    "dateAfter",
    function(value, element, params) {
        // if start date is valid, validate it as well
        var start = $(params);
        if (!start.data("validation.running")) {
            $(element).data("validation.running", true);
            setTimeout(
                $.proxy(function() {
                    this.element(start);
                }, this),
                0
            );
            setTimeout(function() {
                $(element).data("validation.running", false);
            }, 0);
        }
        return (
            this.optional(element) ||
            this.optional(start[0]) ||
            new Date(value) > new Date($(params).val())
        );
    },
    "Must be after corresponding start date"
);

$.validator.addMethod(
    "zipcode",
    function(value, element) {
        return this.optional(element) || /^\d{6}$|^\d{5}-\d{4}$/.test(value);
    },
    "Please provide a valid zipcode."
);

$.validator.addMethod(
    "gst",
    function(value, element) {
        return (
            this.optional(element) ||
            /^([0-2][0-9]|[3][0-7])[A-Z]{3}[ABCFGHLJPTK][A-Z]\d{4}[A-Z][A-Z0-9][Z][A-Z0-9]$/.test(
                value
            )
        );
    },
    "Please Enter Valid GSTIN Number."
);

/* Custom Functions */

function detectLocation(location = "") {
    if (typeof getLocation !== "undefined") {
        $.ajax({
            url: getLocation,
            type: "post",
            dataType: "json",
            data: { location: location },
            beforeSend: function() {},
            success: function(res) {
                if (res.status == 200 && res.location) {
                    $("input[name=location]").val(res.location.city);
                    // $("input[name=location]").val(res.cityname);
                } else {
                    alert(res.message);
                }
            },
            error: function(res) {
                console.log(res);
            },
            complete: function() {}
        });
    }
}

function intlPhoneField(e, i, t = 0) {
    e || (e = "phone"), (intlPhone = document.querySelector("#" + e));

    iti = window.intlTelInput(intlPhone, {
        utilsScript: asset_url + "js/dialcode/utils.js",
        // preferredCountries: ['in', 'us', 'gb', 'ch', 'ca', 'do'],
        initialCountry: "in"
            // hiddenInput: "dialcode",
    });
}

function createNotification(title, text, icon) {
    const noti = new Notification(title, {
        body: text,
        icon
    });
}

function loadAudioTune() {
    hangoutsAudioElement = document.createElement("audio");
    hangoutsAudioElement.setAttribute(
        "src",
        asset_url + "/audio/hangouts_video_call.mp3"
    );
    hangoutsAudioElement.addEventListener(
        "ended",
        function() {
            this.play();
        },
        false
    );

    callingAudioElement = document.createElement("audio");
    callingAudioElement.setAttribute("src", asset_url + "/audio/calling.mp3");
    callingAudioElement.addEventListener(
        "ended",
        function() {
            this.play();
        },
        false
    );
}

function init_authpage() {
    $("input").focus(function() {
        $(this)
            .siblings("label")
            .addClass("active");
    });
    $("input").blur(function() {
        if ($(this).val().length > 0) {
            $(this)
                .siblings("label")
                .addClass("active");
        } else {
            $(this)
                .siblings("label")
                .removeClass("active");
        }
    });
}

function toastrAlert(status, title = "", message = "", position = "topCenter") {
    if (status === "success") {
        iziToast.success({
            title: title,
            message: message,
            position: position
        });
    }
    if (status === "error") {
        iziToast.error({
            title: title,
            message: message,
            position: position
        });
    }
    if (status === "warning") {
        iziToast.warning({
            title: title,
            message: message,
            position: position
        });
    }
    if (status === "info") {
        iziToast.info({
            title: title,
            message: message,
            position: position
        });
    }
}

function init_tooltip() {
    $('[data-toggle="tooltip"]').tooltip();
}

function init_select2() {
    $(".select2").select2();
}

function init_rating($class) {
    $("." + $class + "").starRating({
        starSize: 20,
        useFullStars: true,
        readOnly: true,
        useGradient: false
    });
}

function deleteRecord(
    header,
    ajax_url,
    tableID,
    title = "Are you sure?",
    text = "You will not be able to recover this record!",
    confirm_button_text = "Yes, delete it!",
    custom_class = ""
) {
    swal({
            html: true,
            title: title,
            text: text,
            type: "warning",
            showCancelButton: true,
            customClass: custom_class,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: confirm_button_text,
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        },
        function() {
            $.ajax({
                headers: header,
                url: ajax_url,
                type: "DELETE",
                processData: false,
                contentType: false,
                beforeSend: function() {},
                success: function(response) {
                    if (response.status === 200) {
                        var message = response.message ?
                            response.message :
                            "Your record has been deleted.";
                        tableID.draw();
                        swal({
                            title: "Deleted!",
                            text: message,
                            type: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    if (response.status === 400) {
                        toastrAlert("error", title, "Something went wrong.");
                        swal.close();
                    }
                },
                error: function() {
                    toastrAlert("error", title, "Something went wrong.");
                    swal.close();
                }
            });
        }
    );
}

function myWishlist(id) {
    if (typeof manageWishlistUrl !== "undefined") {
        $.ajax({
            headers: header,
            type: "POST",
            url: manageWishlistUrl,
            data: { doctor_id: id },
            success: function(data) {
                $("#isWishlist_" + id).html(data.html);
                $('[data-toggle="tooltip"]').tooltip("hide");
                init_tooltip();
                console.log(data);
                toastrAlert("success", "Favorite", data.message);
            },
            error: function(data) {
                if (data.status == 401) {
                    if (typeof login_url !== "undefined") {
                        window.location.href = login_url;
                    }
                }
            }
        });
    }
}

/* Autosearch  */
function setValue($id) {
    $("input[name=search]").val($id);
}

function autoSuggest(field) {
    var keyword = $(field).val();
    //if(search.length >= 2){ //this function work after 2 or more word for search
    if (typeof autoSearch !== "undefined") {
        $.ajax({
            url: autoSearch,
            type: "post",
            dataType: "json",
            data: { search: keyword },
            beforeSend: function() {},
            success: function(res) {
                $document.find("#search-list").html(res.html);
            },
            error: function(res) {},
            complete: function() {}
        });
    }
    //}
}

var currentRequest = null;

function autoSuggestCity(field) {
    var keyword = $(field).val();
    //if(search.length >= 2){ //this function work after 2 or more word for search
    if (typeof autoSearchCity !== "undefined") {
        currentRequest = $.ajax({
            url: autoSearchCity,
            type: "post",
            dataType: "json",
            data: { city: keyword },
            beforeSend: function() {
                if (currentRequest != null) {
                    currentRequest.abort();
                }
            },
            success: function(res) {
                if (res.is_exist) {
                    $document
                        .find("#location-list")
                        .html(res.html)
                        .show();
                } else {
                    $document.find("#location-list").html(res.html);
                }
            },
            error: function(res) {},
            complete: function() {}
        });
    }
    //}
}

function selectCity(location) {
    if (location) {
        detectLocation(location);
    }
}

function viewCallDetails($id) {
    $document.find("#phone-detail-" + $id).fadeToggle(); //use for phone details view of serched user at filter page
}

/*Add review */
function addReview(id) {
    if (typeof addReviewUrl !== "undefined") {
        $.ajax({
            url: addReviewUrl,
            type: "get",
            dataType: "json",
            data: { rateable_id: id },
            success: function(response) {
                if (response.status == "success") {
                    globalModal.html(response.html);
                    globalModal.modal("toggle");
                    if (response.data.rating) {
                        init_rating_box(response.data.rating.rating);
                        $("#review").html(response.data.rating.review);
                        $("#rating").val(response.data.rating.rating);
                    } else {
                        init_rating_box(0);
                    }
                    init_rating_form();
                } else {
                    //
                }
            },
            error: function() {
                //
            }
        });
    }
}

function init_rating_box(rating) {
    $(".add-rating").starRating({
        totalStars: 5,
        strokeWidth: 0,
        initialRating: rating,
        disableAfterRate: false,
        useFullStars: true,
        ratedColors: ["#ffa500", "#ffa500", "#ffa500", "#ffa500", "#ffa500"],
        callback: function(currentRating) {
            $("#rating").val(currentRating);
            $document.find("#rating-error").html("");
        }
    });
}

function init_rating_form() {
    ratingForm = $document.find("#ratingForm");

    //Jquery validation of form field
    ratingForm.validate({
        ignore: [],
        rules: {
            rating: "required"
        },
        messages: {
            rating: "Please give star rating"
        },
        errorPlacement: function(error, element) {
            $document.find("#rating-error").html(error.text());
        },
        submitHandler: function(form) {
            var action = $(form).attr("action");
            var formData = new FormData($(form)[0]);
            $.ajax({
                type: "POST",
                url: action,
                data: formData,
                processData: false,
                dataType: "json",
                contentType: false,
                beforeSend: function() {
                    ratingForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    ratingForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        globalModal.modal("toggle");
                        ratingForm.trigger("reset");
                        $(".review-list").html(data.html);
                        $("#totalRatingCount").html(data.total_rating);
                        $("#add_Review").html(" Edit Review");
                        $("#avg_rating_box_" + data.rateable_id).attr(
                            "data-rating",
                            data.avg_rating
                        );
                        $("#avg_rating_box_" + data.rateable_id).starRating(
                            "setRating",
                            data.avg_rating
                        );
                        init_rating("rating_box" + data.rateable_id);
                    }
                },
                error: function() {
                    //
                },
                complete: function() {
                    ratingForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    ratingForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}

function init_inquiry_form() {
    if (typeof inquiryForm != "undefined") {
        inquiryForm.validate({
            ignore: [],
            rules: {
                name: "required",
                email: {
                    required: true,
                    email: true
                },
                subject: "required",
                message: "required"
            },
            submitHandler: function(form) {
                var action = $(form).attr("action");
                var formData = new FormData($(form)[0]);
                $.ajax({
                    type: "POST",
                    url: action,
                    data: formData,
                    processData: false,
                    dataType: "json",
                    contentType: false,
                    beforeSend: function() {
                        inquiryForm
                            .find(".btn-submit")
                            .addClass("btn-progress");
                        inquiryForm.find(".btn-submit").prop("disabled", true);
                    },
                    success: function(data) {
                        if (data.status == "200") {
                            inquiryForm.trigger("reset");
                            $(".inquiry-message").html(data.message);
                            $(".inquiry-message").addClass("text-success");
                            $(".inquiry-message")
                                .fadeTo(2000, 500)
                                .slideUp(500, function() {
                                    $(".inquiry-message").removeClass(
                                        "text-success"
                                    );
                                    $(".inquiry-message").slideUp(500);
                                });
                        }
                        if (data.status == "400") {
                            $(".inquiry-message").html(data.message);
                            $(".inquiry-message").addClass("text-danger");
                            $(".inquiry-message")
                                .fadeTo(2000, 500)
                                .slideUp(500, function() {
                                    $(".inquiry-message").removeClass(
                                        "text-danger"
                                    );
                                    $(".inquiry-message").slideUp(500);
                                });
                        }
                    },
                    error: function(data) {},
                    complete: function() {
                        inquiryForm
                            .find(".btn-submit")
                            .removeClass("btn-progress");
                        inquiryForm.find(".btn-submit").prop("disabled", false);
                    }
                });
            }
        });
    }
}

var is_enable = 0;
var caller_id;
var is_incoming_call = false;

function syncFirebaseActivity(block = 1) {
    is_enable = block;
    /* is_enable = block;
    var starCountRef = firebase.database().ref('calls/' + my_id + '/incoming_call_from');
    starCountRef.on('value', function(snapshot) {
        c("is_enable:= " + is_enable);
        if (snapshot.val() !== null && snapshot.val() !== false && is_enable) {
            c('check the incoming call event');
            c(snapshot.val());
            starCountRef.set(false);
            window.location = videoConsultUrl.replace(':slug', snapshot.val())
        }
    }); */

    /* Sync user object activity */
    if (my_id && typeof firebase !== "undefined") {
        let callListener = firebase.database().ref("calls/" + my_id);
        callListener.on("value", snapshot => {
            var child = snapshot.val();
            if (child != null && is_enable) {
                c("Global function");
                c(child);

                if (
                    child.status === false &&
                    typeof child.id !== "undefined" &&
                    child.data == null
                ) {
                    caller_id = child.id;
                    c("receive call");
                    c(is_incoming_call);
                    if (is_incoming_call == false) {
                        hangoutsAudioElement.play();
                        $.confirm({
                            theme: "supervan",
                            title: child.name ? child.name : "",
                            content: "is calling you...",
                            buttons: {
                                Accept: function() {
                                    if (
                                        typeof videoChatboxUrl !==
                                        "undefined" &&
                                        caller_id
                                    ) {
                                        var url = videoChatboxUrl.replace(
                                            ":id",
                                            caller_id
                                        );
                                        $.ajax({
                                            url: url,
                                            type: "get",
                                            dataType: "json",
                                            success: function(response) {
                                                if (response.status == 200) {
                                                    globalModal.html(
                                                        response.result.html
                                                    );
                                                    globalModal.modal("toggle");
                                                    hangoutsAudioElement.pause();
                                                    init_video(response.result);
                                                    // init_dropzone_files(response.files);
                                                }
                                            },
                                            error: function() {}
                                        });
                                    }
                                },
                                Reject: {
                                    text: "Reject", // With spaces and symbols
                                    action: function() {
                                        database
                                            .ref("calls/" + my_id)
                                            .set({ status: false });
                                        database
                                            .ref("users/" + my_id)
                                            .update({ available: true });
                                        if (caller_id)
                                            database
                                            .ref("calls/" + caller_id)
                                            .set({ status: false });
                                        database
                                            .ref("users/" + caller_id)
                                            .update({ available: true });
                                        hangoutsAudioElement.pause();
                                        pc = null;
                                        is_incoming_call = false;
                                    }
                                }
                            }
                        });
                        is_incoming_call = true;
                    }
                    // incomingCallPopup.show();

                    // var r = confirm("Answer the call?");
                    // if (r == true) {
                    //     if (typeof videoChatboxUrl !== 'undefined') {
                    //         var url = videoChatboxUrl.replace(':id', child.id)
                    //         $.ajax({
                    //             url: url,
                    //             type: 'get',
                    //             dataType: 'json',
                    //             success: function(response) {
                    //                 if (response.status == 200) {
                    //                     globalModal.html(response.result.html);
                    //                     globalModal.modal('toggle');
                    //                     init_video(response.result);
                    //                     // init_dropzone_files(response.files);
                    //                 }
                    //             },
                    //             error: function() {}
                    //         })
                    //     }
                    // }
                }

                if (child.status === true && snapshot.val().data) {
                    var msg = snapshot.val().data ? snapshot.val().data : "";
                    var sender = snapshot.val().id;
                    var receiver = snapshot.key;

                    if (msg.ice != undefined) {
                        pc.addIceCandidate(new RTCIceCandidate(msg.ice)).catch(
                            e => {
                                console.log(
                                    "Failure during addIceCandidate(): " +
                                    e.name
                                );
                            }
                        );
                    } else if (
                        msg.sdp.type.toLowerCase() == "offer" &&
                        child.status === true
                    ) {
                        pc.setRemoteDescription(
                                new RTCSessionDescription(msg.sdp)
                            )
                            .then(() => pc.createAnswer())
                            .then(answer => pc.setLocalDescription(answer))
                            .then(() => {
                                let data = JSON.stringify({
                                    sdp: pc.localDescription
                                });
                                database
                                    .ref("calls/" + sender)
                                    .update({ data: JSON.parse(data) });
                                /* database.ref('calls/' + receiver).update({ 'status': true }); */
                                $(localWindow)
                                    .addClass("thumb")
                                    .draggable({ containment: "parent" });
                                $(remoteWindow).addClass("big");
                                $("#startVideo").hide();
                                $("#stopVideo").show();
                                $("#startAudio").hide();
                                $("#stopAudio").show();
                            });
                    } else if (msg.sdp.type.toLowerCase() == "answer") {
                        pc.setRemoteDescription(
                            new RTCSessionDescription(msg.sdp)
                        );
                        c(
                            "given answer by receiver: " +
                            receiver +
                            " and their sdp is:"
                        );
                        /* database.ref('calls/' + receiver).update({ 'status': true });
                            database.ref('users/' + sender).update({ 'call_status': 'active', 'available': true });
                            database.ref('users/' + receiver).update({ 'call_status': 'active', 'available': true }); */
                        $(localWindow)
                            .addClass("thumb")
                            .draggable({ containment: "parent" });
                        $(remoteWindow).addClass("big");
                        $("#startVideo").hide();
                        $("#stopVideo").show();
                        $("#startAudio").hide();
                        $("#stopAudio").show();
                    }
                }
            }
        });
    }
}

function syncSocketActivity(block = 1) {
    /* notification about the video consulat with doctor */
    if (block && typeof socket !== "undefined") {
        socket.on("notifyVideoConsult", function(res) {
            if (res.doctor_id == sender.id || res.patient_id == sender.id) {
                // c(res);
                if (res.doctor_id == sender.id) {
                    var videoChatUrl = videoConsultatScreen.replace(
                        ":id",
                        res.patient_id
                    );
                } else {
                    var videoChatUrl = videoConsultatScreen.replace(
                        ":id",
                        res.doctor_id
                    );
                }
                $.confirm({
                    title: "Video Call With " + res.doctor_name,
                    content: "<p>You can add content and not worry about the alignment. The goal is to make a Interactive dialog!.</p>",
                    buttons: {
                        someButton: {
                            text: "Open",
                            btnClass: "btn-green",
                            action: function() {
                                window.location.href = videoChatUrl;
                                return false; // prevent dialog from closing.
                            }
                        },
                        close: function() {
                            // lets the user close the modal.
                        }
                    }
                });
            }
        });
    }
}

var pc;
var localWindow, remoteWindow;
var localStream;
var receiver;

function init_video(data) {
    c("init Video call");
    receiver = data.receptorUser;
    localWindow = document.getElementById("localScreen");
    remoteWindow = document.getElementById("RemoteScreen");

    var mediaConstraints = {
        optional: [{RtpDataChannels: true}]
    };

    var servers = {
        iceServers: [
            { urls: "stun:stun.services.mozilla.com" },
            { urls: "stun:stun.l.google.com:19302" },
            { urls: "stun:stun.stunprotocol.org:3478"},
            { url: "stun:stun.services.mozilla.com" },
            { url: "stun:stun.l.google.com:19302" },
            { url: "stun:stun.stunprotocol.org:3478"}   
        ]
    };
    //offer SDP = [Session Description Protocol] tells other peers what you would like
var rtc_media_constraints = {
    mandatory: {
      OfferToReceiveAudio: true,
      OfferToReceiveVideo: true
    }
  };
  
  var rtc_peer_options = {
    optional: [
                {DtlsSrtpKeyAgreement: true}, //To make Chrome and Firefox to interoperate.
    ]
  }
    pc = new RTCPeerConnection(servers, mediaConstraints);

    pc.onicecandidate = event => {
        event.candidate ?
            storeCandidate(
                receiver.id,
                JSON.stringify({ ice: event.candidate })
            ) :
            console.log("Sent All Ice");
    };

    pc.onaddstream = event => {
        remoteWindow.srcObject = event.stream;
    };

    pc.addEventListener("iceconnectionstatechange", event => {
        c("event change");
        c(pc.iceConnectionState);
        if (pc.iceConnectionState === "failed") {
            pc.restartIce();
        }

        if (pc.iceConnectionState === "disconnected") {
            pc.close();
            pc = null;
            globalModal.html("");
            globalModal.modal("hide");
        }
    });

    navigator.mediaDevices
        .getUserMedia({ audio: true, video: true })
        .then(localMediaStream)
        .catch(function(err) {
            let error_msg = "";
            if (
                err.name == "NotFoundError" ||
                err.name == "DevicesNotFoundError"
            ) {
                error_msg = "Requested device not found";
            } else if (
                err.name == "NotReadableError" ||
                err.name == "TrackStartError"
            ) {
                error_msg = "webcam or mic are already in use ";
            } else if (
                err.name == "OverconstrainedError" ||
                err.name == "ConstraintNotSatisfiedError"
            ) {
                error_msg = "constraints can not be satisfied by avb. devices ";
            } else if (
                err.name == "NotAllowedError" ||
                err.name == "PermissionDeniedError"
            ) {
                error_msg = "permission denied in browser ";
            } else if (err.name == "TypeError" || err.name == "TypeError") {
                error_msg = "empty constraints object ";
            } else {
                error_msg = "Something went wrong";
            }
            toastrAlert("error", "Warning", error_msg);
        });

    /* Start: Initialize chat functions */
    openChat("#user_" + receiver.id, receiver.id);

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
                /*  let decryptText = CryptoJS.AES.decrypt(
                     res.messageText,
                     "%n&c!h*e!a^l@t(h~h)u%b$"
                 ).toString(CryptoJS.enc.Utf8);
                 let messageText = JSON.parse(decryptText).text ?
                     JSON.parse(decryptText).text :
                     ""; */
                let messageText = res.messageText;
                askForNotificationApproval(title, messageText);
            }
        }
    });

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
    /* End: Initialize chat functions */

    $(".rating_box").starRating({
        starSize: 20,
        useFullStars: true,
        readOnly: true
    });
    var totalSecond = data.totalSecond;
    var totalMinute = data.totalMinute * 60;
    var time = parseInt(totalSecond) + parseInt(totalMinute);
    c(time);
    c(totalMinute);
    //var time = 20;
    var countdown = document.getElementById("countdown");
    var minutes, seconds;
    var chatHome = "{{route('chat.index')}}";
    setInterval(function() {
        if (time == 15) {
            console.log(time);
            $("#countdown").addClass("text-danger blink_me");
        }
        if (time <= 0) {
            countdown.innerHTML = "Call End";
            setTimeout(function() {
                callHangup();
            }, 2000);
        } else {
            minutes = parseInt(time / 60);
            seconds = parseInt(time % 60);
            countdown.innerHTML = "" + minutes + " : " + seconds + "";
        }
        time -= 1;
    }, 1000);

    $(".btn2").hide();
    $(".btn4").hide();

    $(".btn1").click(function() {
        $(this).hide();
        $(".btn2").show();
    });
    $(".btn2").click(function() {
        $(this).hide();
        $(".btn1").show();
    });

    $(".btn3").click(function() {
        $(this).hide();
        $(".btn4").show();
    });
    $(".btn4").click(function() {
        $(this).hide();
        $(".btn3").show();
    });
}

function localMediaStream(stream) {
    localWindow.srcObject = stream;
    localStream = stream;
    pc.addStream(stream);
    database.ref("calls/" + my_id).update({ status: true });
    $document.find("#btnCall").hide();
    $document.find("#btnHangup").show();
}

function storeCandidate(receiverId, data) {
    let tempData = JSON.parse(data);
    c("Store Data: Get Ice");
    c(tempData.ice);
    if (typeof tempData.ice !== "undefined") {
        database.ref("calls/" + receiverId + "/data").update(tempData);
    }
}

function stopVideo() {
    localStream.getVideoTracks()[0].enabled = false;
}

function startVideo() {
    localStream.getVideoTracks()[0].enabled = true;
}

function stopAudio() {
    localStream.getAudioTracks()[0].enabled = false;
}

function startAudio() {
    localStream.getAudioTracks()[0].enabled = true;
}

function callHangup() {
    pc.close();
    pc = null;
    is_incoming_call = false;
    /* $(localWindow).removeClass('thumb').draggable("destroy")
    $(remoteWindow).html('').css({ 'display': 'none' });
    $document.find('#btnCall').show();
    $document.find('#btnHangup').hide(); */

    firebase
        .database()
        .ref("calls/" + my_id)
        .set({ status: false });
    firebase
        .database()
        .ref("calls/" + receiver.id)
        .set({ status: false });
    firebase
        .database()
        .ref("users/" + my_id)
        .update({ available: false });
    firebase
        .database()
        .ref("users/" + receiver.id)
        .update({ available: false });

    $(localWindow)
        .removeClass("thumb")
        .draggable("destroy");
    $(remoteWindow)
        .html("")
        .css({ display: "none" });
    globalModal.html("");
    globalModal.modal("hide");
    $document.find("#btnCall").show();
    $document.find("#btnHangup").hide();
    /* location.reload(); */
}

function incomingCall(child) {
    incomingCallPopup.show();
}

function incomingCallAction(action) {
    if (action === "accept") {
        if (typeof videoChatboxUrl !== "undefined" && caller_id) {
            var url = videoChatboxUrl.replace(":id", caller_id);
            $.ajax({
                url: url,
                type: "get",
                dataType: "json",
                success: function(response) {
                    if (response.status == 200) {
                        globalModal.html(response.result.html);
                        globalModal.modal("toggle");
                        init_video(response.result);
                        // init_dropzone_files(response.files);
                    }
                },
                error: function() {}
            });
        }
    }
    if (action === "reject") {
        database.ref("calls/" + my_id).set({ status: false });
        database.ref("users/" + my_id).update({ available: true });
        if (caller_id)
            database.ref("calls/" + caller_id).set({ status: false });
        database.ref("users/" + caller_id).update({ available: true });
    }
    incomingCallPopup.hide();
    return false;
}

/* Start: Chat functions */
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
                $("#right_chat_container").html(response.result.html);
                syncActiveUser();
                syncMessageCounter(receiver.id);
                loadHistory(response.result.chat.id);
                syncMessage(response.result.chat.id);

                if (is_enable) {
                    $("#right_chat_container")
                        .find(".chatbox-panel-close")
                        .remove();
                }
            }
        });
    }
}

function syncActiveUser() {
    let dbUserObject = firebase.database().ref("users");
    dbUserObject.on("value", snapshot => {
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
        });
    });
}

function syncMessageCounter(is_read = 0) {
    var messageCountRef = firebase
        .database()
        .ref("unreadCounter/user_" + sender.id);

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
                    if (sender.id == row.senderId) {
                        message_content += renderMessage(row, "outgoing");
                    } else {
                        message_content += renderMessage(row, "incoming");
                    }
                }
            });
        }

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
        let messageText = decryptText ? JSON.parse(decryptText).text : ""; */
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
        this.fileUrl = window.URL.createObjectURL(blob);

        const anchors = document.createElement("a");
        anchors.setAttribute("href", this.fileUrl);
        anchors.setAttribute("download", name);
        anchors.click();
    };
    xhr.open("GET", url);
    xhr.send();
}

function sendMessage(identity = "") {
    let message_content = "";
    let text = $document.find(".write_msg").val();
    if (text.replace(/\s/g, "").length > 0) {
        /*  let encryptText = CryptoJS.AES.encrypt(
             JSON.stringify({ text }),
             "%n&c!h*e!a^l@t(h~h)u%b$"
         ).toString(); */
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

        $.ajax({
            type: "POST",
            url: sendChatNotification, // This is what I have updated
            data: { receiver_id: receiver.id, sender_id: sender.id, type: 'chat', title: sender.name, text: text }
        }).done(function() {
            console.log('done');
        });
    }
}

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

function lazyLoading() {
    $(".lazy").Lazy();
}

function openfileDialog() {
    $("#image_field").click();
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


        if (extension != 'jpg' && extension != 'jpeg' && extension != 'png' && extension != 'pdf' && extension != 'doc' && extension != 'docx') {
            // alert('File size exceeds 2 MB');
            toastrAlert(
                "error",
                "",
                "File Formate Not Supported, please select a file jpg, jpeg, png, pdf, doc and docx"
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

            if (extension == 'jpg' || extension == 'jpeg' || extension == 'png') {
                icone_text = "\uD83D\uDCF7 Photo";
            } else {
                icone_text = "\uD83D\uDCC4 Document";
            }
            $.ajax({
                type: "POST",
                url: sendChatNotification, // This is what I have updated
                data: { receiver_id: receiver.id, sender_id: sender.id, type: 'chat', title: sender.name, text: icone_text }
            }).done(function() {
                console.log('done');
            });
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

function imagePopup() {
    document.addEventListener("DOMContentLoaded", function(event) {
        Chocolat(document.querySelectorAll(".chocolat-image"));
    });

    Chocolat(document.querySelectorAll(".chocolat-image"));
}

/* End: Chat functions */

function c(value, i = 0) {
    console.log(value);
    if (i == 1) {
        return false;
    }
}