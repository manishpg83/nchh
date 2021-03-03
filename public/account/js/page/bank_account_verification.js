$document = $(document);
var rules, validation_msg;
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    accountVerificationModal.on("shown.bs.modal", function() {
        //
    });
});

function loadBankAccountVerificationModal() {
    if (typeof uploadBankDetailsFormURL !== "undefined") {
        $.ajax({
            url: uploadBankDetailsFormURL,
            type: "GET",
            beforeSend: function() {},
            success: function(res) {
                accountVerificationModal.html(res.html);
                accountVerificationModal.modal({
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
    accountVerificationForm = $document.find("#accountVerificationForm");
    //Jquery validation of form field
    accountVerificationForm.validate({
        rules: {
            bank_name: "required",
            account_number: {
                required: true,
                number: true
            },
            ifsc_code: "required",
            beneficiary_name: "required",
            confirm_account_number: {
                equalTo: "#account_number"
            }
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
                    accountVerificationForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    accountVerificationForm
                        .find(".close-button")
                        .addClass("disabled");
                },
                success: function(data) {
                    if (data.status == 200) {
                        accountVerificationModal.modal("toggle");
                        accountVerificationForm.trigger("reset");
                        window.location.reload();
                    } else {
                        toastrAlert("error", "Document", data.message);
                    }
                },
                error: function(data) {},
                complete: function(data) {
                    accountVerificationForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    accountVerificationForm
                        .find(".close-button")
                        .removeClass("disabled");
                }
            });
        }
    });
}

function viewBankDetail() {
    if (typeof viewBankDetailsURL !== "undefined") {
        $.ajax({
            url: viewBankDetailsURL,
            type: "GET",
            beforeSend: function() {},
            success: function(res) {
                accountVerificationModal.html(res.html);
                accountVerificationModal.modal({
                    show: true,
                    backdrop: "static",
                    keyboard: false
                });
            },
            error: function(res) {
                toastrAlert("error", "Profile", res.message);
            },
            complete: function() {}
        });
    }
}