$document = $(document);
$document.ready(function() {

    /*Pass csrf token for every ajax call*/
    $.ajaxSetup({ headers: header });

    if (typeof payPaymentList !== "undefined" && $('#paymentTable').length > 0) {
        paymentTable = $('#paymentTable').DataTable({
            // dom: "<'row'<'col-xs-12 col-lg-12't>><'row'<'col-lg-6'i><'col-lg-6'p>>",
            responsive: true,
            processing: true,
            serverSide: true,
            /*ajax: courseList,*/
            ajax: {
                url: payPaymentList,
                data: function(d) {
                    // d.search = $('input[type=search]').val();
                }
            },
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'appointment_with', width: '15%' },
                { data: 'order_no', width: '15%' },
                { data: 'invoice_id', width: '15%' },
                { data: 'receipt_id' },
                { data: 'order_id' },
                { data: 'payment_id' },
                { data: 'txn_date' },
                { data: 'payable_amount' },
                { data: 'discount' },
                { data: 'refund_amount' },
                { data: 'status' },
                { data: 'action', orderable: false, searchable: false }
            ],
            initComplete: function() {
                init_tooltip();
            },
            drawCallback: function() {
                init_tooltip();
            }
        });
    }

    if (typeof receivedPaymentList !== "undefined" && $('#paymentTable').length > 0) {
        paymentTable = $('#paymentTable').DataTable({
            // dom: "<'row'<'col-xs-12 col-lg-12't>><'row'<'col-lg-6'i><'col-lg-6'p>>",
            responsive: true,
            processing: true,
            serverSide: true,
            /*ajax: courseList,*/
            ajax: {
                url: receivedPaymentList,
                data: function(d) {
                    // d.search = $('input[type=search]').val();
                }
            },
            columns: [
                { data: 'id', sortable: false, searchable: false, visible: false },
                { data: 'patient', width: '25%' },
                { data: 'invoice_id', width: '10%' },
                { data: 'receipt_id', width: '10%' },
                { data: 'order_id', width: '10%' },
                { data: 'payment_id', width: '10%' },
                { data: 'txn_date', width: '10%' },
                { data: 'payable_amount', width: '10%' },
                { data: 'discount', width: '10%' },
                { data: 'refund_amount', width: '10%' },
                { data: 'status', width: '10%' },
                { data: 'action', orderable: false, searchable: false }
            ],
            initComplete: function() {
                init_tooltip();
            },
            drawCallback: function() {
                init_tooltip();
            }
        });
    }
});

function viewPayment(id) {
    if (typeof viewPaymentDetailUrl !== 'undefined') {
        url = viewPaymentDetailUrl.replace(':slug', id);
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'JSON',
            success: function(res) {
                paymentModel.html(res.html);
                paymentModel.modal('toggle');
            },
            error: function() {
                toastrAlert('error', 'Payment Details', res.message, 'bottomCenter')
            }
        })
    }
}