<form class="form-inline mr-auto">
    <ul class="navbar-nav mr-3">
        <li><a href="javascript:;" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a>
        </li>
        <li><a href="javascript:;" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i
                    class="fas fa-search"></i></a>
        </li>
    </ul>
</form>
<ul class="navbar-nav navbar-right">

    <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown"
            class="nav-link notification-toggle nav-link-lg @if(Auth::user()->notification_count) beep @endif"><i
                class="far fa-bell"></i></a>
        <div class="dropdown-menu dropdown-list dropdown-menu-right">
            <div class="dropdown-header">Notifications
                <!-- <div class="float-right">
<a disabled>Mark All As Read</a>
</div> -->
            </div>
            @if(Auth::user()->notification && Auth::user()->notification_count > 0)
            <div class="dropdown-height dropdown-list-content dropdown-list-icons ">

                @foreach(Auth::user()->notification as $n)
                <a href="@if($n->type == 'doctor_profile_verification'){{route('admin.doctor.profile.verification')}}@elseif($n->type == 'user_register'){{route('admin.user.index')}}@elseif($n->type == 'agent_profile_verification'){{route('admin.agent.profile.verification')}}@elseif($n->type == 'diagnostics_profile_verification'){{route('admin.diagnostics.profile.verification')}}@elseif($n->type == 'bank_account_verification'){{route('admin.bank.account.verification')}} @elseif($n->type == 'health_feed') {{route('admin.healthfeed.healthfeed-verification')}} @else {{route('admin.notification.index')}}@endif"
                    class="dropdown-item dropdown-item-unread">
                    <div class="dropdown-item-icon text-white">
                        <img class="img-profile rounded-circle w-100" src="{{$n->sender->profile_picture}}">
                        <!-- <i class="fas fa-check"></i> -->
                    </div>
                    <div class="dropdown-item-desc">
                        <h6>{{$n->title}}</h6>
                        <div class="f-sm">{!!$n->message!!}</div>
                        <div class="time text-primary">{{$n->created_date}}</div>
                    </div>
                </a>
                @endforeach

            </div>
            @else
            <a href="javascript:;" class="dropdown-item dropdown-item-unread">
                No any notifications
            </a>
            @endif
            {{-- <div class="dropdown-footer text-center">
    <a disabled>View All <i class="fas fa-chevron-right"></i></a>
    </div> --}}
        </div>
    </li>

    <li class="dropdown">
        <a href="javascript:;" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <img alt="image" src="{{$user->profile_picture }}" class="rounded-circle mr-1" id="header_profile_icon">
            <div class="d-sm-none d-lg-inline-block">{{$user->name}}</div>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <!-- <div class="dropdown-title">Logged in 5 min ago</div> -->
            <!-- <a href="{{ route('account.show-profile-form') }}"
    class="dropdown-item has-icon {{Route::is('account.show-profile-form') ? 'active' : ''}}">
    <i class="far fa-user"></i> Profile
    </a> 
    <div class="dropdown-divider"></div>
    -->
            <a class="dropdown-item has-icon text-danger" href="{{ route('admin.logout') }}"
                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </li>
</ul>