$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });
    init_calender();
});

function init_calender() {
    var calendarEl = document.getElementById("myAppointmentCalendar");

    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek"
        },
        editable: false,
        navLinks: true, // can click day/week names to navigate views
        dayMaxEvents: true, // allow "more" link when too many events 
        eventClick: function(info) {
            check_event_info(info.event.extendedProps.appointment_id);
        },
        events: {
            url: getCalendarData,
        },
        loading: function(bool) {
            document.getElementById("loading").style.display = bool ?
                "block" :
                "none";
        }
    });

    calendar.render();
}

function check_event_info(id) {
    if (typeof getEventDetail !== 'undefined') {
        $.ajax({
            url: getEventDetail,
            type: "post",
            dataType: "json",
            data: { 'id': id },
            success: function(response) {
                if (response.status == "success") {
                    calendarModal.html(response.html);
                    calendarModal.modal("toggle");
                    $("[data-toggle='tooltip']").tooltip();
                }
            },
            error: function() {
                //
            }
        });
    }
}

function deleteAppointment(id) {
    if (typeof appointmentCancelUrl != 'undefined' && id) {
        var url = appointmentCancelUrl.replace(':slug', id);
        swal({
                html: true,
                title: "Cancel Appointment",
                text: "Are you sure you want to cancel this appointment?",
                type: "warning",
                showCancelButton: true,
                customClass: "",
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, Cancel it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    url: url,
                    type: "post",
                    dataType: "JSON",
                    data: {
                        id: id
                    },
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 200) {
                            swal.close();
                            calendarModal.modal("toggle");
                            toastrAlert('success', 'Appointment', response.message)
                        } else {
                            swal.close();
                            calendarModal.modal("toggle");
                            toastrAlert('error', 'Appointment', response.message || "Something want wrong!")
                        }
                        init_calender();
                    },
                    error: function() {
                        swal.close();
                    }
                });
            }
        );
    }
}