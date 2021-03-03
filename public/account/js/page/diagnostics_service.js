$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getDiagnosticsServiceList !== "undefined") {
        diagnosticsServiceTable = $("#diagnosticsServiceTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getDiagnosticsServiceList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name", width: '25%' },
                { data: "price", name: "price", width: '15%' },
                { data: "information", name: "information", width: '40%' },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }

    diagnosticsServiceModal.on("hidden.bs.modal", function() {});
    diagnosticsServiceModal.on("shown.bs.modal", function() {});
});

function addDiagnosticsService() {
    if (typeof addDiagnosticsServiceUrl !== "undefined") {
        $.ajax({
            url: addDiagnosticsServiceUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    diagnosticsServiceModal.html(response.html);
                    diagnosticsServiceModal.modal("toggle");
                    init_diagnostics_service_form();
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

//edit health feed
function editDiagnosticsService(id) {
    diagnosticsServiceForm = $document.find("#diagnosticsServiceForm");
    if (typeof editDiagnosticsServiceUrl !== "undefined") {
        var url = editDiagnosticsServiceUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    diagnosticsServiceModal.html(response.html);
                    diagnosticsServiceModal.modal("toggle");
                    init_diagnostics_service_form();
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

function deleteDiagnosticsService(id) {
    if (typeof deleteDiagnosticsServiceUrl !== "undefined") {
        var url = deleteDiagnosticsServiceUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this services ?",
                type: "warning",
                showCancelButton: true,
                customClass: "",
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    url: url,
                    type: "DELETE",
                    dataType: "JSON",
                    data: {
                        id: id
                    },
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === "success") {
                            swal.close();
                            diagnosticsServiceTable.draw();
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

function init_diagnostics_service_form() {
    diagnosticsServiceForm = $document.find("#diagnosticsServiceForm");

    //Jquery validation of form field
    diagnosticsServiceForm.validate({
        rules: {
            name: "required",
            price: {
                required: true,
                number: true
            }
        },
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
                    diagnosticsServiceForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    diagnosticsServiceForm
                        .find(".close-button")
                        .addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        diagnosticsServiceModal.modal("toggle");
                        diagnosticsServiceForm.trigger("reset");
                        diagnosticsServiceTable.draw();
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    diagnosticsServiceForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    diagnosticsServiceForm
                        .find(".close-button")
                        .removeClass("disabled");
                }
            });
        }
    });
}