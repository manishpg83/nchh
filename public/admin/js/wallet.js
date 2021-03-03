$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    //get user list 
    if (typeof getUserWalletList !== 'undefined') {
        walletTable = $('#walletTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: getUserWalletList,
                data: function(d) {
                    d.user_id = $('select[name=user_name]').val();
                }
            },
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: "name", name: "name" },
                { data: "patient_name", name: "patient_name" },
                { data: "date", name: "date" },
                { data: "price", name: "price" },
                { data: "status", name: "status" }
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }
    $('select[name=user_name]').change(function(e) {
        walletTable.draw();
    });
});