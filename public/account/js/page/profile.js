$document = $(document);
var rules, validation_msg;
var documentForm = new FormData();
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    profileModal.on('shown.bs.modal', function(e) {

        if (documentForm.has('identity_document')) {
            documentForm.delete('identity_document'); //delete identity form data before mopdel open
        }
        if (documentForm.has('medical_document')) {
            documentForm.delete('medical_document'); //delete medical form data before mopdel open
        }

        if ($('#uploadIdentity').length) {
            Dropzone.autoDiscover = false;

            var identityDropzone = new Dropzone("#uploadIdentity", {
                autoProcessQueue: false,
                url: "#",
                acceptedFiles: ".png,.jpg,.jpeg",
                dictInvalidFileType: "You can't upload fild of this type.Only upload PNG, JPG, JPEG",
                maxFilesize: 100,
                /*MB*/
                addRemoveLinks: true,
                maxFiles: 1,
                dictDefaultMessage: 'Drop image here or click to Upload',
                accept: function(file, done) {
                    done();
                },
                error: function(file, message, xhr) {
                    if (xhr == null) this.removeFile(file);
                    toastrAlert('error', 'Image', message)
                },
                addedfiles: function(file) {
                    $(".dz-details").remove();
                    $(".dz-progress").remove();
                },
                init: function() {
                    identityDropzone = this;
                    $.get(getUser, function(user) {
                        if (user == null) {
                            return;
                        }
                        if (user.detail.identity_proof == null) {
                            return;
                        }
                        var mockFile = { name: user.detail.identity_proof_name, size: user.detail.identity_proof_size };

                        identityDropzone.emit("addedfile", mockFile);
                        identityDropzone.options.thumbnail.call(identityDropzone, mockFile, user.detail.identity_proof);
                        identityDropzone.emit("complete", mockFile);
                        identityDropzone.files.push(mockFile);
                    });
                    this.on("addedfile", function(file) {
                        documentForm.delete('identity_document');
                        documentForm.append('identity_document', file);
                        if (this.files.length > 1) {
                            this.removeFile(this.files[0]);
                        }
                    });
                }
            });
        }

        if ($('#uploadMedical').length) {
            var medicalDocDropzone = new Dropzone("#uploadMedical", {
                autoProcessQueue: false,
                url: "#",
                acceptedFiles: ".png,.jpg,.jpeg",
                dictInvalidFileType: "You can't upload fild of this type.Only upload PNG, JPG, JPEG",
                maxFilesize: 100,
                /*MB*/
                addRemoveLinks: false,
                maxFiles: 1,
                accept: function(file, done) {
                    done();
                },
                error: function(file, message, xhr) {
                    if (xhr == null) this.removeFile(file);
                    console.log('Error Find');
                    toastrAlert('error', 'Image', message)
                },
                addedfiles: function(file) {
                    $(".dz-details").remove();
                    $(".dz-progress").remove();
                },
                init: function() {

                    medicalDropzone = this;
                    $.get(getUser, function(user) {
                        if (user == null) { return; }
                        if (user.detail.medical_registration_proof == null) { return; }

                        var mockFile = { name: user.detail.medical_registration_proof_name, size: user.detail.medical_registration_proof_size };
                        medicalDropzone.emit("addedfile", mockFile);
                        medicalDropzone.options.thumbnail.call(medicalDropzone, mockFile, user.detail.medical_registration_proof);
                        medicalDropzone.emit("complete", mockFile);
                        identityDropzone.files.push(mockFile);
                    });
                    this.on("addedfile", function(file) {
                        documentForm.delete('medical_document');
                        documentForm.append('medical_document', file);
                        if (this.files.length > 1) {
                            this.removeFile(this.files[0]);
                        }
                    });
                }
            });
        }
    });

    profileModal.on('hidden.bs.modal', function() {
        $document.find('.primarybox').load(' .primarybox');
    });
});

function loadProfileDetailsModal() {
    if (typeof showProfileDetails_url !== "undefined") {
        $.ajax({
            url: showProfileDetails_url,
            type: 'GET',
            beforeSend: function() {},
            success: function(res) {
                profileModal.html(res.html);
                $document.find('#otpbox .message').html(res.message);
                profileModal.modal({ show: true, backdrop: 'static', keyboard: false });
                init_profileDetailStep();

            },
            error: function(res) {
                toastrAlert('error', 'Profile', res.message)
            },
            complete: function() {}
        });
    }
}

