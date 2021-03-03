 $document = $(document);
 $document.ready(function() {
     $.ajaxSetup({
         headers: header
     });

     if (typeof getNotificationList !== "undefined") {
         notificationTable = $("#notificationTable").DataTable({
             processing: true,
             serverSide: true,
             responsive: true,
             ajax: getNotificationList,
             columns: [{
                     data: "id",
                     sortable: false,
                     searchable: false,
                     visible: false
                 },
                 { data: "sender_by", name: "sender_by", width: '20%' },
                 { data: "title", name: "title", width: '35%' },
                 { data: "message", name: "message", width: '35%' },
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

     notificationModal.on("hidden.bs.modal", function() {});
 });

 function staffInvitationReply(id, action, notification_id) {
     var reply = (action == 1) ? 'accept' : 'reject';
     var action_id = id;
     if (typeof staffInvitationReplyUrl != "undefined") {
         $.ajax({
             type: "POST",
             url: staffInvitationReplyUrl,
             data: { 'action_id': action_id, 'action': reply, 'notification_id': notification_id },
             dataType: "JSON",
             beforeSend: function() {},
             success: function(data) {
                 notificationTable.draw();
             },
             error: function() {
                 //
             },
             complete: function() {

             }
         });
     }
 }