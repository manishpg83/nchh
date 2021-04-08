@extends('layouts.app')

@section('content')
<section class="bg-grey padding pt-5 profile_container">
    <div class="container-fluid">
        <div class="row mt-5">

            <div class="col-12 col-md-8 m-0">

                <div class="card">
                    <div class="row no-gutters content-section">
                        <div class="col-md-3">
                            <img class="card-img" src="{{$profile->profile_picture}}" alt="{{$profile->name}}">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body">
                                <h5 class="card-title mb-1">{{$profile->name}}
                                    <a href="javascript:;" onclick="myWishlist('{{$profile->id}}');" class="wishlist" id="isWishlist_{{$profile->id}}">
                                        @if($profile->isWishlist($profile->id) > 0) <i class="fas fa-star" data-toggle="tooltip" title="Remove from favorite"></i>@else <i class="far fa-star" data-toggle="tooltip" title="Add to favorite"></i> @endif
                                    </a></h5>
                                @if(isset($profile->detail->degree))
                                <div class="font-15px text-dark w-100">{{$profile->detail->degree}}</div>
                                @endif
                                @if(isset($profile->detail->specialty_name))
                                <div class="font-13px text-dark w-100">{{$profile->detail->specialty_name}}</div>
                                @endif
                                @if(isset($profile->detail->experience))
                                <div class="font-13px text-dark w-100">{{$profile->detail->experience}} {{$profile->detail->experience > 1 ? 'Years of experience overall' : 'Year of experience overall' }}</div>
                                @endif
                                <div id="avg_rating_box_{{$profile->id}}" class="rating_box_{{$profile->id}} mt-1 font-13px text-dark" data-rating="{{$profile->average_rating}}">
                                   <span style="float:right;">@if($language) Language: {{ $language }}@endif 
                                </div>

                                @if(isRateable($profile->id))
                                @if(isFirstTimeRating($profile->id))
                                <a href="javascript:;" onclick="addReview(`{{$profile->id}}`)" class="small"><i class="fas fa-star-half-alt"></i> edit review</a>
                                @else
                                <a href="javascript:;" onclick="addReview(`{{$profile->id}}`)" class="small"><i class="fas fa-star-half-alt"></i> <span id="add_Review"> add review</span></a>
                                @endif
                                @endif
                                @if(isset($profile->detail->about))
                                <div class="font-13px text-dark w-100">
                                    {{$profile->detail->about}}
                                </div>
                                @endif
                            </div>
                            <div class="card-footer bottom_sticky text-right border-0">
                                 @if(Auth::id() && $profile->id != Auth::id() && checkPermission(['doctor','patient','agent']))
                                @if($profile->setting->consultant_as == 'INPERSON' || $profile->setting->consultant_as == 'BOTH')
                                <a href="{{route('appointment.index',[$profile->id,$profile->name_slug])}}" class="btn btn-primary btn-sm"><i class="far fa-comments"></i> Book Appointment</a>
                                @endif
                                @if($profile->setting->consultant_as == 'ONLINE' || $profile->setting->consultant_as == 'BOTH')
                                <a href="{{route('appointment.online_consult',[$profile->id,$profile->name_slug])}}" class="btn btn-outline-primary btn-sm"><i class="far fa-comments"></i> Video Consultation</a>
                                @endif
                                @endif 
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-2 content-section">

                    <div class="card-body m-0 p-0">

                        <ul class="nav nav-tabs neucrad_tab" id="detailTab" role="tablist">
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link font-weight-bold active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="false">Consultant At</a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link font-weight-bold" id="review-tab" data-toggle="tab" href="#reviewTab" role="tab" aria-controls="contact" aria-selected="true">Reviews (<span id="totalRatingCount">{{$profile->total_rating}}</span>)</a>
                            </li>
                            <li class="nav-item waves-effect waves-light">
                                <a class="nav-link font-weight-bold" id="healthfeed-tab" data-toggle="tab" href="#healthfeed" role="tab" aria-controls="healthfeed" aria-selected="true">Healthfeed</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade active show" id="info" role="tabpanel" aria-labelledby="info-tab">
                                <div class="card border-top-0">
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


                                    @foreach($profile->practice as $p)
                                    <div class="list-group-item border-top-0">
                                        <div class="row no-gutters">
                                            <div class="col-md-3">
                                                <img class="card-img" src="{{$p->logo}}" alt="{{$p->name}}">
                                            </div>
                                            <div class="col-md-9 col-sm-9">
                                                <div class="card-body ml-2 p-0 font-14px">
                                                    <div class="font-15px text-dark w-100">{{ucfirst($p->locality).', '.$p->city}}</div>
                                                    <a class="text-primary font-weight-bold" href="{{$p->addedBy->getProfileUrl($p->addedBy->role->keyword)}}">{{$p->name}}</a>
                                                    <div class="text-mute">{!!$profile->full_address!!}</div>

                                                    <div class="clinic_details font-14px mt-1">
                                                        <div class="text-success">₹{{$p->fees}} Consultation fee</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

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

                            <div class="tab-pane fade" id="healthfeed" role="tabpanel" aria-labelledby="healthfeed-tab">
                                @if(!empty($profile->healthfeed))
                                <ul class="list-group list-group-flush">
                                    @forelse($profile->healthfeed as $key => $feed)
                                    <li class="list-group-item p-0">
                                        <div class="row card-body">
                                            <div class="col-md-3 p-0 healthfeed-tab">
                                                @if($feed->video_url)
                                                {!! $feed->html_video_url !!}
                                                @else
                                                <img src="{{$feed->cover_photo}}" alt="{{$feed->title}}" class="w-100">
                                                @endif
                                            </div>
                                            <div class="col-md-9">
                                                <h6 class="m-0">{{$feed->title}}</h6>
                                                <div class="card-text f-sm">
                                                    <div class="text-secondary" style="font-size: 14px;">{!!$feed->short_content!!} <a href="{{$feed->url}}" class="text-primary">show more</a></div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @empty
                                    <h5 class="text-warning m-5 text-center">No Record Found.</h5>
                                    @endforelse
                                </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="card user_card mb-1">
                    <div class="row card-body m-0">
                        <div class="col-md-2 p-0"><img src="{{$profile->profile_picture}}" alt="" class="w-100"></div>
                        <div class="col-md-9">
                            <h5>{{$profile->name}}</h5>
                            <div class="card-text">
                                <h6 class="text-secondary">{{$profile->detail->degree}}</h6>
                                @if(!empty($profile->detail->specialty_id))
                                <h6 class="font-weight-normal m-0">{{$profile->detail->specialty_name}}</h6>
                                @endif
                                <small class="font-weight-normal m-0 text-dark">{{$profile->detail->experience > 1 ? $profile->detail->experience.' Years' : $profile->detail->experience.' Year'}} Experience Overall</small>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>

            <div class="col-12 col-md-4">
            </div>
        </div>
</section>
@endsection
@section('page_script')
<script type="text/javascript">
    var manageWishlistUrl = "{{Route('user.manage.wishlist')}}";
    $(".rating_box_{{$profile->id}}").starRating({
        starSize: 20,
        useFullStars: true,
        readOnly: true
    });
</script>
@endsection