$document = $(document);
$document.ready(function() {
    $.ajaxSetup({
        headers: header
    });

    if (typeof getWalletList !== "undefined") {
        walletTable = $("#walletTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getWalletList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "name", name: "name" },
                { data: "date", name: "date" },
                { data: "price", name: "price" },
                { data: "status", name: "status" }
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }
    if (typeof getWithdrawHistoryList !== "undefined") {
        withdrawHistoryTable = $("#withdrawHistoryTable").DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getWithdrawHistoryList,
            columns: [{
                    data: "id",
                    sortable: false,
                    searchable: false,
                    visible: false
                },
                { data: "transfer_id", name: "transfer_id" },
                { data: "amount", name: "amount" },
                { data: "currency", name: "currency" },
                { data: "date", name: "date" },
            ],
            drawCallback: function() {
                $("[data-toggle='tooltip']").tooltip();
            }
        });
    }
});

function withdrawBalance() {
    if (typeof withdrawBalanceUrl !== "undefined") {
        $.ajax({
            url: withdrawBalanceUrl,
            type: "get",
            dataType: "json",
            success: function(response) {
                if (response.status == 200) {
                    toastrAlert("success", "Withdraw Balance", response.message);
                    $document.find("#total_balance").html(response.total_balance);
                    $document.find("#withdrawable_balance").html(response.withdrawable_balance);
                } else {
                    toastrAlert("error", "Withdraw Balance", response.message);
                }
                walletTable.draw();
            },
            error: function(response) {
                toastrAlert("error", "Withdraw Balance", response.message);
                walletTable.draw();
            }
        });
    }
}