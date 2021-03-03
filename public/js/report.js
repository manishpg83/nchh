$document = $(document);
$document.ready(function () {
	$.ajaxSetup({
		headers: header
	});

	if(typeof getReportList !== 'undefined'){
		reportTable = $('#reportTable').DataTable({
			processing: true,
			serverSide: true,
			responsive: true,
			ajax: getReportList,
			columns: [
			{data: 'id', sortable: false, searchable: false, visible: false},
			{data: 'title', name: 'title'},
			{data: 'name', name: 'name'},
			{data: 'image', name: 'image'},
			{data: 'date', name: 'date'},
			{data: 'action', name: 'action', orderable: false, searchable: false},
			],
			drawCallback: function() {
					//init_switch_reload();
				}
			});
	}

	driveModal.on('hidden.bs.modal', function () {
		
	});
});

function addReport(){
	if(typeof addReportUrl !== 'undefined'){
		$.ajax({
			url: addReportUrl,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					driveModal.html(response.html);
					driveModal.modal('toggle');
					init_report_form();
				}else{
					//
				}
			},error:function(){
				//
			}
		})
	}
}

//edit report
function editReport(id){
	reportForm = $document.find('#reportForm');
	if(typeof editReportUrl !== 'undefined'){
		var url = editReportUrl.replace(':slug',id);
		$.ajax({
			url: url,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					driveModal.html(response.html);
					driveModal.modal('toggle');
					init_report_form();
				}else{
					//
				}
			},error:function(){
				//
			}
		})
	}
}

//view blog
function viewReport(id){
	reportForm = $document.find('#reportForm');
	if(typeof viewFullReport !== 'undefined'){
		var url = viewFullReport.replace(':slug',id);
		$.ajax({
			url: url,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					driveModal.html(response.html);
					driveModal.modal('toggle');
				}else{
					//
				}
			},error:function(){
				//
			}
		})
	}
}

function deleteReport(id){
	if(typeof deleteReportUrl !== 'undefined'){
		var url = deleteReportUrl.replace(':slug',id);
		swal({
			html : true,
			title: "Delete",
			text: "Are you sure you want to delete this report ?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Yes, delete it!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
		}, function () {
			$.ajax({
				url: url,
				type: 'DELETE',
				dataType: "JSON",
				data: {
					"id": id,
				},
				success: function (data){  
					reportTable.draw();
				//
			},error:function(){
				//
			}
		});
		});
	}
}

function init_report_form(){
	reportForm = $document.find('#reportForm');

	//Jquery validation of form field
	reportForm.validate({
		ignore: [],
		rules: {
			title: "required",
			name: "required",
			date: "required",
			type: "required",
		},
		messages: {
			title: "Please enter title",
			name: "Please enter name",
			date: "Please select date",
			type: "Please select a record type.",
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
					reportForm.find('.btn-submit').addClass('btn-disabled');
					reportForm.find('.close-button').addClass('btn-disabled');
				},
				success:function(data){
					if(data.status == 200){
						driveModal.modal('toggle');
						reportForm.trigger("reset");
						reportTable.draw();
					}else{
					}
				},
				error:function(){
					//
				}, complete: function () {
					reportForm.find('.btn-submit').removeClass('btn-disabled');
					reportForm.find('.close-button').removeClass('btn-disabled');
				}
			});
		}
	});

}