@extends('layouts.app')

@section('content')
<section class="bg-grey padding pt-5">
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="col-md-9 col-sm-8">

                <div class="card">
                    <div class="row no-gutters content-section">
                        <div class="col-md-3">
                            <img class="card-img" src="{{$profile->profile_picture}}" alt="{{$profile->name}}">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body">
                                <h5 class="card-title mb-1">{{$profile->name}}</h5>
                                <h6 class="font-13px text-secondary mb-1">{{isset($profile->detail->Specialties) && ($profile->detail->Specialties->count() > 0) ? "Multi-speciality Clinic" : ''}}</h6>


                                @php
                                if($profile->role->keyword == "doctor"){
                                $practice = !empty($profile->practice->count()) ? $profile->practice : [];
                                }else{
                                $practice = ($profile->practiceAsStaff->count() > 0) ? $profile->practiceAsStaff : [];
                                }
                                @endphp

                                <div class="clinic_details font-14px mt-2">
                                    @if(!empty($practice))
                                    <strong class="mb-2">{{$practice[0]->locality.', '.$practice[0]->city}}</strong>
                                    @endif
                                    <div class="text-mute">{!!$profile->full_address!!}</div>

                                    <div id="avg_rating_box_{{$profile->id}}" class="rating_box_{{$profile->id}}" data-rating="{{$profile->average_rating}}"></div>
                                    @if(isRateable($profile->id))
                                    @if(isFirstTimeRating($profile->id))
                                    <a href="javascript:;" onclick="addReview(`{{$profile->id}}`)" class="small"><i class="fas fa-star-half-alt"></i> edit review</a>
                                    @else
                                    <a href="javascript:;" onclick="addReview(`{{$profile->id}}`)" class="small"><i class="fas fa-star-half-alt"></i> <span id="add_Review"> add review</span></a>
                                    @endif
                                    @endif
                                </div>
                            </div>
                            @if(checkPermission(['doctor','patient','agent']))
                            @if($profile->practiceAsStaff->count() > 0)
                            <a href="{{route('diagnostics.service.appointment',[$profile->id,$profile->name_slug])}}" class="btn btn-primary btn-sm float-right mr-2 mt-5"><i class="far fa-comments"></i> Book Appointment</a>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-2 content-section">

                    <div class="card-body m-0 p-0">

                        <ul class="nav nav-tabs neucrad_tab" id="detailTab" role="tablist">
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link font-weight-bold active" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="false">About</a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link font-weight-bold" id="review-tab" data-toggle="tab" href="#reviewTab" role="tab" aria-controls="contact" aria-selected="true">Reviews (<span id="totalRatingCount">{{$profile->total_rating}}</span>)</a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link font-weight-bold" id="service-tab" data-toggle="tab" href="#service" role="tab" aria-controls="service" aria-selected="true">Services ({{$profile->services->count()}})</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade active show" id="about" role="tabpanel" aria-labelledby="about-tab">
                                <div class="card border-top-0">
                                    <div class="card-body">
                                        <h5 class="card-title mb-1">About {{$profile->name}}</h5>
                                        @if(isset($profile->detail->about))
                                        <p class="font-15px">{{$profile->detail->about}}</p>
                                        @endif

                                        @if(!empty($profile->gallery) && $profile->gallery->count() > 0)
                                        <div class="gallary">
                                            <h6 class="mt-1 font-14px">Photos</h6>
                                            <div class="row col-md-12 chocolat-parent">
                                                @foreach($profile->gallery as $gallery)
                                                <a class="chocolat-image col-1 p-1" href="{{$gallery->image}}" title="{{$profile->name}}">
                                                    <img src="{{$gallery->image}}" class="img-thumbnail" width="100%">
                                                </a>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="reviewTab" role="tabpanel" aria-labelledby="review-tab">
                                @if(!empty($profile->ratings))
                                <div class="card border-top-0">
                                    <ul class="list-group list-group-flush review-list">
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
                                    </ul>
                                </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="service" role="tabpanel" aria-labelledby="service-tab">
                                @if(!empty($profile->services))
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="list-group">
                                            <div class="row">
                                                @forelse($profile->services as $key => $s)
                                                <div class="col-sm-3">
                                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-1" title="{{$s->information}}" data-toggle="tooltip">
                                                        <span>{{$s->name}}</span>
                                                        <span class="badge badge-primary badge-pill mw-50"><i class="fas fa-rupee-sign"></i> {{$s->price}}</span>
                                                    </li>
                                                </div>
                                                @empty
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    No Any Record Found.
                                                </li>
                                                @endforelse
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection
@section('page_script')
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function(event) {
        Chocolat(document.querySelectorAll('.chocolat-parent .chocolat-image'))
    })

    $(".rating_box_{{$profile->id}}").starRating({
        starSize: 20,
        useFullStars: true,
        readOnly: true
    });

    $(".review-list").niceScroll({
        scrollspeed: 500,
    });
</script>

@endsection