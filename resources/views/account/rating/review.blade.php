@forelse($profile->ratings as $key => $r)
<li class="list-group-item">
    <div class="row card-body">
        <div class="col-md-1 p-0"><img src="{{$r->user->profile_picture}}" class="rounded-circle img-60"></div>
        <div class="col-md-9 p-0">
            <div class="card-title mb-1">
                <span class="font-weight-bold">{{$r->user->name}}</span>
                <span class="bullet"></span><small>{{$r->created_at->diffForHumans()}}</small>
            </div>
            <div class="font-14px m-0">{{$r->review}}</div>
            <div class="rating_box" data-rating="{{$r->rating}}"></div>
        </div>
    </div>
</li>
@empty
<h5 class="text-warning m-5 text-center">No Record Found.</h5>
@endforelse