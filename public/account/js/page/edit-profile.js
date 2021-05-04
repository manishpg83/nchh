$document = $(document);
var rules, validation_msg, galleriesFileDropzone;
$document.ready(function() {

    $('input[name="weight"]').numeric({
        allowMinus: false,
        allowThouSep: false
    });

    $.ajaxSetup({
        headers: header
    });

    rules = {
        profile_picture: {
            required: true
        },
        phone: {
            required: true,
            number: true,
            min: 10
        },
        email: {
            required: true,
            email: true
        },
        timezone: {
            required: true
        },
        name: {
            required: true
        },
        address: {
            required: true,
        },
        locality: {
            required: true
        },
        city: {
            required: true
        },
        state: {
            required: true
        },
        country: {
            required: true
        },
        pincode: {
            required: true,
            zipcode: true
        },
        "detail[gst_in]": {
            gst: true
        },
        "detail[url]": {
            url: true
        },
        "specialty_ids[]": {
            required: true
        },
        "services[]": {
            required: true
        },
        "detail[bed]": {
            number: true
        }
    };

    validation_msg = {
        profile_picture: {
            required: "Please upload a profile picture."
        },
        phone: {
            required: "Please enter your phone number",
            number: "Please enter valid phone number.",
            min: "Please enter valid phone number."
        },
        email: {
            required: "Please enter your email address",
            email: "Please enter valid email address."
        },
        "specialty_ids[]": {
            required: "Please select specialty"
        },
        "services[]": {
            required: "Please select services"
        },
        "detail[bed]": {
            number: "Please enter only number."
        }
    };

    $(".select2").select2();

    initMap();

    userModal.on("hidden.bs.modal", function() {
        $document.find(".is-textbox").show();
        $document.find(".is-edit").hide();
    });

    if ($("#files").length) {

        galleriesFileDropzone = new Dropzone("#files", {
            autoProcessQueue: false,
            url: "#",
            acceptedFiles: ".png,.jpg,.jpeg",
            dictInvalidFileType: "You can't upload fild of this type.Only upload PNG, JPG, JPEG",
            maxFilesize: 100,
            /*MB*/
            addRemoveLinks: true,
            maxFiles: 5,
            accept: function(file, done) {
                done();
            },
            error: function(file, message, xhr) {
                if (xhr == null) this.removeFile(file);
                toastrAlert("error", "Image", message);
            },
            addedfiles: function(file) {
                $(".dz-details").remove();
                $(".dz-progress").remove();
            },
            init: function() {

                if (typeof getUserGalleryDetails !== "undefined") {
                    $.get(getUserGalleryDetails, function(user_gallery) {
                        user_gallery.forEach(file => {
                            console.log(file);
                            if (file == null) {
                                return;
                            }
                            if (file.image == null) {
                                return;
                            }

                            var mockFile = {
                                id: file.id,
                                name: file.file_name,
                                size: file.file_size
                            };
                            galleriesFileDropzone.emit("addedfile", mockFile);
                            galleriesFileDropzone.options.thumbnail.call(
                                galleriesFileDropzone,
                                mockFile,
                                file.image
                            );
                            galleriesFileDropzone.emit("complete", mockFile);
                            galleriesFileDropzone.files.push(mockFile);
                        });
                    });
                }

                this.on("addedfile", function(file) {});
                this.on("removedfile", function(file) {
                    console.log(file);
                    if (typeof deleteUserGalleryFileUrl !== "undefined") {
                        url = deleteUserGalleryFileUrl.replace(
                            ":slug",
                            file.id
                        );
                        $.ajax({
                            type: "DELETE",
                            url: url,
                            data: file,
                            processData: false,
                            dataType: "json",
                            contentType: false,
                            beforeSend: function() {},
                            success: function(res) {
                                //toastrAlert('success', 'user Gallery', res.message)
                            },
                            error: function(res) {
                                toastrAlert(
                                    "error",
                                    "user Gallery",
                                    res.message,
                                    "bottomCenter"
                                );
                            },
                            complete: function() {}
                        });
                        return false;
                    }
                });
            }
        });

        galleriesFileDropzone.on("removedfile", function(file) {});
    }
});

let time = 0;
$('input[name="address"]').on('keydown', function() {
    clearTimeout(time);

    var val = $(this).val();
    time = setTimeout(function() {
        changeMapMarker(val);
    }, 500);
});

function browsePicture() {
    fileupload.click();
    fileupload.change(function() {
        readURL(this, "previewPicture");
    });
}

