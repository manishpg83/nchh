$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof practice_id !== "undefined") {
        loadPracticeTiming(practice_id);
    }

    diagnosticsAppointmentForm.validate({
        rules: {
            patient_name: {
                required: true,
                normalizer: function(value) {
                    return $.trim(value);
                }
            },
            patient_email: {
                required: true,
                email: true
            }
        },
        messages: {
            patient_email: { email: "Enter a valid email address." }
        },
        submitHandler: function(form) {
            var time = $(form).find('#time').val();
            var action = $(form).attr("action");
            var formData = new FormData($(form)[0]);

            formData.append('appointment_type', "INPERSON");
            formData.append('practice_id', practice_id);
            if (!time) {
                toastrAlert('error', 'Book Appointment', 'Please choose schedule time.');
                return false;
            }

            var checkbox = document.querySelector(
                'input[name="services_ids[]"]:checked'
            );
            if (!checkbox) {
                toastrAlert("error", "Appointment", 'Please select at list one service');
                return false;
            }

            $.ajax({
                type: "POST",
                url: action,
                data: formData,
                processData: false,
                dataType: "json",
                contentType: false,
                beforeSend: function() {
                    diagnosticsAppointmentForm.find('.btn-submit').addClass('disable btn-progress');
                },
                success: function(res) {
                    if (typeof key_id !== "undefined" && res.result.payment.order_id !== "undefined") {
                        openCheckout(res.result);
                    }
                },
                error: function(res) {
                    toastrAlert('error', 'Book Appointment', res.responseJSON.message);
                },
                complete: function() {
                    diagnosticsAppointmentForm.find('.btn-submit').removeClass('disable btn-progress')
                }
            });
            return false;
        }
    });
});


function loadPracticeTiming(practice_id) {

    if (typeof load_practice_timing !== "undefined") {
        var doctor_id = $('#doctor_id').val();
        $.ajax({
            type: 'POST',
            url: load_practice_timing,
            data: { practice_id: practice_id || '' },
            beforeSend: function() {
                // setPasswordForm.find('.btn-submit').addClass('disabled btn-progress');
                // setPasswordForm.find('.close-button').addClass('disabled');
            },
            success: function(res) {
                practice_schedule_div.html('')
                practice_schedule_div.html(res.html)
                load_timeSlot();
            },
            error: function(res) {
                //
            },
            complete: function() {}
        });
    }

}

function openCheckout(data) {
    if (typeof options !== "undefined") {
        options.order_id = data.payment.order_id;
        options.description = "Book Appointment | NC Health Hub";
        options.handler = function(response) {
            if (typeof response.razorpay_payment_id !== "undefined" && typeof orderVerifyUrl !== "undefined") {
                response.auth_record = data;
                $.ajax({
                    headers: header,
                    url: orderVerifyUrl,
                    type: 'POST',
                    data: response,
                    dataType: 'JSON',
                    beforeSend: function() {
                        $("body").addClass("progress-loader");
                    },
                    success: function(response) {
                        location.href = response.url
                            //$document.find('.appointment_container').html(response.html).removeClass('padding');
                    },
                    error: function(res) {
                        toastrAlert('error', 'Book Appointment', res.message);
                    },
                    complete: function() {
                        $("body").addClass("progress-loader");
                    }
                });
            }
        };
    }
    var rzp1 = new Razorpay(options);
    rzp1.open();
}

function load_timeSlot() {
    $document.find('.nav-tabs').scrollTabs();
    $document.find('a[data-toggle="tab"]').on('click', function(e) {
        var target = $(this).attr('data-id');

        diagnosticsAppointmentForm.find('input[name=date]').val($(this).attr('data-value'))
        diagnosticsAppointmentForm.find('#time').val('');

        $('.tab-pane').removeClass('active show');
        $(target).addClass('active show');
        $(target).fadeIn();
    })

    $document.find('.schedule_time > a').click(function() {
        // console.log($(this).attr('data-value'));
        diagnosticsAppointmentForm.find('input[name=time]').val($(this).attr('data-value'))
        $document.find('.schedule_time > a').removeClass('active');
        $(this).addClass('active');
    })
}