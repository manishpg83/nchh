<form class="form-inline mr-auto">
    <ul class="navbar-nav mr-3">
        <li><a href="javascript:;" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a>
        </li>
        <li><a href="javascript:;" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a>
        </li>
    </ul>
</form>
<ul class="navbar-nav navbar-right">

    <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg @if(Auth::user()->notification_count) beep @endif"><i class="far fa-bell"></i></a>
        <div class="dropdown-menu dropdown-list dropdown-menu-right">
            <div class="dropdown-header">Notifications
                <!--  <div class="float-right">
                    <a disabled>Mark All As Read</a>
                </div> -->
            </div>
            @if(Auth::user()->notification && Auth::user()->notification_count > 0)
            <div class="dropdown-height dropdown-list-content dropdown-list-icons">
                @foreach(Auth::user()->notification as $n)
                <a href="{{route('account.notification.index')}}" class="dropdown-item dropdown-item-unread">
                    @if($n->type == 'doctor_profile_verification_verify')
                    <div class="dropdown-item-icon bg-success text-white">
                        <i class="fas fa-check"></i>
                    </div>
                    @elseif($n->type == 'doctor_profile_verification_reject')
                    <div class="dropdown-item-icon bg-danger text-white">
                        <i class="fas fa-times"></i>
                    </div>
                    @elseif($n->type == 'health_feed_approved')
                    <div class="dropdown-item-icon bg-success text-white">
                        <i class="far fa-newspaper"></i>
                    </div>
                    @elseif($n->type == 'health_feed_rejected')
                    <div class="dropdown-item-icon bg-danger text-white">
                        <i class="far fa-newspaper"></i>
                    </div>
                    @elseif($n->type == 'Add Staff')
                    <div class="dropdown-item-icon bg-warning text-white">
                        <i class="far fa-question-circle"></i>
                    </div>
                    @else
                    <div class="dropdown-item-icon bg-success text-white">
                        <i class="far fa-envelope"></i>
                    </div>
                    @endif

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
                No any notifications.
            </a>
            @endif
            {{-- <div class="dropdown-footer text-center">
                <a disabled>View All <i class="fas fa-chevron-right"></i></a>
            </div> --}}
        </div>
    </li>

    <li class="dropdown">
        <a href="javascript:;" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <img alt="image" src="{{Auth::user()->profile_picture }}" class="rounded-circle mr-1" id="header_profile_icon">
            <div class="d-sm-none d-lg-inline-block">{{Auth::user()->name}}</div>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <!-- <div class="dropdown-title">Logged in 5 min ago</div> -->
            <a href="{{ route('account.show-profile-form') }}" class="dropdown-item has-icon {{Route::is('account.show-profile-form') ? 'active' : ''}}">
                <i class="far fa-user"></i> Profile
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item has-icon text-danger" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </li>
</ul>