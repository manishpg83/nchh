$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof appointmentList !== 'undefined') {
        appointmentListTable = $('#appointmentListTable').DataTable({
            dom: "<'row'<'col-xs-12 col-lg-12't>><'row'<'col-lg-6'i><'col-lg-6'p>>",
            processing: true,
            serverSide: true,
            responsive: true,
            // ajax: appointmentList,
            ajax: {
                url: appointmentList,
                data: function(d) {
                    d.appointment_type = $('select[name="appointment_type"]').val();
                }
            },
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'doctor_name', name: 'doctor_name' },
                { data: 'patient_name', name: 'patient_name' },
                { data: 'date', name: 'date' },
                { data: 'start_time', name: 'start_time' },
                { data: 'appointment_type', name: 'appointment_type' },
                { data: 'address', name: 'address' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            initComplete: function() {
                init_tooltip();
            },
            drawCallback: function() {
                init_tooltip();
            }
        });
    }

    appointmentFilter.find('input[type=search]').on('keyup', function(e) {
        appointmentListTable.search(this.value).draw();
    });

    $('select[name="appointment_type"]').change(function(e) {
        appointmentListTable.draw();
    });

    appointmentDetailModal.on('hidden.bs.modal', function() {

    });
});

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
                            appointmentListTable.draw();
                            toastrAlert('success', 'Appointment', response.message)
                        } else {
                            swal.close();
                            appointmentListTable.draw();
                            toastrAlert('error', 'Appointment', response.message || "Something want wrong!")
                        }
                    },
                    error: function() {
                        swal.close();
                    }
                });
            }
        );
    }
}

function viewAppointment(id) {
    if (typeof appointmentViewUrl != 'undefined') {
        var url = appointmentViewUrl.replace(':slug', id);
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            success: function(res) {
                if (res.status == 200) {
                    appointmentDetailModal.html(res.html);
                    appointmentDetailModal.modal('toggle');
                    Chocolat(document.querySelectorAll('.chocolat-parent .chocolat-image'), {
                        loop: true
                    })
                } else {
                    toastrAlert('error', 'Appointment', res.message)
                }
            },
            error: function(res) {
                toastrAlert('error', 'Appointment', res.message)
            }

        });
    }
}