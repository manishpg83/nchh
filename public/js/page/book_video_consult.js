$document = $(document);
var practice_id, consult_time;
var today = new Date();
var consult_date = today.getFullYear() + '-' + (today.getMonth() + 1).toString().padStart(2, "0") + '-' + today.getDate().toString().padStart(2, "0");
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    bookVideoConsultForm.validate({
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
            },
            patient_phone: {
                required: true,
            }
        },
        messages: {
            patient_email: { email: 'Enter a valid email address.' },
        },
        submitHandler: function(form) {
            var time = $(form).find('#time').val();
            var formData = new FormData($(form)[0]);
            formData.append('appointment_type', "ONLINE");
            formData.append('practice_id', practice_id);
            formData.append('date', consult_date);
            formData.append('time', consult_time);
            console.log(consult_date + ' == ' + consult_time + ' == ' + practice_id);
            /* return false; */
            if (typeof consult_time === "undefined") {
                toastrAlert('error', 'Book Appointment', 'Please choose schedule time.');
                return false;
            } else if (typeof practice_id === "undefined") {
                toastrAlert('error', 'Book Appointment', 'Please choose schedule time.');
                return false;
            }

            if (typeof orderCreateUrl !== "undefined") {
                $.ajax({
                    type: 'POST',
                    url: orderCreateUrl,
                    data: formData,
                    processData: false,
                    dataType: 'json',
                    contentType: false,
                    beforeSend: function() {
                        bookVideoConsultForm.find('.btn-submit').addClass('disable btn-progress');
                    },
                    success: function(res) {
                        if (typeof key_id !== "undefined" && res.result.payment.order_id !== "undefined") {
                            openCheckout(res.result);
                        }
                    },
                    error: function(res) {
                        toastrAlert('error', 'Book Appointment', res.message);
                    },
                    complete: function() {
                        bookVideoConsultForm.find('.btn-submit').removeClass('disable btn-progress')
                    }
                });
            }
            return false;
        }
    });

});

function openCheckout(data) {
    if (typeof options !== "undefined") {
        options.order_id = data.payment.order_id;
        options.prefill.name = data.patient_name;
        options.prefill.email = data.patient_email;
        options.prefill.contact = data.patient_phone;
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
                        //$document.find('.appointment_container').html(response.html).removeClass('padding');
                        location.href = response.url
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

        /* Allocate date and reset the time */
        consult_date = $(this).attr('data-value');
        consult_time = '';
        console.log(consult_date);

        $('.tab-pane').removeClass('active show');
        $(target).addClass('active show');
        $(target).fadeIn();
    })

    $document.find('.schedule_time > a').click(function() {
        /* On select the slot */
        practice_id = $(this).attr('data-id');
        consult_time = $(this).attr('data-value');
        $document.find('.schedule_time > a').removeClass('active');
        $(this).addClass('active');
    })
}