function viewdoctorProfile() {
    if (typeof showProfileDetails_url !== "undefined") {
        $.ajax({
            url: showProfileDetails_url,
            type: 'GET',
            data: {'type': 'approved-doctor'},
            beforeSend: function() {},
            success: function(res) {
                profileModal.html(res.html);
                $document.find('#otpbox .message').html(res.message);
                profileModal.modal({ show: true, backdrop: 'static', keyboard: false });
                init_profileDetailStep();

            },
            error: function(res) {
                toastrAlert('error', 'Profile', res.message)
            },
            complete: function() {}
        });
    }
}

function init_profileDetailStep() {

    var profileForm = $document.find("#profileForm").show();
    var finishButton = profileForm.find('a[href="#finish"]');
    profileForm.steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "slideLeft",
        onStepChanging: function(event, currentIndex, newIndex) {
            // Allways allow previous action even if the current form is not valid!
            if (currentIndex > newIndex) {
                return true;
            }
            // Forbid next action on "Warning" step if the user is to young
            if (newIndex === 3) {
                return false;
            }
            // Needed in some cases if the user went back (clean up)
            if (currentIndex < newIndex) {
                // To remove error styles
                profileForm.find(".body:eq(" + newIndex + ") label.error").remove();
                profileForm.find(".body:eq(" + newIndex + ") .error").removeClass("error");
            }
            profileForm.validate().settings.ignore = ":disabled,:hidden";
            return profileForm.valid();
        },
        onStepChanged: function(event, currentIndex, priorIndex) {
            // Used to skip the "Warning" step if the user is old enough.
            if (currentIndex === 2) {
                profileForm.steps("next");
            }
            // Used to skip the "Warning" step if the user is old enough and wants to the previous step.
            if (currentIndex === 2 && priorIndex === 3) {
                profileForm.steps("previous");
            }
        },
        onFinishing: function(event, currentIndex) {
            profileForm.validate().settings.ignore = ":disabled";
            return profileForm.valid();
        },
        onFinished: function(event, currentIndex) {
            var finishButton = $document.find('.actions a[href="#finish"]');
            var prevButton = $document.find('.actions a[href="#previous"]');
            var action = $(profileForm).attr('action');
            var formData = new FormData($(profileForm)[0]);
            formData.append('as_dedicated_profile', 1);
            $.ajax({
                type: 'POST',
                url: action,
                data: formData,
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function() {
                    finishButton.addClass('btn-progress disabled');
                    prevButton.addClass('disabled');
                },
                success: function(res) {
                    if (res.status == 200) {
                        profileModal.modal('hide');
                        $(profileForm).trigger("reset")
                        $('#profileDetail').html('Change');
                        toastrAlert('success', 'Profile', res.message)
                    } else {
                        toastrAlert('error', 'Profile', res.message, 'bottomCenter')
                    }
                },
                error: function(res) {
                    toastrAlert('error', 'Profile', res.message)
                },
                complete: function() {
                    finishButton.removeClass('btn-progress disabled');
                    prevButton.removeClass('disabled');
                }
            });
        }
    }).validate({
        errorPlacement: function errorPlacement(error, element) {
            if (element.attr("name") === "specialty_ids[]") {
                element.parent().find('.select2-container').after(error);
            } else {
                error.insertAfter(element);
            }
            // element.after(error); 
        }
    });

    $(".select2").select2({
        placeholder: "Select Specialty",
    });
}

function loadProfileVerificationModal() {

    if (typeof showProfileDocumentVerification_url !== "undefined") {
        $.ajax({
            url: showProfileDocumentVerification_url,
            type: 'GET',
            beforeSend: function() {},
            success: function(res) {
                profileModal.html(res.html);
                profileModal.modal({ show: true, backdrop: 'static', keyboard: false });
                init_documentForm();
            },
            error: function(res) {
                toastrAlert('error', 'Profile', res.message)
            },
            complete: function() {}
        });
    }
}

function viewdoctorverifieddocument() {

    if (typeof showProfileDocumentVerification_url !== "undefined") {
        $.ajax({
            url: showProfileDocumentVerification_url,
            type: 'GET',
            data: {type: 'approved-document'},
            beforeSend: function() {},
            success: function(res) {
                profileModal.html(res.html);
                profileModal.modal({ show: true, backdrop: 'static', keyboard: false });
            },
            error: function(res) {
                toastrAlert('error', 'Profile', res.message)
            },
            complete: function() {}
        });
    }
}

