@if($appointment->files)
<div class="gallery">
<div class="row col-md-12 chocolat-parent">
@foreach($appointment->files as $file)
<a class="chocolat-image col-1 p-1" href="{{$file->filename}}" title="">
<img src="{{$file->filename}}" class="img-thumbnail" width="100%">
</a>
@endforeach
</div>
</div>
@endif