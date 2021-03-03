$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getPrescriptionList !== "undefined") {
        prescriptionTable = $("#prescriptionTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getPrescriptionList,
            columns: [
                { data: "prescription_id", name: "prescription_id", width: '5%' },
                { data: "patient", name: "patient", width: '25%' },
                { data: "doctor", name: "doctor", width: '25%' },
                { data: "practice", name: "practice", width: '25%' },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    width: '5%'
                }
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }
});