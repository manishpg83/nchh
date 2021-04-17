$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getDrugList !== "undefined") {
        drugTable = $("#drugTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getDrugList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "type", name: "type" },
                { data: "unit", name: "unit" },
                { data: "strength", name: "strength" },
                { data: "instructions", name: "instructions" },
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

    drugModal.on("hidden.bs.modal", function() {});
});

function addDrug() {
    if (typeof addDrugUrl !== "undefined") {
        $.ajax({
            url: addDrugUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    drugModal.html(response.html);
                    drugModal.modal("toggle");
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

//edit health feed
function editDrug(id) {
    drugForm = $document.find("#drugForm");
    if (typeof editDrugUrl !== "undefined") {
        var url = editDrugUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == "success") {
                    drugModal.html(response.html);
                    drugModal.modal("toggle");
                    init_drug_form();
                    $('#type').trigger('change');
                    $('#unit').trigger('change');
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

//delete drug
function deleteDrug(id) {
    if (typeof deleteDrugUrl !== "undefined") {
        var url = deleteDrugUrl.replace(":slug", id);
        swal({
                html: true,
                title: "Delete",
                text: "Are you sure you want to delete this drug ?",
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
                    processData: false,
                    contentType: false,
                    dataType: "JSON",
                    data: { id: id },
                    success: function(response) {
                        if (response.status === "success") {
                            swal.close();
                            drugTable.draw();
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

function init_drug_form() {
    drugForm = $document.find("#drugForm");

    //Jquery validation of form field
    drugForm.validate({
        rules: {
            name: "required",
            type: "required",
            strength: "required",
            unit: "required",
            /*other_unit: function() {
                return $('#unit').val() == 'other';
            }*/
        },
        messages: {
            name: "Please enter name",
            type: "Please select drug type",
            strength: "Please enter strength",
            unit: "Please select drug unit",
            //other_unit: "Please enter other unit"
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
                    drugForm.find(".btn-submit").addClass("disabled btn-progress");
                    drugForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == "success") {
                        drugModal.modal("toggle");
                        drugForm.trigger("reset");
                        drugTable.draw();
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

    /*$('#unit').on('change', function() {
        if ($(this).val() == 'other') {
            $('.showOtherUnitInput').removeClass('d-none');
        } else {
            $('.showOtherUnitInput').addClass('d-none');
        }
    });*/
}

function showOtherType() {
    if($('select[name="type"] option:selected').text() == 'Other') {
        $('.showOtherTypeDiv').removeClass('d-none');
    } else {
        $('.showOtherTypeDiv').addClass('d-none');
    }
}

function showOtherUnit() {
    if($('select[name="unit"] option:selected').text() == 'Other') {
        $('.showOtherUnitDiv').removeClass('d-none');
    } else {
        $('.showOtherUnitDiv').addClass('d-none');
    }
}