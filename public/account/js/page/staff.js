$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getStaffList !== 'undefined') {
        StaffTable = $('#StaffTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getStaffList,
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'name' },
                { data: 'email' },
                { data: 'phone' },
                { data: 'role' },
                { data: 'fees' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }

    if ($('#staffManagerForm').length > 0) {
        init_staff_form();
    }
});

function init_staff_form() {

    var FormStaff = staffManagerForm.validate({
        rules: {
            role_id: "required",
            name: "required",
            phone: {
                required: true,
                remote: {
                    url: isDoctorRegisteredUrl,
                    headers: header,
                    type: "POST",
                    data: {
                        phone: function() {
                            return $("input[name='phone']").val();
                        }
                    },
                },
                valid_phone: true,
            },
            email: {
                required: true,
                email: true,
                remote: {
                    url: isDoctorRegisteredUrl,
                    headers: header,
                    type: "POST",
                    data: {
                        email: function() {
                            return $("input[name='email']").val();
                        }
                    },
                }
            },
            address: {
                required: true,
            },
            locality: {
                required: true,
            },
            city: {
                required: true,
            },
            state: {
                required: true,
            },
            country: {
                required: true,
            },
            pincode: {
                required: true,
                zipcode: true
            }
        },
        messages: {
            phone: { remote: "Doctor already register with this number.", },
            email: { remote: "Doctor already register with this email address." }
        },
        submitHandler: function(form) {
            if ($(form).find('input[name=dialcode]').length > 0) {
                $(form).find('input[name=dialcode]').val(iti.getSelectedCountryData().dialCode);
            }
            staffManagerForm.find('.btn-submit').addClass('disabled btn-progress');
            staffManagerForm.find('.btn-reset').addClass('disabled');

            if ($('#timing_chart').length > 0 && typeof as_doctor != "undefined") {
                var get_timing = $document.find('#timing_chart').jqs('export');
                $('#field_timing').val(get_timing);

                var count = 0;
                JSON.parse(get_timing).forEach(element => {
                    if (element.periods.length !== 0) {
                        count++;
                    }
                });
                if (count > 0) {
                    $('#field_timing').val(get_timing);
                } else {
                    toastrAlert('error', 'Timing', 'Please select practice timing')
                    staffManagerForm.find('.btn-submit').removeClass('disabled btn-progress');
                    staffManagerForm.find('.btn-reset').removeClass('disabled');
                    return false;
                }
            }

            form.submit();
        }
    });

    if (typeof as_doctor != "undefined" && as_doctor != '') {
        $document.find('#doctor_dropdown').show();
        $document.find('.other_field').show();
        $document.find('#basic_detail_box').empty();
        init_doctor_dropdown($('.search_doctor_dropdown'));

        /* apply validation method */
        staffManagerForm.find("#user_id").rules("add", { required: true, messages: { required: "Please select the doctor." } });
        staffManagerForm.find("input[name='fees']").rules("add", { required: true, number: true, min: 1 });
        /* staffManagerForm.find("input[name='address']").rules("add", { required: true });
        staffManagerForm.find("input[name='locality']").rules("add", { required: true });
        staffManagerForm.find("input[name='city']").rules("add", { required: true });
        staffManagerForm.find("input[name='state']").rules("add", { required: true });
        staffManagerForm.find("select[name='country']").rules("add", { required: true });
        staffManagerForm.find("input[name='pincode']").rules("add", { required: true }); */
        // $('.search_doctor_dropdown').select2(as_doctor);
        /* $('.search_doctor_dropdown').select2('data', { id: as_doctor, term: 'Lorem Ipsum' });
        $('.search_doctor_dropdown').trigger('change'); */

        if (typeof getDoctorScheduleUrl !== "undefined") {
            url = getDoctorScheduleUrl.replace(':slug', as_doctor);
            $.ajax({
                type: 'POST',
                url: url,
                data: { 'practice_id': practice_id, '_method': 'PUT' },
                beforeSend: function() {},
                success: function(res) {
                    $document.find('#schedule_box').show();
                    var schedule = res.result.length > 0 ? JSON.parse(res.result) : res.result
                    console.log(schedule);
                    $('.schedule_container').empty();
                    $('.schedule_container').prepend($('<div id="timing_chart"></div>'));
                    initTimeChart(schedule);
                },
                error: function(res) {
                    toastrAlert('error', 'Add Staff', res.message, 'bottomCenter')
                },
                complete: function() {}
            });
        }
    }

    /* Change Role Script*/
    $document.find('input[name="role_id"]').change(function() {
        var role = $(this).attr('id')
        var id = $(this).val()

        if (role == "doctor") {
            $document.find('#doctor_dropdown').show();
            $document.find('.other_field').remove();
            $document.find('#basic_detail_box').empty();
            $document.find('#address_detail_box').remove();
            init_doctor_dropdown($('.search_doctor_dropdown'));
            $(".buttontext").text('Send Request');
            /* apply validation method */
            staffManagerForm.find("#user_id").rules("add", { required: true, messages: { required: "Please select the doctor." } });
            /* staffManagerForm.find("input[name='fees']").rules("add", { required: true, number: true }); */
        } else {
            $(".buttontext").text('Add');
            $document.find('#basic_detail_box').load(' #basic_detail_box', function() {
                intlPhoneField('phone', FormStaff);
                init_select2();
            });

            $document.find('#address_detail_box').load(' #address_detail_box', function() {
                init_select2();
            });

            $document.find('#doctor_dropdown').hide();
            $document.find('.other_field').hide();
            $document.find('#schedule_box').hide();
            $document.find('.schedule_container').empty();

            /* Remove validation */
            staffManagerForm.find("#user_id").rules("remove");
            /* staffManagerForm.find("input[name='fees']").rules("remove"); */
            /* staffManagerForm.find("input[name='address']").rules("remove");
            staffManagerForm.find("input[name='locality']").rules("remove");
            staffManagerForm.find("input[name='city']").rules("remove");
            staffManagerForm.find("input[name='state']").rules("remove");
            staffManagerForm.find("select[name='country']").rules("remove");
            staffManagerForm.find("input[name='pincode']").rules("remove"); */
        }
    })

    $document.find('.search_doctor_dropdown').change(function() {
        var user_id = $(this).val();
        if (typeof getDoctorScheduleUrl !== "undefined" && user_id) {
            url = getDoctorScheduleUrl.replace(':slug', user_id);
            $.ajax({
                type: 'POST',
                url: url,
                data: { '_method': 'PUT' },
                beforeSend: function() {},
                success: function(res) {
                    $document.find('#schedule_box').hide();
                    var schedule = res.result.length > 0 ? JSON.parse(res.result) : res.result
                    $('.schedule_container').empty();
                    $('.schedule_container').prepend($('<div id="timing_chart"></div>'));
                    initTimeChart(schedule);
                },
                error: function(res) {
                    toastrAlert('error', 'Add Staff', res.message, 'bottomCenter')
                },
                complete: function() {}
            });
        }
    })

    if ($('#phone').length > 0) {
        intlPhoneField('phone', FormStaff);
    }
}