function init_documentForm() {

    var form = $document.find("#uploadDocumentForm").show();
    form.steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "slideLeft",
        onStepChanging: function(event, currentIndex, newIndex) {
            if (currentIndex == 0) {
                return documentForm.has('identity_document');
            }

            if (currentIndex == 1) {
                return documentForm.has('medical_document');
            }
            /*Needed in some cases if the user went back (clean up)*/
            if (currentIndex < newIndex) {
                /*To remove error styles*/
                form.find(".body:eq(" + newIndex + ") label.error").remove();
                form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
            }
            form.validate().settings.ignore = ":disabled,:hidden";
            return form.valid();
        },
        onStepChanged: function(event, currentIndex, priorIndex) {
            /*Used to skip the "Warning" step if the user is old enough.*/
            if (currentIndex === 1) {
                form.steps("next");
            }
            /*Used to skip the "Warning" step if the user is old enough and wants to the previous step.*/
            if (currentIndex === 1 && priorIndex === 2) {
                form.steps("previous");
            }
        },
        onFinishing: function(event, currentIndex) {
            if (currentIndex == 1) {
                return documentForm.has('medical_document');
            }
            form.validate().settings.ignore = ":disabled";
            return form.valid();
        },
        onFinished: function(event, currentIndex) {
            var prevButton = $document.find('.actions a[href="#previous"]');
            var finishButton = $document.find('.actions a[href="#finish"]');
            if (typeof storeProfileDocumentVerification_url !== "undefined") {
                $.ajax({
                    type: 'POST',
                    url: storeProfileDocumentVerification_url,
                    data: documentForm,
                    processData: false,
                    dataType: 'json',
                    contentType: false,
                    beforeSend: function() {
                        finishButton.addClass('btn-progress disabled');
                        prevButton.addClass('disabled');
                    },
                    success: function(res) {
                        if (res.status == 200) {
                            profileModal.modal('hide');
                            $(form).trigger("reset")
                            $('#profileDocument').html('Change');
                            toastrAlert('success', 'Profile', res.message)
                        } else {
                            toastrAlert('error', 'Profile', res.message, 'bottomCenter')
                        }
                    },
                    error: function(res) {
                        toastrAlert('error', 'Profile', res.message)
                    },
                    complete: function() {
                        finishButton.removeClass('btn-progress disabled');
                        prevButton.removeClass('disabled');
                    }
                });
            }
        }
    }).validate({
        errorPlacement: function errorPlacement(error, element) { element.before(error); },
        rules: { identity_document: true, medical_document: true }
    });
}

function loadEstablishmentModal(button) {
    if (typeof showProfileEstablishment_url !== "undefined") {
        $.ajax({
            url: showProfileEstablishment_url,
            type: 'GET',
            beforeSend: function() {
                $(button).addClass('btn-progress disabled')
            },
            success: function(res) {
                profileModal.html(res.html);
                profileModal.modal({ show: true, backdrop: 'static', keyboard: false });
                init_establishmentStep(res.result);
            },
            error: function(res) {
                toastrAlert('error', 'Profile', res.message)
            },
            complete: function() {
                $(button).removeClass('btn-progress disabled')
            }
        });
    }
}

