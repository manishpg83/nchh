$document = $(document);
var recordFileDropzone;
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });
    
    if ($('#medicalRecordForm').length > 0) {
        init_medicalRecord_form();
    }
    
    Chocolat(document.querySelectorAll('.gallery-item'), {
        // options here
        loop: false,
    })
    
});

function init_medicalRecord_form() {
    var btn_submit = medicalRecordForm.find('#btn_submit');
    var Recordform = medicalRecordForm.validate({
        rules: {
            title: {
                required: true,
                normalizer: function(value) {
                    return $.trim(value);
                }
            },
            record_for: {
                required: true,
            },
            record_date: {
                validDate: true,
                required: true
            },
            type: { required: true }
        },
        messages: {},
        submitHandler: function(form) {
            var action = $(form).attr('action');
            var formData = new FormData($(form)[0]);

            if(recordFileDropzone.files.length == 0) {
                $('.showFileValidationError').html('');
                $('.showFileValidationError').append('<label id="record_file-error" for="record_file" class="error">File is required.</label>');
                return false;
            }
            
            images = [];
            for (var i = 0; i < recordFileDropzone.files.length; i++) {
                images.push(recordFileDropzone.files[i]);
                formData.append('file[]', recordFileDropzone.files[i]);
            }
            // formData.append('images', images);
            // console.log(images);
            // return false;
            
            $.ajax({
                type: 'POST',
                url: action,
                data: formData,
                processData: false,
                dataType: 'json',
                contentType: false,
                beforeSend: function() {
                    btn_submit.addClass('btn-progress disabled');
                },
                success: function(res) {
                    toastrAlert('success', 'Medical Record', res.message)
                    setTimeout(() => {
                        if (res.redirect) {
                            window.location = res.redirect;
                        }
                    }, 5000);
                },
                error: function(res) {
                    toastrAlert('error', 'Medical Record', res.message, 'bottomCenter')
                },
                complete: function() {
                    btn_submit.removeClass('btn-progress disabled');
                }
            });
            return false;
        }
    });
    
    if ($('#files').length) {
        recordFileDropzone = new Dropzone("#files", {
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
                $('.showFileValidationError').html('');
            },
            init: function() {
                
                if (typeof getMedicalRecordDetails !== "undefined") {
                    $.get(getMedicalRecordDetails, function(medical_record) {
                        console.log(medical_record);
                        medical_record.forEach(file => {
                            console.log(file);
                            if (file == null) { return; }
                            if (file.filename == null) { return; }
                            
                            var mockFile = { id: file.id, name: file.file_name, size: file.file_size };
                            recordFileDropzone.emit("addedfile", mockFile);
                            recordFileDropzone.options.thumbnail.call(recordFileDropzone, mockFile, file.filename);
                            recordFileDropzone.emit("complete", mockFile);
                            recordFileDropzone.files.push(mockFile);
                        });
                    });
                }
                
                this.on("addedfile", function(file) {
                    // if (this.files.length > 1) {
                    //     this.removeFile(this.files[0]);
                    // }
                    // if (this.files.length > 1) {
                    //     this.removeFile(this.files[0]);
                    // }
                });
                this.on("removedfile", function(file) {
                    console.log(file);
                    if (typeof deleteMedicalRecordFileUrl !== "undefined") {
                        url = deleteMedicalRecordFileUrl.replace(':slug', file.id)
                        $.ajax({
                            type: 'DELETE',
                            url: url,
                            data: file,
                            processData: false,
                            dataType: 'json',
                            contentType: false,
                            beforeSend: function() {},
                            success: function(res) {
                                // toastrAlert('success', 'Medical Record', res.message)
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
        
        recordFileDropzone.on("removedfile", function(file) {
            // console.log(file);
            // alert('remove triggered');
        });
    }
}

function deleteMedicalRecord(anchor, id) {
    
    var me = $(anchor)
    var me_data = me.data('confirm');
    
    me_data = me_data.split("|");
    me.fireModal({
        title: me_data[0],
        body: me_data[1],
        buttons: [{
            text: me.data('confirm-text-yes') || 'Yes',
            class: 'btn btn-danger btn-shadow',
            handler: function(modal) {
                if (typeof deleteMedicalRecordUrl !== "undefined") {
                    url = deleteMedicalRecordUrl.replace(':slug', id)
                    $.ajax({
                        type: 'DELETE',
                        url: url,
                        processData: false,
                        dataType: 'json',
                        contentType: false,
                        beforeSend: function() {},
                        success: function(res) {
                            toastrAlert('success', 'Medical Record', res.message)
                            $.destroyModal(modal);
                            $('.medical_record_container').load(' .medical_record_container');
                        },
                        error: function(res) {
                            toastrAlert('error', 'Medical Record', res.message, 'bottomCenter')
                            $.destroyModal(modal);
                        },
                        complete: function() {}
                    });
                    return false;
                }
            }
        },
        {
            text: me.data('confirm-text-cancel') || 'Cancel',
            class: 'btn btn-secondary',
            handler: function(modal) {
                $.destroyModal(modal);
            }
        }
    ]
})
return false;
}