function initTimeChart(parseValue) {
    $document.find('#timing_chart').jqs({
        mode: 'edit',
        hour: 24,
        days: 7,
        periodDuration: 60,
        data: parseValue,
        periodOptions: false,
        periodColors: [],
        periodTitle: '',
        periodBackgroundColor: 'rgba(82, 155, 255, 0.5)',
        periodBorderColor: '#2a3cff',
        periodTextColor: '#000',
        periodRemoveButton: 'Remove',
        // periodDuplicateButton: 'Duplicate',
        periodTitlePlaceholder: 'Title',
        daysList: [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ],
        onInit: function() {
            console.log('Yeah Init');
        },
        onAddPeriod: function(period, jqs) {},
        onRemovePeriod: function() {
            var get_timing = $('#timing_chart').jqs('export');
            console.log(get_timing);
        },
        onDuplicatePeriod: function() {},
        onClickPeriod: function() {}
    });
}

function deleteStaff(id) {

    if (typeof deleteStaffUrl != 'undefined' && id) {
        url = deleteStaffUrl.replace(':slug', id);
        deleteRecord(header, url, StaffTable, "Remove Staff", "Are you sure you want to delete this member?", 'Yes remove it!');
    }
}

function getValue(radio) {
    if (radio.id == 'doctor') {
        $("#ifDoctor").show();
        $("#ifNotDoctor").hide();
    } else if (radio.id == 'manager') {
        $("#ifDoctor").hide();
        $("#ifNotDoctor").show();
    } else {
        $("#ifDoctor").hide();
        $("#ifNotDoctor").show();
    }
}