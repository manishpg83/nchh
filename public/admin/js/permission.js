$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });
    init_permission_form();
});


$document.find('select[name="role_id"]').change(function() {
    var role_id = $(this).val();
    if (typeof loadRoutesList !== "undefined" && role_id) {
        $.ajax({
            headers: header,
            url: loadRoutesList + '?role_id=' + role_id,
            type: 'GET',
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('.alert-danger').html('');
            },
            success: function(response) {
                if (response.status == 'success') {
                    container.html(response.html);
                    init_permission_form();
                } else {
                    toastrAlert('error', 'Permission', response.message, 'bottomCenter')
                }
            },
            error: function(error) {
                toastrAlert('error', 'Permission', 'Something went wrong.')
            },
            complete: function() {
                /*empty*/
            }
        });
    }
});

function init_permission_form() {

    var permissionForm = $document.find('form#permissionForm');
    var button = $document.find('form#permissionForm').find('button[type=submit]');
    permissionForm.validate({
        submitHandler: function(form) {
            var url = $(form).attr('action');
            $.ajax({
                headers: header,
                url: url,
                type: 'post',
                data: new FormData($(form)[0]),
                processData: false,
                contentType: false,
                beforeSend: function() {
                    button.addClass("disabled btn-progress");
                    button.addClass("disabled");

                },
                success: function(response) {
                    if (response.status === "success") {
                        toastrAlert('success', 'Set Permission', response.message)
                    } else {
                        toastrAlert('error', 'Set Permission', response.message, 'bottomCenter')
                    }
                },
                error: function() {
                    toastrAlert('error', 'Set Permission', response.message, 'bottomCenter')
                },
                complete: function() {
                    button.removeClass("disabled btn-progress");
                    button.removeClass("disabled");

                }
            });
        }
    });
}

/* checkboxall() call when user hover each class */
function checkAll(className) {
    $('.' + className).checkboxall();
}

/*Select All*/
(function($) {
    'use strict';

    if (typeof jQuery === "undefined") {
        console.log('jquery.checkboxall plugin needs the jquery plugin');
        return false;
    }

    $.fn.checkboxall = function(allSelector) {

        if (allSelector === undefined) {
            allSelector = 'all';
        }

        var parent = this;

        if ($('.' + allSelector, parent).length) {
            var all = $('.' + allSelector, parent),
                checkbox = parent.find('input[type="checkbox"]'),
                childCheckbox = checkbox.not('.' + allSelector, parent);

            return checkbox
                .unbind('click')
                .click(function(event) {
                    event.stopPropagation();

                    var th = $(this);

                    if (th.hasClass(allSelector)) {
                        checkbox.prop('checked', th.prop('checked'));
                    } else {
                        if (childCheckbox.length !== childCheckbox.filter(':checked').length) {
                            all.prop('checked', false);
                        } else {
                            all.prop('checked', true);
                        }
                    }
                });
        } else {
            console.log('jquery.checkboxall error: main selector is not exists.');
            console.log('Please add \'all\' class to first checkbox or give the first checkbox a class name and enter the checkboxall() functions for the class name!');
            console.log('Example: $(selector).checkboxall(\'your-checkbox-class-name\');');
        }
    };
}(jQuery));