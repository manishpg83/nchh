@extends('layouts.app')

@section('content')

<section class="padding bg-grey">
    <div class="container">
        <div class="row">

            <article id="">
                <header>
                    <div class="meta">Written by <span class="author">{{$healthfeed->user->name}}</span>
                        <span class="date">{{$healthfeed->health_feed_date}}</span>
                    </div>
                    <h2>{{$healthfeed->title}}</h2>
                </header>
                <div class="entry-content">
                    @if($healthfeed->video_url)
                    {!! $healthfeed->html_video_url !!}
                    @else
                    <img class="card-img-top" src="{{$healthfeed->cover_photo}}" alt="{{$healthfeed->short_title}}">
                    @endif
                    <hr>
                    <p class="lead">{!!$healthfeed->content!!}</p>
                </div>
            </article>
        </div>
    </div>
</section>

@endsection
@section('page_script')
@endsection