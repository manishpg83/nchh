$document = $(document);
var appointmentFileDropzone;
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    //get patient list
    if (typeof getPatientList !== 'undefined') {
        patientTable = $('#patientTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getPatientList,
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'patient_name', name: 'patient_name' },
                { data: 'patient_contact', name: 'patient_contact' },
                { data: 'detail', name: 'detail' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }

    //get patient's appointment list
    if (typeof getPatientAppointmentUrl !== 'undefined') {
        appointmentTable = $('#appointmentTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getPatientAppointmentUrl,
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'patient_name', name: 'patient_name' },
                { data: 'patient_contact', name: 'patient_contact' },
                { data: 'appointment_date', name: 'appointment_date' },
                { data: 'appointment_with', name: 'appointment_with' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }

    //get patient's appointment list
    if (typeof getPatientDiagnosticsAppointmentUrl !== 'undefined') {
        appointmentTable = $('#appointmentTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getPatientDiagnosticsAppointmentUrl,
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'patient_name', name: 'patient_name' },
                { data: 'patient_contact', name: 'patient_contact' },
                { data: 'services', name: 'services' },
                { data: 'price', name: 'price' },
                { data: 'date', name: 'date' },
                { data: 'action', name: 'action' },
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }

    //get appointment prescription list
    if (typeof getPrescriptionList !== 'undefined') {
        Chocolat(document.querySelectorAll('.chocolat-parent .chocolat-image'), {
            loop: true,
        })
        fileModal.on("hidden.bs.modal", function() {});

        fileModal.on("shown.bs.modal", function() {});
    }
});


function getAppointmentFile() {
    if (typeof getAppointmentFileUrl !== 'undefined') {
        $.ajax({
            url: getAppointmentFileUrl,
            type: 'get',
            dataType: 'json',
            success: function(response) {
                if (response.status == 200) {
                    fileModal.html(response.html);
                    fileModal.modal('toggle');
                    init_dropzone_files(response.files);
                }
            },
            error: function() {}
        })
    }
}

function init_dropzone_files(files) {

    if ($('#files').length) {
        appointmentFileDropzone = new Dropzone("#files", {
            autoProcessQueue: false,
            url: "#",
            acceptedFiles: ".png,.jpg,.jpeg",
            dictInvalidFileType: "You can't upload fild of this type.Only upload PNG, JPG, JPEG",
            maxFilesize: 100,
            /*MB*/
            addRemoveLinks: true,
            maxFiles: 5,
            accept: function(file, done) {
                done();
            },
            error: function(file, message, xhr) {
                if (xhr == null) this.removeFile(file);
                toastrAlert('error', 'Image', message)
            },
            addedfiles: function(file) {
                $(".dz-details").remove();
                $(".dz-progress").remove();
            },
            init: function() {
                var appointmentFileDropzone = this;
                files.forEach(file => {
                    if (file == null) { return; }
                    if (file.filename == null) { return; }

                    var mockFile = { id: file.id, name: file.file_name, size: file.file_size };
                    appointmentFileDropzone.emit("addedfile", mockFile);
                    appointmentFileDropzone.options.thumbnail.call(appointmentFileDropzone, mockFile, file.filename);
                    appointmentFileDropzone.emit("complete", mockFile);
                    appointmentFileDropzone.files.push(mockFile);
                });

                this.on("addedfile", function(file) {});
                this.on("removedfile", function(file) {
                    if (typeof deleteAppointmentFileUrl !== "undefined") {
                        url = deleteAppointmentFileUrl.replace(':slug', file.id)
                        $.ajax({
                            type: 'DELETE',
                            url: url,
                            data: file,
                            processData: false,
                            dataType: 'json',
                            contentType: false,
                            beforeSend: function() {},
                            success: function(res) {
                                $('#appointmentFile').html(res.html);
                                Chocolat(document.querySelectorAll('.chocolat-parent .chocolat-image'), {
                                    loop: true,
                                })
                            },
                            error: function(res) {
                                toastrAlert('error', 'Medical Record', res.message, 'bottomCenter')
                            },
                            complete: function() {}
                        });
                        return false;
                    }
                });

            }
        });

        appointmentFileDropzone.on("removedfile", function(file) {});
    }
}

function submitFiles() {
    fileForm = $document.find('#fileForm');

    //Jquery validation of form field
    fileForm.validate({

        submitHandler: function(form) {
            var action = $(form).attr('action');
            var formData = new FormData($(form)[0]);
            if (typeof appointmentFileDropzone !== "undefined") {
                file = [];
                for (var i = 0; i < appointmentFileDropzone.files.length; i++) {
                    formData.append('images[]', appointmentFileDropzone.files[i]);
                }
            }
            $.ajax({
                type: 'POST',
                url: action,
                data: formData,
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function() {
                    fileForm.find('.btn-submit').addClass('disabled btn-progress');
                    fileForm.find('.close-button').addClass('disabled');
                },
                success: function(res) {
                    fileModal.modal('toggle');
                    $('#appointmentFile').html(res.html);
                    Chocolat(document.querySelectorAll('.chocolat-parent .chocolat-image'), {
                        loop: true,
                    })
                },
                error: function(res) {
                    toastrAlert('error', 'Files', res.message, 'bottomCenter')
                },
                complete: function() {
                    fileForm.find('.btn-submit').removeClass('disabled btn-progress');
                    fileForm.find('.close-button').removeClass('disabled');
                }
            });
        }
    });
}

