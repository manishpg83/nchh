$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getTypeList !== "undefined") {
        typeTable = $("#typeTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getTypeList,
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

    typeModal.on("hidden.bs.modal", function() {});
});

function addType() {
    if (typeof addTypeUrl !== "undefined") {
        $.ajax({
            url: addTypeUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    typeModal.html(response.html);
                    typeModal.modal("toggle");
                    init_type_form();
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

//edit health feed type
function editType(id) {
    typeForm = $document.find("#typeForm");
    if (typeof editTypeUrl !== "undefined") {
        var url = editTypeUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    typeModal.html(response.html);
                    typeModal.modal("toggle");
                    init_type_form();
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
function deleteType(id) {
    if (typeof deleteTypeUrl !== "undefined") {
        var url = deleteTypeUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this type ?",
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
                            typeTable.draw();
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

function init_type_form() {
    typeForm = $document.find("#typeForm");

    //Jquery validation of form field
    typeForm.validate({
        rules: {
            name: "required",
        },
        messages: {
            name: "Type is required.",
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
                    typeForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    typeForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        typeModal.modal("toggle");
                        typeForm.trigger("reset");
                        typeTable.draw();
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    typeForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    typeForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}