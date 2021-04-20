@extends('layouts.app')

@section('content')

<section class="padding bg-grey">
    <div class="container">
        <div class="row justify-content-center">
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
                        <div class="card-subtitle mb-2 text-muted">Written by <a href="javascript:;"
                                title="{{$healthfeed->user->name}}">{{$healthfeed->user->name}}</a> on
                            {{$healthfeed->health_feed_date}}</div>
                        <h4 class="card-title">
                            <a href="{{Route('healthfeed.show',$healthfeed->id)}}">{{$healthfeed->short_title}}</a>
                        </h4>
                        <div class="card-text">{!! $healthfeed->short_content!!}</div>
                        <div class="text-right">
                            <a href="{{Route('healthfeed.show',$healthfeed->id)}}" class="card-more">Read More <i
                                    class="ion-ios-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
@section('page_script')
@endsection