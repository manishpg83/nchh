@extends('layouts.app')

@section('content')
<section class="padding bg-grey">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="user-wrapper">
                    <ul class="users">
                        @foreach($users as $user)
                        <li class="user" id="{{ $user->id }}">
                            @if($user->unread)
                            <span class="pending">{{ $user->unread }}</span>
                            @endif

                            <div class="media">
                                <div class="media-left">
                                    <img src="{{url('/storage/app/user/'.$user->profile_picture)}}" alt="" class="chat-image">
                                </div>

                                <div class="media-body">
                                    <p class="name">@if($user->name) {{ $user->name }} @else {{$user->phone}} @endif</p>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-md-8" id="messages">

            </div>
        </div>
    </div>
</section>
@endsection
@section('page_script')
<script src="{{ asset('js/message.js')}}"></script>
<script type="text/javascript">
    var storeMessage = "{{Route('front.message.store')}}";
    var chatScreen = "{{Route('front.message.screen',[':slug'])}}"
</script>
@endsection