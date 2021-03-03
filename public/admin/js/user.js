$document = $(document);
$document.ready(function () {
	$.ajaxSetup({
		headers: header
	});
	
	//get user list 
	if(typeof getUserList !== 'undefined'){
		userTable = $('#userTable').DataTable({
			processing: true,
			serverSide: true,
			responsive: true,
			ajax: {
				url: getUserList,
				data: function (d) {
					d.role = $('select[name=role]').val();
				}
			},
			columns: [
				{data: 'id', sortable: false, searchable: false, visible: false},
				{data: 'name', name: 'name'},
				{data: 'phone', name: 'phone'},
				{data: 'email', name: 'email'},
				{data: 'role', name: 'role'},
				{data: 'action', name: 'action', orderable: false, searchable: false},
			],
			drawCallback: function() {
				//init_switch_reload();
			}
		});
	}
	$('select[name=role]').change(function(e) {
		userTable.draw();
	});
	//get user verification list
	if(typeof getUserVerificationList !== 'undefined'){
		verificationTable = $('#verificationTable').DataTable({
			processing: true,
			serverSide: true,
			responsive: true,
			ajax: getUserVerificationList,
			columns: [
				{data: 'id', sortable: false, searchable: false, visible: false},
				{data: 'name', name: 'name'},
				{data: 'profile', name: 'profile'},
				{data: 'phone', name: 'phone', orderable: false, searchable: false},
				{data: 'location', name: '	location'},
				{data: 'action', name: 'liecence_action', orderable: false, searchable: false},
			],
			drawCallback: function() {
				//init_switch_reload();
			}
		});
	}
	
});

//view user
function viewUser(id){
	if(typeof viewFullUser !== 'undefined'){
		var url = viewFullUser.replace(':slug',id);
		$.ajax({
			url: url,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					userModal.html(response.html);
					userModal.modal('toggle');
				}else{
					//
				}
			},error:function(){
				//
			}
		})
	}
}

//edit user
function editUser(id){
	userForm = $document.find('#userForm');
	if(typeof editUserUrl !== 'undefined'){
		var url = editUserUrl.replace(':slug',id);
		$.ajax({
			url: url,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					userModal.html(response.html);
					userModal.modal('toggle');
					$(".select2").select2();
				}
			},error:function(){
				//
			}
		})
	}
}

//delete user
function deleteUser(id){
	if(typeof deleteUserUrl !== 'undefined'){
		var url = deleteUserUrl.replace(':slug',id);
		swal({
			html : true,
			title: "Delete",
			text: "Are you sure you want to delete this user ?",
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
					userTable.draw();
					//
				},error:function(){
					//
				}
			});
		});
	}
}

function browsePicture() {
	fileupload = $document.find("#profile_picture");
	fileupload.click();
	fileupload.change(function () {
		readURL(this, 'previewPicture');
	});
}

function submitUserProfileForm(btn_id) {
	userProfile = $document.find('#userForm');
	userProfile.validate({
		rules: {
			name: "required",
			pincode: {
				zipcode: true
			} 
		},
		messages: {
			name: "Please enter your name",
		},
		submitHandler: function (form) {
			var action = $(form).attr('action');
			var formData = new FormData($(form)[0]);
			$.ajax({
				type: 'POST',
				url: action,
				data: formData,
				processData: false,
				dataType: 'json',
				contentType: false,
				beforeSend: function () {
					btn_id.addClass('btn-progress disabled');
				},
				success: function (res) {
					if (res.status === 200) {
						userModal.modal('toggle');
						userTable.draw();
					}
					toastrAlert('success', 'Profile', res.message)
				},
				error: function (res) {
					toastrAlert('error', 'Profile', res.message)
				}, complete: function () {
					btn_id.removeClass('btn-progress disabled');
					$document.find('.profile-widget-item-value').load(' .profile-widget-item-value');
				}
			});
		}
	});
}

function removeUserPicture(id = '', btn_id) {
	if (typeof remove_picture_url !== "undefined") {
		if (id) {
		}
		var url = remove_picture_url.replace(':slug', id);
		$.ajax({
			type: 'GET',
			url: url,
			processData: false,
			dataType: 'json',
			contentType: false,
			beforeSend: function () {
				$(btn_id).addClass('btn-progress disabled');
			},
			success: function (res) {
				if (res.status === 200) { 
					$("#previewPicture").attr('src', res.result.profile_picture);
				}
				toastrAlert('success', 'Profile', res.message)
			},
			error: function (res) {
				toastrAlert('error', 'Profile', res.message)
			}, complete: function () {
				$(btn_id).removeClass('btn-progress disabled');
				$document.find('#reloadProfile').load(' #reloadProfile');
			}
		});
	}
	
}

//view user
function checkUserDetail(id){
	if(typeof getUserDetailUrl !== 'undefined'){
		var url = getUserDetailUrl.replace(':slug',id);
		$.ajax({
			url: url,
			type: 'get',
			dataType:'json',
			success:function(response)  
			{	
				if(response.status == 200){
					verificationModal.html(response.html);
					verificationModal.modal('toggle');
				}else{
					//
				}
			},error:function(){
				//
			}
		})
	}
}


//verified user
function verifyUserDetail($id,$action){
	var id = $id;
	var action = $action;
	var rejectMessage = document.getElementById("reject-message");
	var message = rejectMessage.value;
	if(action == 'reject'){
		var valid = rejectMessage . checkValidity();
		if (valid) { 
			$document.find("#error-message").html('');	
		}else{
			rejectMessage.focus();
			$document.find("#error-message").html('please enter a reason for disapproval');
			return false;
		} 
	}
	if(typeof verifyUserDetailUrl !== "undefined"){
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
				url: verifyUserDetailUrl,
				data: {'id': id, 'action': action, 'message': message},
				success: function(data){
					verificationModal.modal('toggle');
					verificationTable.draw();
				}
			});
		});
	}
}
