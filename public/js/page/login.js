$document = $(document);
$document.ready(function () {
    $.ajaxSetup({
        headers: header
    });
    init_tags();

    if (typeof formLogin !== "undefined") {
        // loginForm = $document.find('#loginForm');
        loginForm = formLogin.validate({
            rules: {
                email: {
                    remote: {
                        url: verify_detail_url,
                        headers: header,
                        type: "post",
                        data: {
                            email: function () {
                                return $("input[name='email']").val();
                            }
                        },
                    },
                    required: true,
                }
            },
            messages: {
                email: { remote: "This field value doesn't exists." }
            },
            submitHandler: function (form) {
                var action = ($("#otp_flow").is(":checked")) ? otpLogin : $(form).attr('action');
                var with_otp = ($("#otp_flow").is(":checked")) ? 1 : 0;
                var formData = new FormData($(form)[0]);
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: formData,
                    processData: false,
                    dataType: 'json',
                    contentType: false,
                    beforeSend: function () {
                        //
                    },
                    success: function (res) {
                        if (with_otp && res.status == 200) {
                            $document.find('#loginForm').remove();
                            $document.find('form#otpLoginForm').removeClass('d-none');
                            $document.find('.otp-message').html(res.message);
                            $(':input[type="submit"]').val('Login');
                            init_tags();
                            userOTPLogin();
                            console.log(res);
                        }

                        if (!with_otp) {
                            location.reload();
                        }
                    },
                    error: function (res) {
                        if (res.responseJSON.errors) {
                            // console.log(res.responseJSON.errors.email[0]);
                            loginForm.showErrors({
                                "email": res.responseJSON.errors.email[0]
                            });
                        }
                    },
                    complete: function () {
                        $('#pageLoader').removeClass('loading');
                    }
                });
            }
        });
    }

    $("#otp_flow").click(function () {
        if ($(this).is(":checked")) {
            // visible mobile field
            $('#email').attr('type', 'number');
            $('label[for="email"]:not(.error)').html('Phone');
            $(':input[type="password"]').prop('disabled', true);
            $(':input[type="submit"]').val('Send OTP');
            // intlPhoneField('email', loginForm);
            formLogin.find("#email").rules("remove");
            formLogin.find("#email").rules("add", {
                required: true,
                // valid_phone: true,
                remote: {
                    url: verify_detail_url,
                    headers: header,
                    type: "POST",
                    data: {
                        phone: function () {
                            return $("input[name='email']").val();
                        }
                    },
                },
                // messages: {
                //     required: "Phone is required.",
                //     remote: jQuery.validator.format("{0} doesn't exists.")
                // }
                messages: {
                    required: "Phone number is required",
                    remote: "Phone number doesn't exists.",
                }
            });
        } else {
            // visible email field
            $('#email').attr('type', 'text');
            $('label[for="email"]:not(.error)').html('Email Adderss OR Phone');
            $(':input[type="password"]').prop('disabled', false);
            $(':input[type="submit"]').val('Login');

            formLogin.find("#email").rules("remove");
            formLogin.find("#email").rules("add", {
                remote: {
                    url: verify_detail_url,
                    headers: header,
                    type: "post",
                    data: {
                        email: function () {
                            return $("input[name='email']").val();
                        }
                    },
                },
                required: true,
                messages: {
                    // required: "This field is required.",
                    remote: "This field value doesn't exists."
                }
            });
        }
        /* Reset the validation after add/remove the rule */
        loginForm.resetForm();
    });

});

//Login with google 
// Render Google Sign-in button
function renderButton() {
    gapi.signin2.render('gSignIn', {
        'scope': 'profile email',
        'width': 240,
        'height': 50,
        'longtitle': true,
        'theme': 'dark',
        'onsuccess': onSuccess,
        'onfailure': onFailure
    });
}

// Sign-in success callback
function onSuccess(googleUser) {
    // Get the Google profile data (basic)
    var profile = googleUser.getBasicProfile();
    if (typeof userData !== "undefined") {
        $.ajax({
            headers: header,
            url: userData,
            type: 'post',
            data: { 'data': profile, 'type': 'google' },
            beforeSend: function () {
                $('#pageLoader').addClass('loading');
            },
            success: function (res) {
                console.log(res);
                location.reload();
            },
            error: function () { },
            complete: function () {
                $('#pageLoader').removeClass('loading');
            }
        });
    }
}

// Sign-in failure callback
function onFailure(error) {
    alert(error);
}

//Login with Facebook

window.fbAsyncInit = function () {
    FB.init({
        appId: '549655569080144',
        xfbml: true,
        version: 'v7.0'
    });
    FB.AppEvents.logPageView();
};

(function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) { return; }
    js = d.createElement(s);
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


// Facebook login with JavaScript SDK
function fbLogin() {
    FB.login(function (response) {
        if (response.authResponse) {
            // Get and display the user profile data
            console.log(response);
            getFbUserData();
        } else {
            document.getElementById('status').innerHTML = 'User cancelled login or did not fully authorize.';
        }
    }, { scope: 'email' });
}

