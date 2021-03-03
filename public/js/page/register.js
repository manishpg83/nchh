// var iti;
$document = $(document);
$document.ready(function() {
    $.ajaxSetup({ headers: header });
    init_tags();
    intlPhoneField('phone', formUser);

    $.validator.setDefaults({
        /*errorClass: 'errorField', errorElement: 'div',*/
        errorPlacement: function(error, element) {
            if (element.attr("name") === "phone") {
                element.parent().after(error);
                /*$("label.error_agree_to_receive").after(error);*/
            } else {
                error.insertAfter(element);
            }
        }
    });

    $(function() {
        // guess user timezone 
        $('#tz').val(moment.tz.zone())
    })

});

function requestOTP() {
    userForm = $document.find('#userForm');
    if (typeof formUser !== "undefined" && typeof is_phone_exist_url !== "undefined") {
        userForm = formUser.validate({
            rules: {
                role_id: "required",
                name: {
                    required: true,
                    normalizer: function(value) {
                        return $.trim(value);
                    },
                },
                phone: {
                    required: true,
                    valid_phone: true,
                    remote: {
                        url: is_phone_exist_url,
                        headers: header,
                        type: "POST",
                        data: {
                            phone: function() {
                                return $("input[name='phone']").val();
                            }
                        },
                    }
                },
                password: {
                    required: true,
                    normalizer: function(value) {
                        return $.trim(value);
                    },
                    valid_password: true
                }

            },
            messages: {
                role_id: "Please select any one..",
                phone: { remote: "Phone number already exists.", }
            },
            submitHandler: function(form) {
                // console.log(iti.getSelectedCountryData().dialCode);
                $(form).find('input[name=dialcode]').val(iti.getSelectedCountryData().dialCode);
                var action = $(form).attr('action');
                var formData = new FormData($(form)[0]);
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: formData,
                    processData: false,
                    dataType: 'json',
                    contentType: false,
                    beforeSend: function() {
                        //
                    },
                    success: function(res) {
                        if (res.status == 200) {
                            $document.find('#userForm').remove();
                            $document.find('form#otpRegisterForm').removeClass('d-none');
                            $document.find('.otp-message').html(res.message);
                            $document.find('input#otp').val(res.otp.MOBILE_OTP);
                            $(':input[type="submit"]').val('Register');
                            userRegister();
                            init_tags();
                        } else {
                            //
                        }

                    },
                    error: function(res) {},
                    complete: function() {
                        //
                    }
                });
            }
        });
    }
    return false;
}

function userRegister() {
    userRegisterForm = $document.find('#otpRegisterForm');
    userRegisterForm.validate({
        rules: {
            otp: {
                remote: {
                    url: otp_verify_url,
                    headers: header,
                    type: "POST",
                    data: {
                        otp: function() { return $document.find("input[name='otp']").val() }
                    },
                },
                required: true,
                number: true,
            }
        },
        messages: {
            otp: { remote: "You enter wrong OTP. Please try again!" }
        },
        submitHandler: function(form) {
            // form.submit();
            var action = $(form).attr('action');
            var formData = new FormData(formUser[0]);
            $.ajax({
                type: 'POST',
                url: action,
                data: formData,
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function() {
                    //
                },
                success: function(res) {
                    window.location.href = goto_home;
                },
                error: function(res) {
                    // location.reload();
                },
                complete: function() {
                    //
                }
            });
        }
    });
    return false;
}

function resendOtp() {
    if (typeof resendOTP !== 'undefined') {
        $.ajax({
            url: resendOTP,
            type: 'POST',
            dataType: 'json',
            data: { 'resend_otp': 'resend_otp' },
            beforeSend: function() {
                //
            },
            success: function(res) {
                $document.find('.otp-message').html(res.message);
                userRegister();
                console.log(res);
            },
            error: function() {
                console.log();
            },
            complete: function() {
                $('#pageLoader').removeClass('loading');
            }
        });
    }
}

function init_tags() {
    $('input').focus(function() {
        $(this).siblings('label').addClass('active');
    });
    $('input').blur(function() {

        if ($(this).val().length > 0) {
            $(this).siblings('label').addClass('active');
        } else {
            $(this).siblings('label').removeClass('active');
        }
    });
}