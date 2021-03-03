$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    //get doctor list
    if (typeof getDoctorList !== "undefined") {
        doctorTable = $("#doctorTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getDoctorList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "phone", name: "phone" },
                { data: "email", name: "email" },
                { data: "specialty", name: "specialty" },
                { data: "experience", name: "experience" },
                { data: "reg_date", name: "reg_date" }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get user verification list
    if (typeof getDoctorVerificationList !== "undefined") {
        doctorVerificationTable = $("#doctorVerificationTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getDoctorVerificationList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "profile", name: "profile" },
                {
                    data: "phone",
                    name: "phone",
                    orderable: false,
                    searchable: false
                },
                { data: "location", name: "	location" },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get clinic list
    if (typeof getClinicList !== "undefined") {
        clinicTable = $("#clinicTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getClinicList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "phone", name: "phone" },
                { data: "email", name: "email" },
                { data: "doctors", name: "doctors" },
                { data: "locality", name: "locality" },
                { data: "specialty", name: "specialty" },
                { data: "gallery", name: "gallery" }
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
                $(function() {
                    $("#portfolio").magnificPopup({
                        delegate: "a",
                        type: "image",
                        image: {
                            cursor: null,
                            titleSrc: "title"
                        },
                        gallery: {
                            enabled: true,
                            preload: [0, 1], // Will preload 0 - before current, and 1 after the current image
                            navigateByImgClick: true
                        }
                    });
                });
            }
        });
    }

    //get hospital list
    if (typeof getHospitalList !== "undefined") {
        hospitalTable = $("#hospitalTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getHospitalList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "phone", name: "phone" },
                { data: "email", name: "email" },
                { data: "doctors", name: "doctors" },
                { data: "locality", name: "locality" },
                { data: "specialty", name: "specialty" },
                { data: "services", name: "services" },
                { data: "gallery", name: "gallery" }
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
                $(function() {
                    $("#portfolio").magnificPopup({
                        delegate: "a",
                        type: "image",
                        image: {
                            cursor: null,
                            titleSrc: "title"
                        },
                        gallery: {
                            enabled: true,
                            preload: [0, 1], // Will preload 0 - before current, and 1 after the current image
                            navigateByImgClick: true
                        }
                    });
                });
            }
        });
    }

    //get pharmacy list
    if (typeof getPharmacyList !== "undefined") {
        pharmacyTable = $("#pharmacyTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getPharmacyList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "phone", name: "phone" },
                { data: "email", name: "email" }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get agent list
    if (typeof getAgentList !== "undefined") {
        agentTable = $("#agentTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getAgentList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "phone", name: "phone" },
                { data: "email", name: "email" },
                { data: "locality", name: "locality" },
                { data: "reg_date", name: "reg_date" }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get agent verification list
    if (typeof getAgentVerificationList !== "undefined") {
        agentVerificationTable = $("#agentVerificationTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getAgentVerificationList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "profile", name: "profile" },
                {
                    data: "phone",
                    name: "phone",
                    orderable: false,
                    searchable: false
                },
                { data: "location", name: "	location" },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get diagnostics list
    if (typeof getDiagnosticsList !== "undefined") {
        diagnosticsTable = $("#diagnosticsTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getDiagnosticsList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "phone", name: "phone" },
                { data: "email", name: "email" },
                { data: "locality", name: "locality" },
                { data: "reg_date", name: "reg_date" }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get diagnostics verification list
    if (typeof getDiagnosticsVerificationList !== "undefined") {
        diagnosticsVerificationTable = $(
            "#diagnosticsVerificationTable"
        ).DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getDiagnosticsVerificationList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "profile", name: "profile" },
                {
                    data: "phone",
                    name: "phone",
                    orderable: false,
                    searchable: false
                },
                { data: "location", name: "	location" },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get user bank account list
    if (typeof getUserBankAccountList !== "undefined") {
        userBankAccountTable = $("#userBankAccountTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getUserBankAccountList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "bank_name", name: "bank_name" },
                { data: "account_number", name: "account_number" },
                { data: "ifsc_code", name: "ifsc_code" },
                { data: "account_type", name: "account_type" },
                { data: "beneficiary_name", name: "beneficiary_name" }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }

    //get user bank account verification list
    if (typeof getBankAccountVerificationList !== "undefined") {
        userBankAccountVerificationTable = $(
            "#userBankAccountVerificationTable"
        ).DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getBankAccountVerificationList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "bank_name", name: "bank_name" },
                { data: "account_number", name: "account_number" },
                { data: "ifsc_code", name: "ifsc_code" },
                { data: "account_type", name: "account_type" },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }
});

//view user
function checkDoctorDetail(id) {
    if (typeof getDoctorDetailUrl !== "undefined") {
        var url = getDoctorDetailUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    dataTableModal.html(response.html);
                    dataTableModal.modal("toggle");
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

//verified user
function verifyDoctorDetail($id, $action) {
    var id = $id;
    var action = $action;
    var rejectMessage = document.getElementById("reject-message");
    var message = rejectMessage.value;
    if (action == "reject") {
        var valid = rejectMessage.checkValidity();
        if (valid) {
            $document.find("#error-message").html("");
        } else {
            rejectMessage.focus();
            $document
                .find("#error-message")
                .html("please enter a reason for disapproval");
            return false;
        }
    }
    if (typeof verifyDoctorDetailUrl !== "undefined") {
        swal({
                html: true,
                title: "Request",
                text: "Are you sure ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes!",
                closeOnConfirm: true,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    headers: header,
                    type: "POST",
                    dataType: "json",
                    url: verifyDoctorDetailUrl,
                    data: { id: id, action: action, message: message },
                    success: function(data) {
                        dataTableModal.modal("toggle");
                        doctorVerificationTable.draw();
                    },
                    error: function() {
                        //
                    }
                });
            }
        );
    }
}

//view user
function checkAgentDetail(id) {
    if (typeof getAgentDetailUrl !== "undefined") {
        var url = getAgentDetailUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    dataTableModal.html(response.html);
                    dataTableModal.modal("toggle");
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

//verified user
function verifyAgentDetail($id, $action) {
    var id = $id;
    var action = $action;
    var rejectMessage = document.getElementById("reject-message");
    var message = rejectMessage.value;
    if (action == "reject") {
        var valid = rejectMessage.checkValidity();
        if (valid) {
            $document.find("#error-message").html("");
        } else {
            rejectMessage.focus();
            $document
                .find("#error-message")
                .html("please enter a reason for disapproval");
            return false;
        }
    }
    if (typeof verifyAgentDetailUrl !== "undefined") {
        swal({
                html: true,
                title: "Request",
                text: "Are you sure ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes!",
                closeOnConfirm: true,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    headers: header,
                    type: "POST",
                    dataType: "json",
                    url: verifyAgentDetailUrl,
                    data: { id: id, action: action, message: message },
                    success: function(data) {
                        dataTableModal.modal("toggle");
                        agentVerificationTable.draw();
                    }
                });
            }
        );
    }
}

//view user
function checkDiagnosticsDetail(id) {
    if (typeof getDiagnosticsDetailUrl !== "undefined") {
        var url = getDiagnosticsDetailUrl.replace(":slug", id);
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    dataTableModal.html(response.html);
                    dataTableModal.modal("toggle");
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

//verified user
function verifyDiagnosticsDetail($id, $action) {
    var id = $id;
    var action = $action;
    var rejectMessage = document.getElementById("reject-message");
    var message = rejectMessage.value;
    if (action == "reject") {
        var valid = rejectMessage.checkValidity();
        if (valid) {
            $document.find("#error-message").html("");
        } else {
            rejectMessage.focus();
            $document
                .find("#error-message")
                .html("please enter a reason for disapproval");
            return false;
        }
    }
    if (typeof verifyDiagnosticsDetailUrl !== "undefined") {
        swal({
                html: true,
                title: "Request",
                text: "Are you sure ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes!",
                closeOnConfirm: true,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    headers: header,
                    type: "POST",
                    dataType: "json",
                    url: verifyDiagnosticsDetailUrl,
                    data: { id: id, action: action, message: message },
                    success: function(data) {
                        dataTableModal.modal("toggle");
                        diagnosticsVerificationTable.draw();
                    }
                });
            }
        );
    }
}

//verified user
function verifyBankAccountDetail($id, $action) {
    var id = $id;
    var action = $action;
    if (typeof verifyBankAccountUrl !== "undefined") {
        swal({
                html: true,
                title: "Request",
                text: "Are you sure ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes!",
                closeOnConfirm: true,
                showLoaderOnConfirm: true
            },
            function() {
                $.ajax({
                    headers: header,
                    type: "POST",
                    dataType: "json",
                    url: verifyBankAccountUrl,
                    data: { id: id, action: action },
                    success: function(data) {
                        if (data.status == 200) {
                            userBankAccountVerificationTable.draw();
                            toastrAlert(
                                "success",
                                "Bank Verify",
                                data.message
                            );
                        } else {
                            toastrAlert(
                                "error",
                                "Bank Verify",
                                data.message
                            );
                        }
                    },
                    error: function($data) {
                        toastrAlert(
                            "error",
                            "Bank Verify",
                            data.message,
                            "bottomCenter"
                        );
                    }
                });
            }
        );
    }
}