$document = $(document);
$document.ready(function () {
	$.ajaxSetup({
		headers: header
	});
	
	if(typeof getHealthFeedList !== 'undefined'){
		healthfeedTable = $('#healthfeedTable').DataTable({
			processing: true,
			serverSide: true,
			responsive: true,
			ajax: getHealthFeedList,
			columns: [
				{data: 'id', sortable: false, searchable: false, visible: false},
				{data: 'title', name: 'title'},
				{data: 'image', name: 'image'},
				{data: 'status', name: 'status'},
				{data: 'action', name: 'action', orderable: false, searchable: false},
			],
			drawCallback: function() {
				$('[data-toggle="tooltip"]').tooltip()
			}
		});
	}
	
	healthfeedModal.on('hidden.bs.modal', function () {
		
	});
});

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
