@extends('layouts.app')

@section('content')
<section class="bg-grey padding pt-5 profile_container">
    <div class="container">
        <div class="row mt-5">
            <div class="col-12 col-md-8">
                <!-- <div class="card user_card mb-1">
                    <div class="row card-body m-0">
                        <div class="col-md-2 p-0"><img src="{{$profile->profile_picture}}" alt="" class="w-100"></div>
                        <div class="col-md-9">
                            <h5>{{$profile->name}}</h5>
                            <div class="card-text">
                                <h6 class="text-secondary">{{$profile->detail->degree}}</h6>
                                @if(!empty($profile->detail->specialty_ids))
                                <h6 class="font-weight-normal m-0">{{$profile->detail->specialty_name}}</h6>
                                @endif
                                <small class="font-weight-normal m-0 text-dark">{{$profile->detail->experience > 1 ? $profile->detail->experience.' Years' : $profile->detail->experience.' Year'}} Experience Overall</small>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="card user_card">
                    <div class="row card-body m-0 pt-2">

                        <ul class="nav mb-2" role="tablist">
                            <li role="presentation" class="mr-1 active">
                                <a href="#home" class="btn btn-primary text-white" aria-controls="home" role="tab" data-toggle="tab"><span>Clinic</span></a>
                            </li>

                            <li role="presentation" class="mr-1">
                                <a href="#review" class="btn btn-primary text-white" aria-controls="review" role="tab" data-toggle="tab">
                                    <span>Ratings</span></a>
                            </li>

                            <li role="presentation" class="mr-1">
                                <a href="#healthfeed" class="btn btn-primary text-white" aria-controls="healthfeed" role="tab" data-toggle="tab">
                                    <span>Health Feed</span></a>
                            </li>

                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content w-100">

                            <div role="tabpanel" class="tab-pane active" id="home">

                                @if(!empty($profile->practice))
                                <ul class="list-group list-group-flush">
                                    @forelse($profile->practice as $key => $practice)
                                    <li class="list-group-item p-0">
                                        <div class="row card-body">
                                            <!-- <div class="col-md-3 p-0"><img src="{{$practice->logo}}" alt="{{$practice->name}}" class="w-100"></div> -->
                                            <div class="col-md-9 p-0">
                                                <h6 class="m-0">{{ucfirst($practice->locality).', '.$practice->city}}</h6>
                                                <small class="text-primary">{{$practice->name}}</small>
                                                <div class="card-text f-sm">
                                                    <div>{!!$practice->full_address!!}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @empty
                                    <li class="list-group-item p-0">
                                        <h5 class="text-warning m-5 text-center">No Record Found.</h5>
                                    </li>
                                    @endforelse
                                </ul>
                                @endif

                            </div>

                            <div role="tabpanel" class="tab-pane" id="review">
                                <div class="">
                                    <h5 class="text-warning m-5 text-center">No Record Found.</h5>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="healthfeed">

                                @if(!empty($profile->healthfeed))
                                <ul class="list-group list-group-flush">
                                    @foreach($profile->healthfeed as $key => $feed)
                                    <li class="list-group-item p-0">
                                        <div class="row card-body">
                                            <div class="col-md-3 p-0"><img src="{{$feed->cover_photo}}" alt="{{$feed->title}}" class="w-100"></div>
                                            <div class="col-md-9">
                                                <h6 class="m-0">{{$feed->title}}</h6>
                                                <div class="card-text f-sm">
                                                    <div class="text-secondary" style="font-size: 14px;">{!!$feed->short_content!!} <a href="{{$feed->url}}" class="text-primary">show more</a></div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12 col-md-4">
                <div class="card mb-1">
                    <div class="row card-body m-0">
                        <div class="col-md-2 p-0"><img src="{{$profile->profile_picture}}" alt="" class="w-100"></div>
                        <div class="col-md-9">
                            <h5>{{$profile->name}}</h5>
                            <div class="card-text">
                                <h6 class="text-secondary f-sm">{{$profile->detail->degree}}</h6>
                                @if(!empty($profile->detail->specialty_ids))
                                <h6 class="font-weight-normal m-0 f-sm">{{$profile->detail->specialty_name}}</h6>
                                @endif
                                <small class="font-weight-normal m-0 text-dark">{{$profile->detail->experience > 1 ? $profile->detail->experience.' Years' : $profile->detail->experience.' Year'}} Experience Overall</small>
                            </div>
                        </div>
                        <div class="col-12 mt-3 p-0">
                            <a href="{{route('appointment.index',[$profile->id,$profile->name_slug])}}" class="btn btn-primary btn-sm"><i class="far fa-comments"></i> Book Appointment</a>
                            <a href="{{route('appointment.online_consult',[$profile->id,$profile->name_slug])}}" class="btn btn-outline-primary btn-sm"><i class="far fa-comments"></i> Video Consultation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection
@section('page_script')
@endsection