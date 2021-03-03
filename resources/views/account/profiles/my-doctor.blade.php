@extends('account.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            @forelse($user->wishlist as $w)
            <div class="col-12 col-md-4 col-lg-4">
                <article class="article article-style-c">
                    <div class="article-header">
                        <div class="article-image" data-background="{{$w->doctor->profile_picture}}">
                            <a href="javascript:;" onclick="myWishlist('{{$w->doctor->id}}');" class="wishlist" id="isWishlist_{{$w->doctor->id}}">
                                @if($w->doctor->isWishlist($w->doctor->id) > 0) <i class="fas fa-star" data-toggle="tooltip" title="Remove from favorite"></i>@else <i class="far fa-star" data-toggle="tooltip" title="Add to favorite"></i> @endif
                            </a>
                        </div>
                    </div>
                    <div class="article-details">
                        <div class="article-title">
                            <h5><a class="f-18" href="{{$w->doctor->doctor_profile_url}}">{{$w->doctor->name}}</a></h5>
                        </div>

                        @if(isset($w->doctor->detail->degree))
                        <div class="f-15 text-dark w-100">{{$w->doctor->detail->degree}}</div>
                        @endif
                        @if(isset($w->doctor->detail->specialty_name))
                        <div class="f-13 text-dark w-100">{{$w->doctor->detail->specialty_name}}</div>
                        @endif
                        @if(isset($w->doctor->detail->experience))
                        <div class="f-13 text-dark w-100">{{$w->doctor->detail->experience}} {{$w->doctor->detail->experience > 1 ? 'Years of experience overall' : 'Year of experience overall' }}</div>
                        @endif
                        <div id="avg_rating_box_{{$w->doctor->id}}" class="rating_box_{{$w->doctor->id}} rating_box mt-1" data-rating="{{$w->doctor->average_rating}}"></div>
                        @if(isRateable($w->doctor->id))
                        @if(isFirstTimeRating($w->doctor->id))
                        <a href="javascript:;" onclick="addReview({{$w->doctor->id}})" class="small"><i class="fas fa-star-half-alt"></i> edit review</a>
                        @else
                        <a href="javascript:;" onclick="addReview({{$w->doctor->id}})" class="small"><i class="fas fa-star-half-alt"></i> <span id="add_Review"> add review</span></a>
                        @endif
                        <a href="{{route('chat.private',[$w->doctor->id])}}" class="text-success small ml-1" target="_blank"><i class="far fa-comments"></i></i> chat</a>
                        @endif
                        <div class="article-cta mt-2">
                            @if($w->doctor->id != Auth::id() && checkPermission(['doctor','patient','agent']))
                            @if($w->doctor->setting->consultant_as == 'INPERSON' || $w->doctor->setting->consultant_as == 'BOTH')
                            <a href="{{route('appointment.index',[$w->doctor->id,$w->doctor->name_slug])}}" class="btn btn-primary btn-sm center">Book Appointment</a>
                            @endif
                            @if($w->doctor->setting->consultant_as == 'ONLINE' || $w->doctor->setting->consultant_as == 'BOTH')
                            <a href="{{route('appointment.online_consult',[$w->doctor->id,$w->doctor->name_slug])}}" class="btn btn-outline-primary btn-sm center">Video Consultation</a>
                            @endif
                            @endif
                        </div>
                    </div>
                </article>
            </div>
            @empty
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <h5 class="text-warning m-5 text-center">No Record Found.</h5>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>
<!-- Modal -->
<div class="modal fade" id="globalModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script type="text/javascript">
    var globalModal = $('#globalModal');

    $(".rating_box").starRating({
        starSize: 20,
        useFullStars: true,
        readOnly: true
    });

    var manageWishlistUrl = "{{Route('user.manage.wishlist')}}";
    var addReviewUrl = "{{Route('account.rating.create')}}";
</script>
@endsection