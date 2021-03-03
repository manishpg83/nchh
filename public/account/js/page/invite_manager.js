$document = $(document);
$document.ready(function() {
    /*Pass csrf token for every ajax call*/
    $.ajaxSetup({ headers: header });

    if (typeof UserList !== "undefined" && $('#UserTable').length > 0) {
        UserTable = $('#UserTable').DataTable({
            // dom: "<'row'<'col-xs-12 col-lg-12't>><'row'<'col-lg-6'i><'col-lg-6'p>>",
            responsive: true,
            processing: true,
            serverSide: true,
            /*ajax: courseList,*/
            ajax: {
                url: UserList,
                data: function(d) {
                    // d.search = $('input[type=search]').val();
                }
            },
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'name', width: '15%' },
                { data: 'email', width: '10%', sortable: false, searchable: false },
                { data: 'phone', width: '10%', sortable: false, searchable: false },
                { data: 'locality' },
                { data: 'address' },
                { data: 'register_at' },
                { data: 'commission' },
                { data: 'action', orderable: false, searchable: false }
            ],
            initComplete: function() {
                init_tooltip();
            },
            drawCallback: function() {
                init_tooltip();
            }
        });
    }

    if ($('#InviteForm').length > 0) {
        init_inviteForm();
    }


});

function init_inviteForm() {

    var btn_submit = InviteForm.find('#btn_submit');
    InviteForm.validate({
        rules: {
            subject: {
                required: true,
                normalizer: function(value) {
                    return $.trim(value);
                }
            },
            recipient_email: {
                required: true,
                email: true,
                remote: {
                    url: is_email_exist_url,
                    headers: header,
                    type: "POST",
                    data: {
                        recipient_email: function() {
                            return $("input[name='recipient_email']").val();
                        }
                    },
                }
            },
            recipient_phone: {
                required: true,
                number: true,
                remote: {
                    url: is_phone_exist_url,
                    headers: header,
                    type: "POST",
                    data: {
                        recipient_phone: function() {
                            return $("input[name='recipient_phone']").val();
                        }
                    },
                }
            },
            content: {
                required: true
            }
        },
        messages: {
            recipient_email: { remote: "Email already registered." },
            recipient_phone: { remote: "Phone already registered." }
        },
        submitHandler: function(form) {
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
                    btn_submit.addClass('btn-progress disabled');
                },
                success: function(res) {
                    $(form).trigger("reset");
                    $(form).find('#recipient_email').val('');
                    toastrAlert('success', 'Invite User', res.message)
                },
                error: function(res) {
                    toastrAlert('error', 'Invite User', res.message, 'bottomCenter')
                },
                complete: function() {
                    btn_submit.removeClass('btn-progress disabled');
                }
            });
            return false;
        },
    });
}

function init_inviteFormBK() {

    var btn_submit = InviteForm.find('#btn_submit');
    InviteForm.validate({
        rules: {
            subject: {
                required: true,
                normalizer: function(value) {
                    return $.trim(value);
                }
            },
            multiple_emails: {
                required: true,
                remote: {
                    url: is_email_exist_url,
                    headers: header,
                    type: "POST",
                    data: {
                        recipient_emails: function() {
                            return $("input[name='recipient_emails']").val();
                        }
                    },
                }
            },
            content: {
                required: true
            }
        },
        messages: {
            multiple_emails: { remote: "Email already registered." }
        },
        submitHandler: function(form) {
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
                    btn_submit.addClass('btn-progress disabled');
                },
                success: function(res) {
                    $(form).trigger("reset");
                    $(form).find('#recipient_emails').val('');
                    $(form).find('.multiple_emails-ul').html('');
                    toastrAlert('success', 'Invite User', res.message)
                },
                error: function(res) {
                    toastrAlert('error', 'Medical Record', res.message, 'bottomCenter')
                },
                complete: function() {
                    btn_submit.removeClass('btn-progress disabled');
                }
            });
            return false;
        },
        errorPlacement: function errorPlacement(error, element) {
            if (element.attr("name") === "multiple_emails") {
                element.parent().after(error);
            } else {
                error.insertAfter(element);
            }
            // element.after(error); 
        }
    });

    $('#recipient_emails').multiple_emails({ position: "bottom" });
}

/* function reset_emails() {
    var emails = new Array();
    var container = $orig.siblings('.multiple_emails-container');
    $orig.val('').trigger('change');
} */