function init_establishmentStep(request) {
    var EstablishmentForm = $document.find("#establishmentForm").show();
    var finishButton = $document.find('.actions a[href="#finish"]');
    var prevButton = $document.find('.actions a[href="#previous"]');
    var parseValue = [];

    EstablishmentForm.steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "slideLeft",
        onStepChanging: function(event, currentIndex, newIndex) {

            if (currentIndex == 0) {
                var formData = new FormData($(EstablishmentForm)[0]);
                if (typeof storeEstablishmentDetails_url !== "undefined" && EstablishmentForm.valid()) {
                    $.ajax({
                        type: 'POST',
                        url: storeEstablishmentDetails_url,
                        data: formData,
                        processData: false,
                        dataType: 'json',
                        contentType: false,
                        beforeSend: function() {
                            finishButton.addClass('btn-progress disabled');
                            prevButton.addClass('disabled');
                        },
                        success: function(res) {
                            toastrAlert('success', 'Establishment Details', res.message)
                        },
                        error: function() {
                            toastrAlert('error', 'Establishment Details', res.message)
                        },
                        complete: function() {
                            finishButton.removeClass('btn-progress disabled');
                            prevButton.removeClass('disabled');
                        }
                    });
                } else {
                    return false;
                }
            }

            /*Needed in some cases if the user went back (clean up)*/
            if (currentIndex < newIndex) {
                /*To remove error styles*/
                EstablishmentForm.find(".body:eq(" + newIndex + ") label.error").remove();
                EstablishmentForm.find(".body:eq(" + newIndex + ") .error").removeClass("error");
            }
            // EstablishmentForm.validate().settings.ignore = ":disabled,:hidden";
            return true;
        },
        onStepChanged: function(event, currentIndex, priorIndex) {

            /*Used to skip the "Warning" step if the user is old enough.*/
            if (currentIndex === 1) {
                EstablishmentForm.steps("next");
            }
            /*Used to skip the "Warning" step if the user is old enough and wants to the previous step.*/
            if (currentIndex === 1 && priorIndex === 2) {
                EstablishmentForm.steps("previous");
            }
        },
        onFinished: function(event, currentIndex) {
            var get_timing = $('#timing_chart').jqs('export');
            console.log(get_timing);
            if (typeof storeEstablishmentTimings_url !== "undefined") {
                $.ajax({
                    type: 'POST',
                    url: storeEstablishmentTimings_url,
                    data: { schedule: get_timing },
                    beforeSend: function() {
                        finishButton.addClass('btn-progress disabled');
                        prevButton.addClass('disabled');
                    },
                    success: function(res) {
                        profileModal.modal('hide');
                        $(EstablishmentForm).trigger("reset")
                        $document.find('.establishment_div').load(' .establishment_div');
                        toastrAlert('success', 'Establishment Timing', res.message)
                    },
                    error: function() {
                        toastrAlert('error', 'Establishment Details', res.message)
                    },
                    complete: function() {
                        finishButton.removeClass('btn-progress disabled');
                        prevButton.removeClass('disabled');
                    }
                });
            }
        }
    })

    if (typeof request.user.timing.schedule !== "undefined") {
        parseValue = JSON.parse(request.user.timing.schedule);
        // var parseValue = JSON.parse(request.user.timing.schedule.toString());
        // $('#timing_chart').jqs('import', parseValue);
    }

    $('#timing_chart').jqs({
        mode: 'edit',
        hour: 24,
        days: 7,
        periodDuration: 60,
        data: parseValue,
        periodOptions: false,
        periodColors: [],
        periodTitle: '',
        periodBackgroundColor: 'rgba(82, 155, 255, 0.5)',
        periodBorderColor: '#2a3cff',
        periodTextColor: '#000',
        periodRemoveButton: 'Remove',
        // periodDuplicateButton: 'Duplicate',
        periodTitlePlaceholder: 'Title',
        daysList: [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ],
        onInit: function() {},
        onAddPeriod: function(period, jqs) {
            console.log(period);
        },
        onRemovePeriod: function() {},
        onDuplicatePeriod: function() {},
        onClickPeriod: function() {}
    });

    $('#timing_chart').slimscroll({
        // alwaysVisible: true,
        height: 250
    });

    // if (typeof request.user.timing !== "undefined") {
    //     var parseValue = JSON.parse(request.user.timing.schedule);
    //     // var parseValue = JSON.parse(request.user.timing.schedule.toString());
    //     $('#timing_chart').jqs('import', parseValue);
    // }

    // if (typeof userTimings !== "undefined" && userTimings.length > 0) {
    //     var parseValue = JSON.parse(userTimings.toString());
    //     $('#timing_chart').jqs('import', parseValue);
    // }

    initMap();
}

/* Start: Map Pin Location */
function initMap() {

    var myLatlng = { lat: -25.363, lng: 131.044 };

    var map = new google.maps.Map(document.getElementById('map_canvas'), { zoom: 12, center: myLatlng });

    addMarker(myLatlng, 'Default Marker', map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            map.setCenter(pos);
            addMarker(pos, 'Current Location', map);
        }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
        });
    } else {
        // Browser doesn't support Geolocation
        handleLocationError(false, infoWindow, map.getCenter());
    }
}

function handleEvent(event) {
    document.getElementById('establishment_latitude').value = event.latLng.lat();
    document.getElementById('establishment_longitude').value = event.latLng.lng();
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
/* End: Map Pin Location */

function openRejectionReason(reason) {
    $('.rejection_reason').html('');
    $('#showRejectionReson').modal('toggle');
    $('.rejection_reason').html(reason);
}