<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
		<div class="modal-header pt-2 pb-2">
			<h5 class="modal-title" id="modellabel">{{$title}}</h5>
		</div>
		<form id="reportForm" action="{{route('report.update',$report->id)}}" method="post" enctype="multipart/form-data">
			<input type="hidden" name="_method" value="PUT">	
			<div class="modal-body">
				<div class="card-body">
					<div class="form-group row">
						<div class="col-sm-4 mb-2">
							<label for="image">Image </label>
							<input type="file" name="image" class="form-control" id="image" placeholder="upload profile picture">
							<span class="text-danger">
								<strong id="image-error"></strong>
							</span>	
						</div>
						<div class="col-sm-3 mb-2">
							<div id="imagePreview">
								<img src="@if(!empty($report->image_name)){{$report->image}} @else {{asset('images/d_default.png')}} @endif" class="imagePreview thumbnail w-100 pt-2" id="preview"/>
							</div>
						</div>
						<div class="col-sm-12 mb-2">
							<label for="title">Title</label>
							<input type="text" name="title" value="{{$report->title}}" class="form-control" id="title" placeholder="Enter article title">
							<span class="text-danger">
								<strong id="title-error"></strong>
							</span> 
						</div>
						<div class="col-sm-6 mb-2">
							<label for="name">Name</label>
							<input type="text" name="name" value="{{$report->name}}" class="form-control" id="name" placeholder="Enter article title">
							<span class="text-danger">
								<strong id="name-error"></strong>
							</span> 
						</div>
						<div class="col-sm-4 mb-3">
							<label for="date">Date</label>
							<input class="form-control" type="date" name="date" id="date" placeholder="Select date" value="{{$report->date}}">
						</div>
						<div class="col-sm-12 mb-2">
							<p>Type of report</p>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="report" value="report" @if($report->type == 'report') checked @endif>
								<label class="form-check-label" for="report">Report</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="prescription" value="prescription" @if($report->type == 'prescription') checked @endif>
								<label class="form-check-label" for="prescription">Prescription</label>
							</div>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="invoice" value="invoice" @if($report->type == 'invoice') checked @endif>
								<label class="form-check-label" for="invoice">Invoice</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success btn-submit"><i id="loader" class=""></i>Submit</button>
				<button type="button" class="btn btn-secondary close-button" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
create