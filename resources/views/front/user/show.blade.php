@extends('layouts.app')

@section('content')

<section class="bg-grey padding" id="blog">
    <div class="container">
        <div class="row align-items-center mt-5">
            <div class="col-12 col-md-4">
                <h2>Don’t delay your health concerns.</h2>
                <p class="text-muted">Just enter your doctor, specialty, clinic and hospital name then share your
                    health concern with doctor.</p>
            </div>
            <div class="col-12 col-md-3">
                <form class="subscribe" action="javascript:;">
                    <input type="text" name="location" class="form-control" placeholder="Your location">
                    <button class="btn btn-primary" onclick="detectLocation()">Detect</button>
                </form>
            </div>
            <div class="col-12 col-md-5">
                <form class="subscribe" action="javascript:;">
                    <div class="dropdown w-100">
                        <input class="form-control dropdown-toggle" type="text" name="search"
                            placeholder="Search doctors, clinics, hospitals, etc." id="search" onkeyup="changeValue()"
                            data-toggle="dropdown" autocomplete="off">
                        <ul class="dropdown-menu w-100 nav-list" role="menu" aria-labelledby="search" id="search-list">
                            @foreach($specialist as $s)
                            <li class="list-item">
                                <a class="menu-list"
                                    href="{{Route('user.result',['id' => $s->id, 'type' => 'specialty', 'name' => $s->title])}}"
                                    onclick="setValue('{{$s->title}}')">
                                    <i class="fa fa-search menu-icon" aria-hidden="true"></i>
                                    <span>{{$s->title}}</span>
                                    <span class="text-muted role-tag">Speciality</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<section class="bg-grey" id="blog">
    <div class="container-fluid bg-grey bg-profile">
        <div class="container">
            <div class="row">
                <div class="col-sm-8 p-2">
                    <div class="card">
                        <din class="row p-2">
                            <div class="col-sm-3">
                                <img class="img-thumbnail"
                                    src="@if(!empty($user->detail->image_name)){{$user->detail->profile_picture}} @else {{asset('images/default.png')}} @endif">
                            </div>
                            <div class="col-sm-9">
                                <h4>
                                    {{$user->name}}
                                    <a href="javascript:;" onclick="myWishlist('{{$user->id}}');">
                                        <div id="heart-container" class="heart-container">
                                            <input type="checkbox" id="toggle" class="toggle" @if($user->is_wishlist
                                            > 0) checked="checked" @endif/>
                                            <div id="twitter-heart" class="twitter-heart"></div>
                                        </div>
                                    </a>
                                </h4>
                                <p class="title m-0">{{$user->detail->degree}}</p>
                                @if($user->role->keyword == 'doctor')
                                <p class="m-0">
                                    @if(!empty($user->detail->doctor_category)){{$user->detail->doctor_category->name}},
                                    @endif{{$user->detail->experience}}</p>
                                <p class="m-0">{{$user->detail->address}}</p>
                                <p class="m-0">{{$user->detail->about}}</p>
                                @endif
                            </div>
                            <div class="col-sm-12 mt-3">

                                <a href="{{route('message.show',$user->id)}}"
                                    class="btn btn-success float-right mr-3"><i class="far fa-comments"></i> Chat
                                    Now</a>

                                <button class="btn btn-primary float-right mr-3" id="phone-show"><i
                                        class="fa fa-volume-control-phone" aria-hidden="true"></i> Call Now</button>

                                <div class="mt-5 p-3" id="phone-detail" style="display: none">
                                    <hr>
                                    <p class="text-muted">Phone number</p>
                                    <h3 class="text-success">{{$user->phone}}</h3>
                                    <p class="text-muted small p-0 m-0">By calling this number, you agree to the <a
                                            href="#">Terms & Conditions</a> .If you could not connect</p>
                                    <p class="text-muted small p-0 m-0">with the center, please write to <a
                                            href="#">support@neucrad.com</a></p>
                                </div>
                            </div>
                        </din>
                    </div>
                    <div class="card mt-4">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            @if($user->role->keyword == 'doctor')
                            <li class="nav-item">
                                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab"
                                    aria-controls="info" aria-selected="true">Info</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="consult-tab" data-toggle="tab" href="#consult" role="tab"
                                    aria-controls="consult" aria-selected="false">Consult Q&A</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="healthfeed-tab" data-toggle="tab" href="#healthfeed" role="tab"
                                    aria-controls="healthfeed" aria-selected="false">Healthfeed</a>
                            </li>
                            @endif
                            @if($user->role->keyword == 'clinic' || $user->role->keyword == 'hospital')
                            <li class="nav-item">
                                <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview"
                                    role="tab" aria-controls="overview" aria-selected="true">Overview</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="doctor-tab" data-toggle="tab" href="#doctor" role="tab"
                                    aria-controls="doctor" aria-selected="true">Doctors
                                    ({{$user->detail->doctor_count}})</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="services-tab" data-toggle="tab" href="#services" role="tab"
                                    aria-controls="services" aria-selected="true">Services
                                    ({{$user->detail->service_count}})</a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" id="stories-tab" data-toggle="tab" href="#stories" role="tab"
                                    aria-controls="stories" aria-selected="false">Stories</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            @if($user->role->keyword == 'doctor')
                            <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                                <div class="row p-2">
                                    <div class="col-sm-4">
                                        <h6>Address</h6>
                                        <p>{{$user->detail->address}}</p>
                                    </div>
                                    <div class="col-sm-4">
                                        <h6>timing</h6>
                                        <p>{{$user->detail->timing}}</p>
                                    </div>
                                    <div class="col-sm-4">
                                        <h6>Charge</h6>
                                        <p>₹ {{$user->detail->charge}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="consult" role="tabpanel" aria-labelledby="consult-tab">
                                <div class="row p-2">
                                    <div class="col-sm-12">
                                        <p style="text-align: center">No any consult Q&A...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="healthfeed" role="tabpanel" aria-labelledby="healthfeed-tab">
                                <div class="row p-2">
                                    <div class="col-sm-12">
                                        @foreach($user->healthfeed as $healthfeed)
                                        <div class="card">
                                            <a href="{{Route('healthfeed.show',$healthfeed->id)}}"
                                                class="article-link">
                                                <img class="card-img-top" src="{{$healthfeed->cover_photo}}"
                                                    alt="Card image" style="width:100%">
                                                <div class="card-body card-wh">
                                                    <h5 class="card-title">{{$healthfeed->short_title}}</h5>
                                                    <h6 class="card-title float-left">
                                                        <img class="article-profile mr-2"
                                                            src="{{$healthfeed->user->profile_picture}}">
                                                        <span
                                                            class="text-secondary">{{$healthfeed->user->short_name}}</span>
                                                    </h6>
                                                    <p class="card-title text-muted float-right">
                                                        {{$healthfeed->health_feed_date}}</p>
                                                    <!-- <p class="card-text">{!!$healthfeed->short_content!!}</p> -->
                                                </div>
                                            </a>
                                        </div>
                                        <div class="divider"></div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($user->role->keyword == 'clinic' || $user->role->keyword == 'hospital')
                            <div class="tab-pane fade show active" id="overview" role="tabpanel"
                                aria-labelledby="overview-tab">
                                <div class="row p-2">
                                    <div class="col-sm-12">
                                        <h5>About {{$user->name}}</h5>
                                        <p>{{$user->detail->about}}</p>
                                    </div>
                                    <div class="col-sm-4">
                                        <h6>Address</h6>
                                        <p>{{$user->detail->address}}</p>
                                    </div>
                                    <div class="col-sm-4">
                                        <h6>Timing</h6>
                                        <p>{{$user->detail->timing}}</p>
                                    </div>
                                    <div class="col-sm-4">
                                        <h6>Mode of payment</h6>
                                        <p>{{$user->detail->mode_of_payment}}</p>
                                        <p>Number of beds - <b>{{$user->detail->bed}}</b></p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="doctor" role="tabpanel" aria-labelledby="doctor-tab">
                                @foreach($user->detail->doctors as $doctor)
                                <din class="row p-2">
                                    <div class="col-sm-3">
                                        <img class="img-thumbnail"
                                            src="@if(!empty($doctor->detail->image_name)){{$doctor->detail->profile_picture}} @else {{asset('images/default.png')}} @endif">
                                    </div>
                                    <div class="col-sm-6">
                                        <h4>
                                            <a
                                                href="{{Route('user.result',['id' => $doctor->id, 'type' => 'user', 'name' => $doctor->name])}}">
                                                {{$doctor->name}}</a>
                                        </h4>
                                        <p class="title m-0">{{$doctor->detail->degree}}</p>
                                        <p class="m-0">
                                            @if(!empty($doctor->detail->doctor_category)){{$doctor->detail->doctor_category->name}},
                                            @endif{{$doctor->detail->experience}}</p>
                                    </div>
                                    <div class="col-sm-3">
                                        <p><i class="fa fa-money" aria-hidden="true"></i> {{$doctor->detail->charge}}
                                        </p>
                                        <p><i class="fa fa-clock-o" aria-hidden="true"></i> {{$doctor->detail->timing}}
                                        </p>
                                    </div>
                                </din>
                                @endforeach
                            </div>
                            <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
                                <div class="row p-2">
                                    <ul class="w-100">
                                        @foreach($user->detail->services_list as $service)
                                        <li class="col-sm-4 float-left">{{$service->name}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif
                            <div class="tab-pane fade" id="stories" role="tabpanel" aria-labelledby="stories-tab">
                                <div class="row p-2">
                                    <div class="col-sm-12">
                                        <p style="text-align: center">No any stories...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_script')
<script src="{{ asset('js/home.js')}}"></script>
<script type="text/javascript">
/*Url List*/

var enable_header = true;
var getLocation = "{{Route('detect.location')}}";
var manageWishlistUrl = "{{Route('user.manage.wishlist')}}";
</script>
@endsection