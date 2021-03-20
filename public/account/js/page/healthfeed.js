$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getHealthFeedList !== "undefined") {
        healthfeedTable = $("#healthfeedTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getHealthFeedList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "title", name: "title" },
                { data: "category", name: "category" },
                { data: "image", name: "image" },
                { data: "status", name: "status" },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
                //init_switch_reload();
            }
        });
    }

    healthfeedModal.on("hidden.bs.modal", function() {});
    healthfeedModal.on("shown.bs.modal", function() {
        if ($("#cover_photo").length > 0) {
            $("#cover_photo").change(function(e) {
                //var fileName = e.target.files[0].name;
                // alert('The file "' + fileName + '" has been selected.');
                readURL(this, "preview");
            });
        }
        showOtherCategory();
    });
});

function showOtherCategory() {
    if($('select[name="category_ids"] option:selected').text() == 'Other') {
        $('.showOtherCategoryDiv').removeClass('d-none');
    } else {
        $('.showOtherCategoryDiv').addClass('d-none');
    }
}

function addHealthFeed() {
    if (typeof addHealthFeedUrl !== "undefined") {
        $.ajax({
            url: addHealthFeedUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    healthfeedModal.html(response.html);
                    healthfeedModal.modal("toggle");
                    init_healthfeed_form();
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
function editHealthFeed(id) {
    healthfeedForm = $document.find("#healthfeedForm");
    if (typeof editHealthFeedUrl !== "undefined") {
        var url = editHealthFeedUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    healthfeedModal.html(response.html);
                    healthfeedModal.modal("toggle");
                    init_healthfeed_form();
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

//view blog
function viewHealthFeed(id) {
    if (typeof viewFullHealthFeed !== "undefined") {
        var url = viewFullHealthFeed.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    healthfeedModal.html(response.html);
                    healthfeedModal.modal("toggle");
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

function deleteHealthFeed(id) {

    if (typeof deleteHealthFeedUrl !== "undefined") {
        var url = deleteHealthFeedUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this health feed ?",
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
                            healthfeedTable.draw();
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

function init_healthfeed_form() {
    healthfeedForm = $document.find("#healthfeedForm");

    //Jquery validation of form field
    healthfeedForm.validate({
        rules: {
            category_ids: "required",
            title: "required",
            video_url: {
                url: true
            },
            content: 'required'
        },
        ignore: ".note-editor *",
        messages: {
            category_ids: "Please select health feed category",
            title: "Please enter health feed title",
            content: 'Please enter health feed content'
        },
        errorPlacement: function(error, element) {
            if(element.hasClass("summernote")) {
              error.insertAfter('.note-editor');
            } else {
              error.insertAfter(element);
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
                    healthfeedForm.find(".btn-submit").addClass("disabled btn-progress");
                    healthfeedForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        healthfeedModal.modal("toggle");
                        healthfeedForm.trigger("reset");
                        healthfeedTable.draw();
                    } else {}
                },
                error: function() {
                    //
                },
                complete: function() {
                    healthfeedForm.find(".btn-submit").removeClass("disabled btn-progress");
                    healthfeedForm.find(".close-button").removeClass("disabled");
                }
            });
        }
    });

    $(".summernote").summernote({
        dialogsInBody: true,
        minHeight: 200,
        maxHeight: 250,
        callbacks: {
            onChange: function(contents, $editable) {
                $(".summernote").val($(".summernote").summernote('isEmpty') ? $(".note-editable").html('') : contents);

                $("#healthfeedForm").valid();
            }
        }
    });
}