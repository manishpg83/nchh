$document = $(document);
$document.ready(function () {
    $.ajaxSetup({
        headers: header
    });
    
    if(typeof getNotificationList !== 'undefined'){
        notificationTable = $('#notificationTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: getNotificationList,
            columns: [
                {data: 'id', sortable: false, searchable: false, visible: false},
                {data: 'user', name: 'user'},
                {data: 'title', name: 'title'},
                {data: 'type', name: 'type'},
                //{data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            drawCallback: function() {
                //init_switch_reload();
            }
        });
    }
    
    notificationModal.on('hidden.bs.modal', function () {
        
    });
});