$document = $(document);
var rules, validation_msg;
var documentForm = new FormData();
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    agentProfileModal.on("shown.bs.modal", function() {
        if ($("#identity_proof").length > 0) {
            $("#identity_proof").change(function(e) {
                //var fileName = e.target.files[0].name;
                // alert('The file "' + fileName + '" has been selected.');
                readURL(this, "preview");
            });
        }
    });
});

function loadAgentProfileVerificationModal() {
    if (typeof uploadDocumentFormURL !== "undefined") {
        $.ajax({
            url: uploadDocumentFormURL,
            type: "GET",
            beforeSend: function() {},
            success: function(res) {
                agentProfileModal.html(res.html);
                agentProfileModal.modal({
                    show: true,
                    backdrop: "static",
                    keyboard: false
                });
                init_documentForm();
            },
            error: function(res) {
                toastrAlert("error", "Profile", res.message);
            },
            complete: function() {}
        });
    }
}

function init_documentForm() {
    agentProfileForm = $document.find("#agentProfileForm");
    //Jquery validation of form field
    agentProfileForm.validate({
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
                    agentProfileForm.find(".btn-submit").addClass("disabled btn-progress");
                    agentProfileForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == 200) {
                        agentProfileModal.modal("toggle");
                        agentProfileForm.trigger("reset");
                        window.location.reload();
                    } else {
                        toastrAlert('error', 'Document', data.message)
                    }
                },
                error: function(data) {},
                complete: function(data) {
                    agentProfileForm.find(".btn-submit").removeClass("disabled btn-progress");
                    agentProfileForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}

function sendForAgentVerification() {
    if (typeof sendAgentVerificationUrl != undefined) {
        $.ajax({
            type: "POST",
            url: sendAgentVerificationUrl,
            data: { as_doctor: true },
            dataType: "json",
            beforeSend: function() {
                $("#agentProfileDocument").addClass("disabled btn-progress");
            },
            success: function(data) {
                if (data.status == 200) {
                    window.location.reload();
                } else {
                    toastrAlert('error', 'Document', data.message)
                }
            },
            error: function(data) {},
            complete: function(data) {
                $("#agentProfileDocument").removeClass("disabled btn-progress");
            }
        });
    }
}