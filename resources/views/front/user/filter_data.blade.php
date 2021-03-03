@extends('layouts.app')

@section('content')
<section class="padding bg-grey">
    <div class="row justify-content-center">
        <div class="col-md-12 p-0">
            <div class="input-group mb-3 col-sm-4 float-left">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" class="form-control pointer" placeholder="Search location" name="location">
                <div class="input-group-append">
                    <span class="input-group-text"><a href="javascript:;" onclick="detectLocation()"><i class="fas fa-search-location"></i> Detect</a></span>
                </div>
            </div>
            <div class="input-group mb-3 col-sm-6">
                <div class="dropdown w-100">
                    <input class="form-control pointer dropdown-toggle" type="text" name="search" placeholder="Search doctors, clinics, hospitals, etc." id="search" onkeyup="changeValue()" data-toggle="dropdown" autocomplete="off" value="{{$search}}">
                    <ul class="dropdown-menu w-100 nav-list" role="menu" aria-labelledby="search" id="search-list">
                        @foreach($specialist as $s)
                        <li class="list-item">
                            <a class="menu-list" href="{{Route('front.user.result',['id' => $s->id, 'type' => 'specialty', 'name' => $s->title])}}" onclick="setValue('{{$s->title}}')">
                                <i class="fa fa-search menu-icon" aria-hidden="true"></i>
                                <span>{{$s->title}}</span>
                                <span class="text-muted role-tag">Speciality</span>
                            </a>
                        </li>
                        @endforeach
                        <!--<li class="divider"></li>
<li><a class="menu-list" href="#">About Us</a></li>     -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="container-fluid bg-profile">
        <div class="container">
            <div class="row">
                <div class="col-sm-8 p-2">
                    @forelse($users as $user)
                    <div class="card mb-3">
                        <din class="row p-2">
                            <div class="col-sm-3">
                                <img class="img-thumbnail" src="{{$user->profile_picture}}">
                            </div>
                            <div class="col-sm-9">
                                <h4>
                                    <a href="{{Route('front.user.result',['id' => $user->id, 'type' => 'user', 'name' => $user->name])}}">
                                        {{$user->name}}
                                    </a>
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
                                    @if(!empty($user->detail->specialty_ids))
                                    {{$user->detail->specialty_name}}
                                    @endif
                                </p>
                                <p class="m-0">@if(!empty($user->detail->experience))Year of experience
                                    {{$user->detail->experience}}@endif</p>
                                <p class="m-0">{{$user->detail->address}}</p>
                                <p class="m-0">{{$user->detail->about}}</p>
                                @endif
                                @if($user->role->keyword == 'clinic' || $user->role->keyword == 'hospital')
                                <p class="m-0">{{$user->detail->address}}</p>
                                <p class="m-0">{{$user->detail->bed}} Beds</p>
                                @endif
                            </div>
                            <div class="col-sm-12 mt-3">

                                <a href="{{route('front.message.show',$user->id)}}" class="btn btn-success float-right mr-3"><i class="far fa-comments"></i> Chat
                                    Now</a>

                                <button class="btn btn-primary float-right mr-3" id="phone-show-{{$user->id}}" onclick="viewPhoneDetail('{{$user->id}}')"><i class="fa fa-volume-control-phone" aria-hidden="true"></i> Call Now</button>

                                <div class="mt-5 p-3" id="phone-detail-{{$user->id}}" style="display: none">
                                    <hr>
                                    <p class="text-muted">Phone number</p>
                                    <h3 class="text-success">{{$user->phone}}</h3>
                                    <p class="text-muted small p-0 m-0">By calling this number, you agree to the <a href="#">Terms & Conditions</a> .If you could not connect</p>
                                    <p class="text-muted small p-0 m-0">with the center, please write to <a href="#">support@neucrad.com</a></p>
                                </div>
                            </div>
                        </din>
                    </div>
                    @empty
                    <div class="card mb-3">
                        <din class="row p-2">
                            <div class="col-sm-12 mt-3">
                                <h3 style="text-align: center"><i class="far fa-clock mr-3"></i>No data fount. Please
                                    try
                                    again...</h3>
                            </div>
                        </din>
                    </div>
                    @endforelse
                </div>
                <div class="col-sm-4">

                </div>
            </div>
        </div>
</section>
@endsection
@section('page_script')
<script src="{{ asset('js/home.js')}}"></script>
<script type="text/javascript">
    var manageWishlistUrl = "{{Route('front.user.manage.wishlist')}}";
</script>
@endsection