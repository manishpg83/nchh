$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getLanguageList !== "undefined") {
        languageTable = $("#languageTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getLanguageList,
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

    languageModal.on("hidden.bs.modal", function() {});
});

function addLanguage() {
    if (typeof addLanguageUrl !== "undefined") {
        $.ajax({
            url: addLanguageUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    languageModal.html(response.html);
                    languageModal.modal("toggle");
                    init_language_form();
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

//edit health feed language
function editLanguage(id) {
    languageForm = $document.find("#languageForm");
    if (typeof editLanguageUrl !== "undefined") {
        var url = editLanguageUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    languageModal.html(response.html);
                    languageModal.modal("toggle");
                    init_language_form();
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
function deleteLanguage(id) {
    if (typeof deleteLanguageUrl !== "undefined") {
        var url = deleteLanguageUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this language ?",
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
                            languageTable.draw();
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

function init_language_form() {
    languageForm = $document.find("#languageForm");

    //Jquery validation of form field
    languageForm.validate({
        rules: {
            name: "required",
            short_name: "required",
        },
        messages: {
            name: "Language is required.",
            short_name: "Language Short Name is required.",
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
                    languageForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    languageForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        languageModal.modal("toggle");
                        languageForm.trigger("reset");
                        languageTable.draw();
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    languageForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    languageForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });
}