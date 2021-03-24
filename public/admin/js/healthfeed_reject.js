var $document = $(document);
$document.ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
	if(typeof getHealthFeedRequestUrl !== 'undefined'){
		healthfeedRequestTable= $('#healthfeedRequestTable').DataTable({
			processing: true,
			serverSide: true,
			responsive: true,
			ajax: getHealthFeedRequestUrl,
			columns: [
				{data: 'id', sortable: false, searchable: false, visible: false},
				{data: 'title', name: 'title'},
				{data: 'image', name: 'image'},
				{data: 'action', name: 'action', orderable: false, searchable: false},
			],
			drawCallback: function() {
				init_reject_healthfeed_form();
			}
		});
	}
	healthfeedModal.on('hidden.bs.modal', function () {
		
	});
	
});

function rejectHealthFeed(id){
	healthfeedForm = $document.find('#healthfeedForm');
	if(typeof rejectHealthFeedUrl !== 'undefined'){
		var url = rejectHealthFeedUrl.replace(':slug',id);
		$.ajax({
			url: url,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					healthfeedModal.html(response.html);
					healthfeedModal.modal('toggle');
					init_reject_healthfeed_form();
				}else{
					//
				}
			},error:function(){
				//
			}
		})
	}
}

function init_reject_healthfeed_form(){
	healthfeedForm = $document.find('#healthfeedForm');
	//Jquery validation of form field
	healthfeedForm.validate({
		rules: {
			feedback_message: "required",
		},
		messages: {
			feedback_message: "Please enter message",
		},
		submitHandler: function(form){
			var action = $(form).attr('action');
			
			var formData = new FormData($(form)[0]);
			$.ajax({	
				type:'POST',
				url: action,
				data: formData,
				processData: false,
				dataType: 'json',
				contentType: false,
				beforeSend: function () {
					//
				},
				success:function(data){
					
					if(data.status == 200){
						healthfeedModal.modal('toggle');
						healthfeedForm.trigger("reset");
						healthfeedRequestTable.draw();
					}else{
						
					}
				},
				error:function(data){
					//
				}, complete: function () {
					//
				}
			});
		}
	});
}

//Change HealthFeed request
function requestHealthFeed(data){
	var id = data.id;
	var status = data.value;
	if(typeof changeStatusUrl !== "undefined"){
		swal({
			html : true,
			title: "Request",
			text: "Are you sure ?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Yes!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
		}, function () {
			$.ajax({
				headers: header,
				type: "POST",
				dataType: "json",
				url: changeStatusUrl,
				data: {'status': status, 'id': id,'message':null},
				success: function(data){
					healthfeedRequestTable.draw();
				}
			});
		});
	}
}

//view blog
function viewHealthFeed(id){
	if(typeof viewFullHealthFeed !== 'undefined'){
		var url = viewFullHealthFeed.replace(':slug',id);
		$.ajax({
			url: url,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					healthfeedModal.html(response.html);
					healthfeedModal.modal('toggle');
				}else{
					//
				}
			},error:function(){
				//
			}
		})
	}

}