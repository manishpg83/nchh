<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        @if(Auth::user()->role->name == 'Admin')
            <a href="{{ route('admin.dashboard.index') }}">{{config('app.name', 'Neucrad')}}</a>
        @else
            <a href="{{ url('/') }}">{{config('app.name', 'Neucrad')}}</a>
        @endif

    </div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="{{ url('/') }}">Nc</a>
    </div>
    <ul class="sidebar-menu">
        @if(checkPermission(['admin']))
        <li class="{{Route::is('admin.dashboard.index') ? 'active' : ''}}">
            <a class="nav-link" href="{{route('admin.dashboard.index')}}">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>

        <li class="{{Route::is('admin.user.index') ? 'active' : ''}}">
            <a class="nav-link" href="{{route('admin.user.index')}}">
                <i class="fas fa-user"></i> <span>Users</span>
            </a>
        </li>
        <li class="nav-item dropdown @if(Route::is('admin.getDoctor') || Route::is('admin.doctor.profile.verification')) active @endif">
            <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-user-md"></i>
                <span>Doctors</span></a>
            <ul class="dropdown-menu">
                <li class="{{Route::is('admin.getDoctor') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.getDoctor')}}">Doctor</a></li>
                <li class="{{Route::is('admin.doctor.profile.verification') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.doctor.profile.verification')}}">Verification Request</a>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown @if(Route::is('admin.getAgent') || Route::is('admin.agent.profile.verification')) active @endif">
            <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-user"></i>
                <span>Agents</span></a>
            <ul class="dropdown-menu">
                <li class="{{Route::is('admin.getAgent') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.getAgent')}}">Agent</a></li>
                <li class="{{Route::is('admin.agent.profile.verification') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.agent.profile.verification')}}">Verification Request</a>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown @if(Route::is('admin.getDiagnostics') || Route::is('admin.diagnostics.profile.verification')) active @endif">
            <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-diagnoses"></i>
                <span>Diagnostics</span></a>
            <ul class="dropdown-menu">
                <li class="{{Route::is('admin.getDiagnostics') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.getDiagnostics')}}">Diagnostics</a></li>
                <li class="{{Route::is('admin.diagnostics.profile.verification') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.diagnostics.profile.verification')}}">Verification Request</a>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown @if(Route::is('admin.getUserBankAccount') || Route::is('admin.bank.account.verification')) active @endif">
            <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-user-md"></i>
                <span>User's Bank Account</span></a>
            <ul class="dropdown-menu">
                <li class="{{Route::is('admin.getUserBankAccount') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.getUserBankAccount')}}">Account's</a></li>
                <li class="{{Route::is('admin.bank.account.verification') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.bank.account.verification')}}">Account Verification Request</a>
                </li>
            </ul>
        </li>
        <li class="{{Route::is('admin.getClinic') ? 'active' : ''}}">
            <a class="nav-link" href="{{route('admin.getClinic')}}">
                <i class="fas fa-clinic-medical"></i> <span>Clinic</span>
            </a>
        </li>
        <li class="{{Route::is('admin.getHospital') ? 'active' : ''}}">
            <a class="nav-link" href="{{route('admin.getHospital')}}">
                <i class="fas fa-hospital"></i> <span>Hospital</span>
            </a>
        </li>
        <li class="nav-item dropdown @if(Route::is('admin.getPharmacy') || Route::is('admin.pharmacies.profile.verification')) active @endif">
            <a href="javascript:void(0);" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-diagnoses"></i>
                <span>Pharmacy</span></a>
            <ul class="dropdown-menu">
                <li class="{{Route::is('admin.getPharmacy') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.getPharmacy')}}">Pharmacy</a></li>
                <li class="{{Route::is('admin.pharmacies.profile.verification') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.pharmacies.profile.verification')}}">Verification Request</a>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown @if(Route::is('admin.healthfeed.index') || Route::is('admin.healthfeed.healthfeed-verification') || Route::is('admin.healthfeed_category.index')) active @endif">
            <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-book-medical"></i>
                <span>Health Feed</span></a>
            <ul class="dropdown-menu">
                <li class="{{Route::is('admin.healthfeed_category.index') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.healthfeed_category.index')}}">Health Feed Category</a></li>
                <li class="{{Route::is('admin.healthfeed.index') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.healthfeed.index')}}">Health Feed List</a></li>
                <li class="{{Route::is('admin.healthfeed.healthfeed-verification') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.healthfeed.healthfeed-verification')}}">Health Feed
                        Request</a></li>
            </ul>
        </li>

        <li class="nav-item dropdown @if(Route::is('admin.drug.index') || Route::is('admin.drug-types.index') || Route::is('admin.drug-units.index')) active @endif">
            <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"> <i class="fas fa-capsules"></i>
                <span>Drugs</span></a>
            <ul class="dropdown-menu">
                <li class="{{Route::is('admin.drug.index') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.drug.index')}}">Drugs List</a></li>
                <li class="{{Route::is('admin.drug-types.index') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.drug-types.index')}}">Types</a></li>
                <li class="{{Route::is('admin.drug-units.index') ? 'active' : ''}}"><a class="nav-link" href="{{route('admin.drug-units.index')}}">Units</a></li>
            </ul>
        </li>
        <li class="{{Route::is('admin.permission.index') ? 'active' : ''}}">
            <a class="nav-link" href="{{route('admin.permission.index')}}">
                <i class="fas fa-user-lock"></i> <span>Role & Permission</span>
            </a>
        </li>
        <li class="{{Route::is('admin.wallet.index') ? 'active' : ''}}">
            <a class="nav-link" href="{{route('admin.wallet.index')}}">
                <i class="fas fa-wallet"></i> <span>Wallet</span>
            </a>
        </li>
        <li class="{{Route::is('admin.notification.index') ? 'active' : ''}}">
            <a class="nav-link" href="{{route('admin.notification.index')}}">
                <i class="far fa-bell"></i> <span>Notification</span>
            </a>
        </li>
        <li class="@if(Route::is('admin.setting.index') || Route::is('admin.setting.commission')){{'active'}} @endif">
            <a class="nav-link" href="{{route('admin.setting.index')}}">
                <i class="fas fa-cog"></i> <span>Setting</span>
            </a>
        </li>
        @endif
    </ul>
</aside>