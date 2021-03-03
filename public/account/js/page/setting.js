$document = $(document);
$document.ready(function () {
    $.ajaxSetup({
        headers: header
    });
    init_set_password_form();
    init_change_password_form();
});

function init_set_password_form() {
    setPasswordForm = $document.find('#setPasswordForm');

    //Jquery validation of form field
    setPasswordForm.validate({
        rules: {
            password: {
                required: true,
                normalizer: function (value) {
                    return $.trim(value);
                },
                valid_password: true
            },
            confirm_password: {
                equalTo: "#password"
            }
        },
        submitHandler: function (form) {
            var action = $(form).attr('action');
            var formData = new FormData($(form)[0]);
            $.ajax({
                type: 'POST',
                url: action,
                data: formData,
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function () {
                    setPasswordForm.find('.btn-submit').addClass('disabled btn-progress');
                    setPasswordForm.find('.close-button').addClass('disabled');
                },
                success: function (data) {
                    if (data.status == 'success') {
                        setPasswordForm.trigger("reset");
                        toastrAlert('success', 'Setting', data.message)
                    }
                    // if (data.status == 'error') {
                    //     toastrAlert('error', 'Setting', data.message)
                    // }
                },
                error: function (res) {
                    // if (data.status == 'error') {
                    //     toastrAlert('error', 'Setting', data.message)
                    // }
                    if (res.responseJSON.message) {
                        FormChangePassword.showErrors({
                            "password": res.responseJSON.message
                        });
                    }

                    if (res.responseJSON.errors) {
                        FormChangePassword.showErrors({
                            "password": res.responseJSON.errors[0]
                        });
                    }
                }, complete: function () {
                    setPasswordForm.find('.btn-submit').removeClass('disabled btn-progress');
                    setPasswordForm.find('.close-button').removeClass('disabled');
                }
            });
        }
    });

}

function init_change_password_form() {
    changePasswordForm = $document.find('#changePasswordForm');

    //Jquery validation of form field
    var FormChangePassword = changePasswordForm.validate({
        rules: {
            old_password: {
                required: true
            },
            password: {
                required: true,
                normalizer: function (value) {
                    return $.trim(value);
                },
                valid_password: true
            },
            confirm_password: {
                equalTo: "#password"
            }
        },
        submitHandler: function (form) {
            var action = $(form).attr('action');
            var formData = new FormData($(form)[0]);
            $.ajax({
                type: 'POST',
                url: action,
                data: formData,
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function () {
                    changePasswordForm.find('.btn-submit').addClass('disabled btn-progress');
                    changePasswordForm.find('.close-button').addClass('disabled');
                },
                success: function (data) {
                    if (data.status == 'success') {
                        changePasswordForm.trigger("reset");
                        toastrAlert('success', 'Setting', data.message)
                    }
                    // if (data.status == 'error') {
                    //     toastrAlert('error', 'Setting', data.message)
                    //     FormChangePassword.showErrors({
                    //         "old_password": data.message
                    //     });
                    // }
                },
                error: function (res) {

                    if (res.responseJSON.message) {
                        FormChangePassword.showErrors({
                            "old_password": res.responseJSON.message
                        });
                    }

                    if (res.responseJSON.errors) {
                        FormChangePassword.showErrors({
                            "old_password": res.responseJSON.errors[0]
                        });
                    }

                }, complete: function () {
                    changePasswordForm.find('.btn-submit').removeClass('disabled btn-progress');
                    changePasswordForm.find('.close-button').removeClass('disabled');
                }
            });
        }
    });

}