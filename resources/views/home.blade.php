@extends('layouts.app')

@section('content')

<section class="hero bg-overlay" id="hero" data-bg="{{asset('images/leading.jpg')}}">
    <div class="text">
        <p class="lead">Welcome to Neucrad</p>
        <h1>We <span class="bold">care</span>, about <span class="bold">Your Health</span>.</h1>
        {{-- <div class="cta">
    <a href="#features" class="btn btn-primary smooth-link">Get Started</a>
    <div class="link">            
    <a href="#">Under the MIT License</a>
    </div>
    </div> --}}
    </div>
</section>

<section class="bg-grey pb-0" id="blog">
    <div class="container">
        <form class="subscribe" action="{{route('home.search')}}" method="GET">
            <div class="row align-items-center mt-5">
                <div class="col-12 col-md-4">
                    <h2>Don’t delay your health concerns.</h2>
                    <p class="text-muted">Just enter your doctor, speciality, clinic and hospital name then share your
                        health concern with doctor.</p>
                </div>

                <div class="col-12 col-md-3">
                    <div class="dropdown w-100">
                        <input type="text" name="location" id="location" class="form-control dropdown-toggle" placeholder="Your location" onkeyup="autoSuggestCity(this)" data-toggle="dropdown" autocomplete="off" aria-expanded="false">
                        <a class="btn btn-primary text-white" onclick="detectLocation()" style="line-height: 2.2;">Detect</a>
                        <ul class="dropdown-menu w-100 nav-list" role="menu" aria-labelledby="location" id="location-list">
                        </ul>
                    </div>
                </div>

                <div class="col-12 col-md-5">
                    <div class="dropdown w-100">
                        <input class="form-control dropdown-toggle" type="text" name="keyword" placeholder="Search doctors, clinics, hospitals, etc." id="search" onkeyup="autoSuggest(this)" data-toggle="dropdown" autocomplete="off">
                        <ul class="dropdown-menu w-100 nav-list" role="menu" aria-labelledby="search" id="search-list">
                            @foreach($specialist as $s)
                            <li class="list-item">
                                <a class="menu-list" href="{{route('home.search',['speciality' => $s->id, 'search' => $s->title])}}">
                                    <!-- <i class="fa fa-search menu-icon" aria-hidden="true"></i> -->
                                    <img class="search-profile p-0" src="{{$s->image}}">
                                    <span>{{$s->title}}</span>
                                    <span class="text-muted role-tag">Speciality</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="bg-grey">
    <div class="container">
        <div class="section-body">
            <div class="row col-spacing">
                <div class="col-12 col-md-4">
                    <h2>Read top healthfeeds from health experts</h2>
                    <p class="text-muted">Health healthfeeds that keep you suggest about good health practices and
                        achieve your goals.</p>
                    <a href="{{Route('healthfeed.index')}}" class="btn btn-primary">See all healthfeeds</a>
                </div>
                @foreach($healthfeeds as $healthfeed)
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card">
                        @if($healthfeed->video_url)
                        <div class="card-img-top">
                            {!! $healthfeed->html_video_url !!}
                        </div>
                        @else
                        <img class="card-img-top" src="{{$healthfeed->cover_photo}}" alt="{{$healthfeed->short_title}}">
                        @endif
                        <div class="card-body">
                            <div class="card-subtitle mb-2 text-muted">by <a href="javascript:;" title="{{$healthfeed->user->name}}">{{$healthfeed->user->name}}</a> on
                                {{$healthfeed->health_feed_date}}</div>
                            <h4 class="card-title">
                                <a href="{{Route('healthfeed.show',$healthfeed->id)}}">{{$healthfeed->short_title}}</a>
                            </h4>
                            <div class="card-text">{!! $healthfeed->short_content!!}</div>
                            <div class="text-right">
                                <a href="{{Route('healthfeed.show',$healthfeed->id)}}" class="card-more">Read More
                                    <i class="ion-ios-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@endsection
@section('page_script')
<script src="{{ asset('js/home.js')}}"></script>
<script type="text/javascript"></script>
@endsection