function removePicture(id = "", btn_id) {
    if (typeof remove_picture_url !== "undefined") {
        if (id) {}
        var url = remove_picture_url.replace(":slug", id);
        $.ajax({
            type: "GET",
            url: url,
            processData: false,
            dataType: "json",
            contentType: false,
            beforeSend: function() {
                $(btn_id).addClass("btn-progress disabled");
            },
            success: function(res) {
                if (
                    res.status === "success" &&
                    typeof header_profile_icon !== "undefined"
                ) {
                    filepreview.attr("src", res.result.profile_picture);
                    header_profile_icon.attr("src", res.result.profile_picture);
                }
                toastrAlert("success", "Profile", res.message);
            },
            error: function(res) {
                toastrAlert("error", "Profile", res.message);
            },
            complete: function() {
                $(btn_id).removeClass("btn-progress disabled");
                $(".profile-widget-item-value").load(
                    " .profile-widget-item-value"
                );
                window.location.reload();
            }
        });
    }
}

function viewTextbox(identity) {
    var fieldname = $(identity).attr("data-field");
    var field = $("input[name=" + fieldname + "]");
    $(identity)
        .parent()
        .find(".is-textbox")
        .hide();
    $(identity)
        .parent()
        .find(".is-edit")
        .show();
    field.removeAttr("disabled");
    //$document.find(".submitProfile").attr("disabled", true);
}

function submitProfileForm(btn_id) {
    userProfile = $document.find("#userProfile");
    userProfile.validate({
        rules: rules,
        ignore: [],
        messages: validation_msg,
        submitHandler: function(form) {
            var lati = $(form).find('#latitude').val();
            var action = $(form).attr("action");
            var formData = new FormData($(form)[0]);
            formData.delete('email');

            if (lati == 0.0) {
                toastrAlert("error", "Location", "Please Select your location on google map!");
                return false;
            }

            if (typeof galleriesFileDropzone !== "undefined") {
                images = [];
                for (var i = 0; i < galleriesFileDropzone.files.length; i++) {
                    images.push(galleriesFileDropzone.files[i]);
                    formData.append("file[]", galleriesFileDropzone.files[i]);
                }
            }
            $.ajax({
                type: "POST",
                url: action,
                data: formData,
                processData: false,
                dataType: "json",
                contentType: false,
                beforeSend: function() {
                    btn_id.addClass("btn-progress disabled");
                },
                success: function(res) {
                    if (
                        res.status === "success" &&
                        typeof header_profile_icon !== "undefined"
                    ) {
                        header_profile_icon.attr(
                            "src",
                            res.result.profile_picture
                        );
                    }
                    toastrAlert("success", "Profile", res.message);
                    window.location.reload();
                },
                error: function(res) {
                    toastrAlert("error", "Profile", res.message || "Something want wrong!");
                },
                complete: function() {
                    btn_id.removeClass("btn-progress disabled");
                    $(".profile-widget-item-value").load(
                        " .profile-widget-item-value"
                    );
                }
            });
        },
        errorPlacement: function(error, element) {
            if ($(element).attr('id') == 'profile_picture') {
                $('.profile-widget-header').append(error);
            } else {
                error.insertAfter(element);
            }
        }
    });
}

function sendOTP(btn_id) {
    var fieldname = $(btn_id).attr("data-field");
    var fieldvalue = $("input[name=" + fieldname + "]").val();
    userProfile = $document.find("#userProfile");
    userProfile.validate({
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        messages: validation_msg
    });

    if (userProfile.valid()) {
        if (typeof sendOtp_url !== "undefined") {
            $.ajax({
                url: sendOtp_url,
                type: "POST",
                data: { field: fieldname, value: fieldvalue },
                beforeSend: function() {
                    $(btn_id).addClass("btn-progress disabled");
                },
                success: function(res) {
                    userModal.html(res.html);
                    $document.find("#otpbox .message").html(res.message);
                    userModal.modal({
                        show: true,
                        backdrop: "static",
                        keyboard: false
                    });
                },
                error: function(res) {
                    toastrAlert("error", "Profile", res.message);
                },
                complete: function() {
                    $(btn_id).removeClass("btn-progress disabled");
                }
            });
        }
    }
}

