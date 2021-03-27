$document = $(document);
var rules, validation_msg;
var documentForm = new FormData();
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    clinicProfileModal.on("shown.bs.modal", function() {
        validateDocName();
    });
});

function loadClinicProfileVerificationModal() {
    if (typeof uploadDocumentFormURL !== "undefined") {
        $.ajax({
            url: uploadDocumentFormURL,
            type: "GET",
            beforeSend: function() {},
            success: function(res) {
                clinicProfileModal.html(res.html);
                clinicProfileModal.modal({
                    show: true,
                    backdrop: "static",
                    keyboard: false
                });
                init_documentForm();
            },
            error: function(res) {
                toastrAlert("error", "Profile", res.message);
            },
            complete: function() {}
        });
    }
}
function viewClinicverifieddocument() {
    if (typeof uploadDocumentFormURL !== "undefined") {
        $.ajax({
            url: uploadDocumentFormURL,
            type: "GET",
            data: {type: 'approved-document' },
            beforeSend: function() {},
            success: function(res) {
                clinicProfileModal.html(res.html);
                clinicProfileModal.modal({
                    show: true,
                    backdrop: "static",
                    keyboard: false
                });
            },
            error: function(res) {
                toastrAlert("error", "Profile", res.message);
            },
            complete: function() {}
        });
    }
}

function init_documentForm() {
    clinicProfileForm = $document.find("#clinicProfileForm");
    //Jquery validation of form field
    clinicProfileForm.validate({
        rules: {
            'document_name[*]' : {
                required: true
            },
            'document_proof[*]' : {
                required: true,
                extension: "png|jpeg|jpg",
            },
            agree : 'required'
        },
        ignore: [],
        errorPlacement: function (error, element) {
            if (element.attr("type") == "checkbox") {
                element.parents('.custom-control').append(error);
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
                    clinicProfileForm
                        .find(".btn-submit")
                        .addClass("disabled btn-progress");
                    clinicProfileForm.find(".close-button").addClass("disabled");
                },
                success: function(data) {
                    if (data.status == 200) {
                        clinicProfileModal.modal("toggle");
                        clinicProfileForm.trigger("reset");
                        window.location.reload();
                    } else {
                        toastrAlert("error", "Document", data.message);
                    }
                },
                error: function(data) {},
                complete: function(data) {
                    clinicProfileForm
                        .find(".btn-submit")
                        .removeClass("disabled btn-progress");
                    clinicProfileForm
                        .find(".close-button")
                        .removeClass("disabled");
                }
            });
        }
    });
}

function addDocDiv() {

    $dataId = $('.documentDiv .doc_div:last').data('id') + 1;

    var html = `<div class="doc_div doc_div_`+ $dataId +`" data-id="`+ $dataId +`">
                <div class="form-group mb-0 row">
                    <div class="col-sm-12 mb-2">
                        <label for="name">Document Name*</label>
                        <input type="text" name="document_name[`+ $dataId +`]" id="document_name[`+ $dataId +`]" class="form-control document_name" placeholder="Document Name">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-11 mt-3">
                        <label for="name">Document* (jpeg, png, jpg)</label>
                        <input class="form-control document_proof" type="file" name="document_proof[`+ $dataId +`]" id="document_proof[`+ $dataId +`]  placeholder="Document Proof">
                    </div>
                    <div class="col-sm-1">
                        <a href="javascript:void(0)" data-id="`+ $dataId +`" class="btn btn-danger btn-submit mt-5" onclick="removeDocDiv(`+ $dataId +`)"><i class="fa fa-times"></i></a>
                    </div>
                </div>
                <hr/>
            <div>`;

    $('.documentDiv').append(html);

    validateDocName();
}

function validateDocName() {

    $('.document_name').each(function() {
        $(this).rules("add", 
            {
                required: true
            })
    });            
    $('.document_proof').each(function() {
        if($(this).next('label').attr('class') != 'document_file_name'){

            $(this).rules("add", {
                required: true,
                extension: "png|jpeg|jpg",
            })
        } else {

            $(this).rules("add", {
                extension: "png|jpeg|jpg",
            })
        }
    });            
}

function removeDocDiv($dataId) {

    $('.doc_div_' + $dataId).remove();
}

function openRejectionReason(reason) {
    $('.rejection_reason').html('');
    $('#showRejectionReson').modal('toggle');
    $('.rejection_reason').html(reason);
}