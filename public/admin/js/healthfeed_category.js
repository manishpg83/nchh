$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getCategoryList !== "undefined") {
        categoryTable = $("#categoryTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getCategoryList,
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

    categoryModal.on("hidden.bs.modal", function() {});
});

function addCategory() {
    if (typeof addCategoryUrl !== "undefined") {
        $.ajax({
            url: addCategoryUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    categoryModal.html(response.html);
                    categoryModal.modal("toggle");
                    init_category_form();
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

//edit health feed category
function editCategory(id) {
    categoryForm = $document.find("#categoryForm");
    if (typeof editCategoryUrl !== "undefined") {
        var url = editCategoryUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    categoryModal.html(response.html);
                    categoryModal.modal("toggle");
                    init_category_form();
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

//delete category
function deleteCategory(id) {
    if (typeof deleteCategoryUrl !== "undefined") {
        var url = deleteCategoryUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this category ?",
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
                            categoryTable.draw();
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

function init_category_form() {
    categoryForm = $document.find("#categoryForm");

    //Jquery validation of form field
    categoryForm.validate({
        rules: {
            title: "required",
        },
        messages: {
            title: "Please enter title",
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
                    categoryForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    categoryForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        categoryModal.modal("toggle");
                        categoryForm.trigger("reset");
                        categoryTable.draw();
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    categoryForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    categoryForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}