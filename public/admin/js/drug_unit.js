$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getUnitList !== "undefined") {
        unitTable = $("#unitTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getUnitList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: true
                },
                { data: "title", name: "title" },
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

    unitModal.on("hidden.bs.modal", function() {});
});

function addUnit() {
    if (typeof addUnitUrl !== "undefined") {
        $.ajax({
            url: addUnitUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    unitModal.html(response.html);
                    unitModal.modal("toggle");
                    init_unit_form();
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

//edit health feed unit
function editUnit(id) {
    unitForm = $document.find("#unitForm");
    if (typeof editUnitUrl !== "undefined") {
        var url = editUnitUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    unitModal.html(response.html);
                    unitModal.modal("toggle");
                    init_unit_form();
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

//delete type
function deleteUnit(id) {
    if (typeof deleteUnitUrl !== "undefined") {
        var url = deleteUnitUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this unit ?",
                type: "warning",
                showCancelButton: true,
                customClass: "",
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it !",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    url: url,
                    type: "DELETE",
                    processData: false,
                    contentType: false,
                    dataType: "JSON",
                    data: { id: id },
                    success: function(response) {
                        if (response.status === "success") {
                            swal.close();
                            unitTable.draw();
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

function init_unit_form() {
    unitForm = $document.find("#unitForm");

    //Jquery validation of form field
    unitForm.validate({
        rules: {
            name: "required",
        },
        messages: {
            name: "Unit is required.",
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
                    unitForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    unitForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        unitModal.modal("toggle");
                        unitForm.trigger("reset");
                        unitTable.draw();
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    unitForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    unitForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}