function getFbUserData() {
    FB.api('/me', { locale: 'en_US', fields: 'id,name,first_name,last_name,email,link,gender,locale,picture' },
        function (response) {
            //console.log(response);
            if (typeof userData !== "undefined") {
                $.ajax({
                    headers: header,
                    url: userData,
                    type: 'post',
                    data: { 'data': response, 'type': 'facebook' },
                    beforeSend: function () {
                        $('#pageLoader').addClass('loading');
                    },
                    success: function (res) {
                        console.log(res);
                        location.reload();
                    },
                    error: function () { },
                    complete: function () {
                        $('#pageLoader').removeClass('loading');
                    }
                });
            }
        });
}

$(function () {

    // var rules = {
    //     email: {
    //         remote: {
    //             url: email_verify,
    //             headers: header,
    //             type: "POST",
    //             data: { email: function () { return $("input[name='email']").val(); } },
    //         },
    //         required: true,
    //     }
    // }
});

function sendOTP() {
    if ($("input[name=otp_flow]").is(":checked")) {
        loginForm = $document.find('#loginForm');
        loginForm.validate({
            rules: {
                email: {
                    remote: {
                        url: phone_verify,
                        headers: header,
                        type: "post",
                        data: {
                            phone: function () {
                                return $("input[name='email']").val();
                            }
                        },
                    },
                    required: true,
                    number: true
                }
            },
            messages: {
                email: {
                    // required: "Please enter mobile number",
                    number: "Please enter valid mobile number",
                    remote: "This mobile number doesn't exists.",
                }
            },
            submitHandler: function (form) {
                var action = otpLogin;
                var formData = new FormData($(form)[0]);
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: formData,
                    processData: false,
                    dataType: 'json',
                    contentType: false,
                    beforeSend: function () {
                        //
                    },
                    success: function (res) {
                        if (res.status == 200) {
                            $document.find('#loginForm').remove();
                            $document.find('form#otpLoginForm').removeClass('d-none');
                            $document.find('.otp-message').html(res.message);
                            $(':input[type="submit"]').val('Login');
                            init_tags();
                            userOTPLogin();
                            console.log(res);
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function (res) { },
                    complete: function () {
                        $('#pageLoader').removeClass('loading');
                    }
                });
            }
        });
    } else {
        loginForm = $document.find('#loginForm');
        loginForm.validate({
            rules: {
                email: {
                    required: true,
                    remote: {
                        url: email_verify,
                        headers: header,
                        type: "post",
                        data: {
                            email: function () {
                                return $("input[name='email']").val();
                            }
                        },
                    }
                },
                password: "required"
            },
            messages: {
                email: {
                    required: "Please enter email/mobile number",
                    email: "Please enter valid email address.",
                    remote: "Email address doesn't exists.",
                },
                password: "Please enter correct password."
            },
            submitHandler: function (form) {
                var action = $(form).attr('action');
                var formData = new FormData($(form)[0]);
                $.ajax({
                    type: 'POST',
                    url: action,
                    headers: header,
                    data: formData,
                    processData: false,
                    dataType: 'json',
                    contentType: false,
                    beforeSend: function () {
                        $('#pageLoader').addClass('loading');
                    },
                    success: function (res) {
                        console.log(res);
                        location.reload();
                    },
                    error: function (res) { },
                    complete: function () {
                        $('#pageLoader').removeClass('loading');
                    }
                });
            }
        });
    }
}

function resendOtp() {
    if (typeof otpLogin !== 'undefined') {
        $.ajax({
            url: otpLogin,
            type: 'POST',
            dataType: 'json',
            data: { 'resend_otp': 'resend_otp' },
            beforeSend: function () {
                $('#pageLoader').addClass('loading');
            },
            success: function (res) {
                $document.find('.otp-message').html(res.message);
                userOTPLogin();
                console.log(res);
            },
            error: function () {
                console.log();
            },
            complete: function () {
                $('#pageLoader').removeClass('loading');
            }
        });
    }
}

function userOTPLogin() {
    otpLoginForm = $document.find('#otpLoginForm');
    otpLoginForm.validate({
        rules: {
            otp: {
                remote: {
                    url: verifiedOTPUrl,
                    headers: header,
                    type: "post",
                    data: {
                        otp: function () {
                            return $document.find("input[name='otp']").val();
                        }
                    },
                },
                required: true,
                number: true,
            }
        },
        messages: {
            otp: {
                remote: "You enter wrong OTP. Please try again!"
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
                    //
                },
                success: function (res) {
                    location.reload();
                },
                error: function (res) {
                    //
                },
                complete: function () {
                    //
                }
            });
        }
    });
}

function init_tags() {
    $('input').focus(function () {
        $(this).siblings('label').addClass('active');
    });
    $('input').blur(function () {

        if ($(this).val().length > 0) {
            $(this).siblings('label').addClass('active');
        } else {
            $(this).siblings('label').removeClass('active');
        }
    });
}