function verifyOTP(btn_id) {
    otp_verify_form = $("#otpVerificationForm").validate({
        rules: {
            otp: { required: true, digits: true }
        },
        success: function(label) {
            label.prev().removeClass("error");
            label.remove();
        },
        invalidHandler: function() {
            // form, validator
            // Empty body
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
                    btn_id.addClass("btn-progress disabled");
                },
                success: function(res) {
                    if (res.status == "success") {
                        userModal.modal("hide");
                        $(form).trigger("reset");
                        toastrAlert("success", "Verification", res.message);
                    } else {
                        toastrAlert(
                            "error",
                            "Verification",
                            res.message,
                            "bottomCenter"
                        );
                    }
                },
                error: function(res) {
                    toastrAlert(
                        "error",
                        "Verification",
                        res.message,
                        "bottomCenter"
                    );
                    //
                },
                complete: function() {
                    btn_id.removeClass("btn-progress disabled");
                    $document.find(".is-textbox").html('Edit');
                    window.location.reload();
                }
            });
        }
    });
}

function resendOtp() {
    if (typeof sendOtp_url !== "undefined") {
        var verificationForm = $document.find("#otpVerificationForm");
        var formData = new FormData($(verificationForm)[0]);
        formData.append("resend_otp", "1");
        $.ajax({
            url: sendOtp_url,
            type: "POST",
            data: formData,
            processData: false,
            dataType: "json",
            contentType: false,
            success: function(res) {
                $document.find("#otpbox .message").html(res.message);
            }
        });
    }
}

function changeFieldValue(btn_id) {
    var fieldname = $(btn_id).attr("data-field");
    var fieldvalue = $("input[name=" + fieldname + "]").val();
    if (typeof changefield_url !== "undefined") {
        $.ajax({
            url: changefield_url,
            type: "post",
            dataType: "json",
            data: { field: fieldname, value: fieldvalue },
            success: function(res) {
                if (res.status == "success") {
                    $document
                        .find(".other_information_card")
                        .load(" .other_information_card");
                    toastrAlert("success", "Profile", res.message);
                } else {
                    toastrAlert("error", "Profile", res.message);
                }
            },
            error: function() {
                toastrAlert("error", "Profile", res.message);
            }
        });
    }
}


/* Start: Map Pin Location */
var infoMap;
function initMap() {
    var myLatlng = { lat: lati, lng: long };
    var map = new google.maps.Map(document.getElementById('map_canvas'), { zoom: 12, center: myLatlng });

    addMarker(myLatlng, 'Default Marker', map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            map.setCenter(pos);
            if (lati == 0 && lati == 0) {
                addMarker(pos, 'Current Location', map);
            }
        }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
        });
    } else {
        // Browser doesn't support Geolocation
        handleLocationError(false, infoWindow, map.getCenter());
    }
}

function handleEvent(event) {
    document.getElementById('latitude').value = event.latLng.lat();
    document.getElementById('longitude').value = event.latLng.lng();

    $latitude = event.latLng.lat();
    $longitude = event.latLng.lng();
    
    getAddress($latitude,$longitude);
}

function addMarker(latlng, title, map) {
    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title: title,
        draggable: true
    });

    marker.addListener('drag', handleEvent);
    marker.addListener('dragend', handleEvent);
}

function getAddress(latitude,longitude) {

    LocationUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng="+ latitude +","+ longitude +"&key="+ googleMapApi +"";
               
    $.ajax({
        url: LocationUrl, 
        type: "GET",   
        beforeSend: function(jqXHR, settings) {
            delete $.ajaxSettings.headers["X-CSRF-TOKEN"];
        },
        cache: false,
        success: function(response){                          
            let data = response.results[0].address_components;
            let userAddress = data[0].long_name +  ', ' + data[1].long_name + ', ' + data[2].long_name;
            $('input[name="address"]').val(userAddress) 
        }           
    });
}

function changeMapMarker(val) {

    var myLatlng = { lat: lati, lng: long };
    var map = new google.maps.Map(document.getElementById('map_canvas'), { zoom: 12, center: myLatlng });
    const geocoder = new google.maps.Geocoder();
    if(val != '') {
        geocoder.geocode({ address: val }, (results, status) => {
            if (status === "OK") {
                map.setCenter(results[0].geometry.location);
                addMarker(results[0].geometry.location, val, map);
            } else {
                alert('Address not found');
            }
          });
        
    }
}

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(
      browserHasGeolocation
        ? "Error: The Geolocation service failed."
        : "Error: Your browser doesn't support geolocation."
    );
    infoWindow.open(map);
}

/* End: Map Pin Location */