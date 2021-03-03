<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
		<div class="modal-header pt-2 pb-2">
			<h5 class="modal-title" id="modellabel">{{$report->name}}</h5>
			<span class="float-right">{{$report->date}}</span>
		</div>
		<div class="modal-body">
			<div class="container-fluid">
				<div class="row mt-4 text-center">
					<div class="col-sm-12">
						<h5 class="card-title">{{$report->title}}</h5>
						<h6 class="card-subtitle mb-2 text-muted">
							<span class="badge badge-pill badge-secondary ml-2" title="Type of report">{{$report->type}}</span>
						</h6>
					</div>
					<div class="col-sm-12">
						<img class="img-fluid circle" src="@if(!empty($report->image_name)){{$report->image}} @else {{asset('images/d_default.png')}} @endif" alt="bg-img">
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>
