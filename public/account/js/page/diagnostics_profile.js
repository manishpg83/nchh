$document = $(document);
var rules, validation_msg;
var documentForm = new FormData();
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    diagnosticsProfileModal.on("shown.bs.modal", function() {
        if ($("#identity_proof").length > 0) {
            $("#identity_proof").change(function(e) {
                readURL(this, "preview");
            });
        }

        if ($("#diagnostics_proof").length > 0) {
            $("#diagnostics_proof").change(function(e) {
                readURL(this, "diagnostics-preview");
            });
        }
    });
});

function loadDiagnosticsProfileVerificationModal() {
    if (typeof uploadDocumentFormURL !== "undefined") {
        $.ajax({
            url: uploadDocumentFormURL,
            type: "GET",
            beforeSend: function() {},
            success: function(res) {
                diagnosticsProfileModal.html(res.html);
                diagnosticsProfileModal.modal({
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
    diagnosticsProfileForm = $document.find("#diagnosticsProfileForm");
    //Jquery validation of form field
    diagnosticsProfileForm.validate({
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
                    diagnosticsProfileForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    diagnosticsProfileForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == 200) {
                        diagnosticsProfileModal.modal("toggle");
                        diagnosticsProfileForm.trigger("reset");
                        window.location.reload();
                    } else {
                        toastrAlert("error", "Document", data.message);
                    }
                },
                error: function(data) {},
                complete: function(data) {
                    diagnosticsProfileForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    diagnosticsProfileForm
                        .find(".close-button")
                        .removeClass("disabled");
                }
            });
        }
    });
}

function sendForDiagnosticsVerification() {
    if (typeof sendDiagnosticsVerificationUrl != undefined) {
        $.ajax({
            type: "POST",
            url: sendDiagnosticsVerificationUrl,
            data: { as_doctor: true },
            dataType: "json",
            beforeSend: function() {
                $("#diagnosticsProfileDocument").addClass("disabled btn-progress");
            },
            success: function(data) {
                if (data.status == 200) {
                    window.location.reload();
                } else {
                    toastrAlert("error", "Document", data.message);
                }
            },
            error: function(data) {},
            complete: function(data) {
                $("#diagnosticsProfileDocument").removeClass("disabled btn-progress");
            }
        });
    }
}