//remove prescription row
function removeRow(id) {
    $("#" + id).remove();
}

function editPrescription() {
    if (typeof editPrescriptionUrl !== "undefined") {
        $.ajax({
            url: editPrescriptionUrl,
            type: "get",
            dataType: "JSON",
            success: function(res) {
                var previousData = $("#appointmentPrescription").html();
                $("#appointmentPrescription").html(res.html);
                $(".select2").select2();
                init_prescription_form(previousData);
            },
            error: function() {}
        });
    }
}

function init_prescription_form(previousData) {
    $('.cancelEvent').click(function(event) {
        $("#appointmentPrescription").html(previousData);
    });

    $("#drugs").on("change", function(e) {
        var drug_name = document.getElementById("drugs").value;
        if (typeof appendPrescriptionUrl !== "undefined" && drug_name != '') {
            var url = appendPrescriptionUrl.replace(":slug", drug_name);
            $.ajax({
                url: url,
                type: "get",
                dataType: "JSON",
                success: function(data) {
                    $("#drugsTable").append(data.html);
                },
                error: function() {}
            });
        }
    });

    prescriptionForm = $document.find("#prescriptionForm");
    prescriptionForm.validate({
        submitHandler: function(form) {
            var action = $(form).attr("action");
            var formData = new FormData($(form)[0]);
            $.ajax({
                type: "POST",
                url: action,
                data: formData,
                processData: false,
                dataType: "json",
                contentType: false,
                beforeSend: function() {
                    prescriptionForm.find(".btn-submit").addClass("disabled btn-progress");
                },
                success: function(res) {
                    //toastrAlert("success", "Prescription", res.message);
                    $("#appointmentPrescription").html(res.html);
                },
                error: function(res) {
                    console.log(res);
                    toastrAlert("error", "Prescription", res.message);
                },
                complete: function() {
                    prescriptionForm.find(".btn-submit").removeClass("disabled btn-progress");
                }
            });
        }
    });
}

//drug add from prescription page
function addDrugs() {
    if (typeof addDrugUrl !== "undefined") {
        $.ajax({
            url: addDrugUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    fileModal.html(response.html);
                    fileModal.modal("toggle");
                    init_drug_form();
                } else {
                    //
                }
            },
            error: function() {
                //
            }
        });
    }
}

//init drug form 
function init_drug_form() {

    drugForm = $document.find("#drugForm");

    //Jquery validation of form field
    drugForm.validate({
        rules: {
            name: "required",
            type: "required",
            strength: "required",
            unit: "required"
        },
        messages: {
            name: "Please enter name",
            type: "Please select drug type",
            strength: "Please enter strength",
            unit: "Please select drug unit"
        },
        submitHandler: function(form) {
            var action = $(form).attr("action");
            var formData = new FormData($(form)[0]);
            formData.append('from', 'prescription');
            formData.append('appointment_id', appointment_id);
            $.ajax({
                type: "POST",
                url: action,
                data: formData,
                processData: false,
                dataType: "json",
                contentType: false,
                beforeSend: function() {
                    drugForm.find(".btn-submit").addClass("disabled btn-progress");
                    drugForm.find(".close-button").addClass("disabled");
                },
                success: function(res) {
                    if (res.status == "success") {
                        fileModal.modal("toggle");
                        drugForm.trigger("reset");
                        $("#drugsTable").append(res.data.html);
                        $("#drugs").append(res.data.drug_name);
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    drugForm.find(".btn-submit").removeClass("disabled btn-progress");
                    drugForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}

function sendToPharmacy(appointment_id) {
    var pharmacy_id = document.getElementById("pharmacy_id").value;
    if (typeof sendToPharmacyUrl !== "undefined") {
        if (!pharmacy_id) {
            toastrAlert("error", "Pharmacy", "Please select pharmacy");
        } else {
            $.ajax({
                url: sendToPharmacyUrl,
                type: "post",
                dataType: "JSON",
                data: {
                    pharmacy_id: pharmacy_id,
                    appointment_id: appointment_id
                },
                beforeSend: function() {
                    $document.find(".send-prescription").addClass("disabled btn-progress");
                },
                success: function(res) {
                    if (res.status == 200) {
                        $('#send_prescription').html(res.html)
                        toastrAlert("success", "Pharmacy", res.message);
                    } else {
                        toastrAlert("error", "Pharmacy", res.message);
                    }
                },
                error: function(res) {
                    toastrAlert("error", "Pharmacy", res.message);
                },
                complete: function() {
                    $document.find(".send-prescription").removeClass("disabled btn-progress");
                }
            });
        }
    }
}