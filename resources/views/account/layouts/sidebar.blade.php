<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        @if(Auth::user()->role->name == 'Hospital' || Auth::user()->role->name == 'Clinic' || Auth::user()->role->name == 'Pharmacy'
            || Auth::user()->role->name == 'Diagnostics')
            <a href="{{ route('account.show-profile-form') }}">{{isset($siteTitle) ? $siteTitle : 'NC Health Hub'}}</a>
        @else
            <a href="{{ url('/') }}">{{config('app.name', 'Neucrad')}}</a>
        @endif
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
    </div>
    @if(Session::get('panel') == 'agent') 
        <ul class="sidebar-menu">

            @if(!userProfileStatusMessage())
                @if(Auth::user()->as_agent_verified == 2 && Auth::user()->is_bank_verified == 2)
                <li class="{{Route::is('account.agent.invite.user') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.agent.invite.user')}}">
                        <i class="fas fa-copy"></i> <span>Send Invitation</span>
                    </a>
                </li>
                @endif

                <li class="{{Route::is('account.user.bank.account') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.user.bank.account')}}">
                        <i class="fas fa-university"></i> <span>Bank Account</span>
                    </a>
                </li>

                <li class="@if(Route::is('payment.received')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('payment.received')}}">
                        <i class="fas fa-money-bill-alt"></i> <span>Commission History</span>
                    </a>
                </li>

                @if(Auth::user()->as_agent_verified == 2 && Auth::user()->is_bank_verified == 2)
                <li class="{{Route::is('account.agent.refferal.users') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.agent.refferal.users')}}">
                        <i class="fas fa-users"></i> <span>My Referral</span>
                    </a>
                </li>
                @endif
            @endif

            <li class="@if(Route::is('account.setting.index') || Route::is('account.setting.general') || Route::is('account.setting.consultant')){{'active'}} @endif">
                <a class="nav-link" href="{{route('account.setting.index')}}">
                    <i class="fas fa-cog"></i> <span>Setting</span>
                </a>
            </li>

            @if(Session::get('previous-panel'))
                <li>
                    <a class="nav-link" href="{{route('account.user.logout-switch-panel')}}">
                        <i class="fas fa-sign-out-alt"></i> <span>Back To {{ ucfirst(Session::get('previous-panel')) }} Profile</span>
                    </a>
                </li>
            @endif
        </ul>
    @elseif(Session::get('panel') == 'doctor')
        <ul class="sidebar-menu">

            @if(!userProfileStatusMessage())

                <li class="{{Route::is('account.user.bank.account') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.user.bank.account')}}">
                        <i class="fas fa-university"></i> <span>Bank Account</span>
                    </a>
                </li>

                <li class="{{Route::is('account.healthfeed.index') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.healthfeed.index')}}">
                        <i class="far fa-newspaper"></i> <span>Health Feed</span>
                    </a>
                </li>

                <li class="nav-item dropdown @if(Route::is('payment.pay') || Route::is('payment.received')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-money-bill-alt"></i> <span>Payments</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if(checkPermission(['doctor','patient','agent']))
                        <li class="@if(Route::is('payment.pay')){{'active'}} @endif">
                            <a class="nav-link" href="{{route('payment.pay')}}">Paid</a>
                        </li>
                        @endif
                        @if(checkPermission(['doctor','clinic','hospital','manager','accountant','diagnostics']))
                        <li class="@if(Route::is('payment.received')){{'active'}} @endif">
                            <a class="nav-link" href="{{route('payment.received')}}">Received</a>
                        </li>
                        @endif
                    </ul>
                </li>

                <li class="nav-item dropdown @if(Route::is('account.staff.index') || Route::is('account.staff.create') || Route::is('account.staff.edit')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-users"></i> <span>Staff Manager</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="@if(Route::is('account.staff.index') || Route::is('account.staff.edit')){{'active'}}@endif">
                            <a class="nav-link" href="{{route('account.staff.index')}}">Staffs</a>
                        </li>
                        <li class="{{Route::is('account.staff.create') ? 'active' : ''}}"><a class="nav-link" href="{{route('account.staff.create')}}">Add Staff</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown @if(Route::is('account.practice.index') || Route::is('account.practice.create') || Route::is('account.practice.edit')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-clinic-medical"></i> <span>Practice Manager</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="@if(Route::is('account.practice.index') || Route::is('account.practice.edit')){{'active'}}@endif">
                            <a class="nav-link" href="{{route('account.practice.index')}}">Practices</a>
                        </li>
                        <li class="{{Route::is('account.practice.create') ? 'active' : ''}}"><a class="nav-link" href="{{route('account.practice.create')}}">Practice Create</a></li>
                    </ul>
                </li>

                <li class="{{Route::is('account.drug.index') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.drug.index')}}">
                        <i class="fas fa-capsules"></i> <span>Drugs</span>
                    </a>
                </li>

                <li class="@if(Route::is('account.patients.index') || Route::is('account.patients.appointment') || Route::is('account.patients.appointment.detail') || Route::is('account.patients.diagnostics.appointment')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('account.patients.index')}}">
                        <i class="far fa-user"></i> <span>My Patients</span>
                    </a>
                </li>

                <li class="{{Route::is('account.calendar') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.calendar')}}">
                        <i class="fas fa-calendar-check"></i> <span>Calendar</span>
                    </a>
                </li>

                <li class="nav-item dropdown @if(Route::is('account.user.wallet') || Route::is('account.user.wallet.withdraw.history')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-wallet"></i> <span>Wallet</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="@if(Route::is('account.user.wallet')){{'active'}}@endif">
                            <a class="nav-link" href="{{route('account.user.wallet')}}">Wallet Balance</a>
                        </li>
                        <li class="{{Route::is('account.user.wallet.withdraw.history') ? 'active' : ''}}">
                            <a class="nav-link" href="{{route('account.user.wallet.withdraw.history')}}">Withdraw History</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if(Auth::user()->locality != null)
                @if(checkPermission(['doctor','patient']))
                    <li class="{{Route::is('account.profiles') ? 'active' : ''}}">
                        <a class="nav-link" href="{{route('account.profiles')}}">
                            <i class="far fa-user"></i> <span>Doctor Profiles</span>
                        </a>
                    </li>
                @endif
            @endif

            <li class="@if(Route::is('account.setting.index') || Route::is('account.setting.general') || Route::is('account.setting.consultant')){{'active'}} @endif">
                <a class="nav-link" href="{{route('account.setting.index')}}">
                    <i class="fas fa-cog"></i> <span>Setting</span>
                </a>
            </li>

            @if(Session::get('previous-panel'))
            <li>
                <a class="nav-link" href="{{route('account.user.logout-switch-panel')}}">
                    <i class="fas fa-sign-out-alt"></i> <span>Back To {{ ucfirst(Session::get('previous-panel')) }} Profile</span>
                </a>
            </li>
            @endif
        </ul>
    @elseif(Session::get('panel') == 'patient')
        <ul class="sidebar-menu">
            @if(Auth::user()->locality != null)
                <li class="@if(Route::is('myAppointment')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('myAppointment')}}">
                        <i class="fas fa-calendar-check"></i> <span>My Appointments</span>
                    </a>
                </li>

                <li class="{{Route::is('account.myDoctors') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.myDoctors')}}">
                        <i class="fas fa-user-md"></i> <span>My Doctor</span>
                    </a>
                </li>

                <li class="@if(Route::is('medical_record.index') || Route::is('medical_record.create') || Route::is('medical_record.edit')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('medical_record.index')}}">
                        <i class="fas fa-notes-medical"></i> <span>Medical Record</span>
                    </a>
                </li>

                <li class="nav-item dropdown @if(Route::is('payment.pay') || Route::is('payment.received')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-money-bill-alt"></i> <span>Payments</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if(checkPermission(['doctor','patient','agent']))
                        <li class="@if(Route::is('payment.pay')){{'active'}} @endif">
                            <a class="nav-link" href="{{route('payment.pay')}}">Paid</a>
                        </li>
                        @endif
                        @if(checkPermission(['doctor','clinic','hospital','manager','accountant','diagnostics']))
                        <li class="@if(Route::is('payment.received')){{'active'}} @endif">
                            <a class="nav-link" href="{{route('payment.received')}}">Received</a>
                        </li>
                        @endif
                    </ul>
                </li>

                @if(checkPermission(['doctor']))
                <li class="{{Route::is('account.profiles') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.profiles')}}">
                        <i class="far fa-user"></i> <span>Doctor Profiles</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['agent']))
                <li class="{{Route::is('account.agent.profile') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.agent.profile')}}">
                        <i class="far fa-user"></i> <span>Agent Profiles</span>
                    </a>
                </li>
                @endif 

                @if(checkPermission(['patient']) && Auth::user()->as_doctor_verified != 2)
                <li class="{{Route::is('account.profiles') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.profiles')}}">
                        <i class="far fa-user"></i> <span>Become a Doctor</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['doctor','patient']) && Auth::user()->as_agent_verified != 2)
                <li class="{{Route::is('account.agent.profile') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.agent.profile')}}">
                        <i class="far fa-user"></i> <span>Become an Agent</span>
                    </a>
                </li>
                @endif


                @if(Auth::user()->as_agent_verified == 2 && Auth::user()->is_bank_verified == 2)
                <li class="{{Route::is('account.agent.refferal.users') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.agent.refferal.users')}}">
                        <i class="fas fa-users"></i> <span>Referral Users</span>
                    </a>
                </li>
                @endif

            @endif

            <li class="@if(Route::is('account.setting.index') || Route::is('account.setting.general') || Route::is('account.setting.consultant')){{'active'}} @endif">
                <a class="nav-link" href="{{route('account.setting.index')}}">
                    <i class="fas fa-cog"></i> <span>Setting</span>
                </a>
            </li>

            @if(Session::get('previous-panel'))
            <li>
                <a class="nav-link" href="{{route('account.user.logout-switch-panel')}}">
                    <i class="fas fa-sign-out-alt"></i> <span>Back To {{ ucfirst(Session::get('previous-panel')) }} Profile</span>
                </a>
            </li>
            @endif

        </ul>
    @else
        <ul class="sidebar-menu">
            @if(!userProfileStatusMessage())
                @if(checkPermission(['doctor','patient']))
                <li class="{{Route::is('account.profiles') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.profiles')}}">
                        <i class="far fa-user"></i> <span>Doctor Profiles</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['doctor','patient','agent']))
                <li class="{{Route::is('account.agent.profile') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.agent.profile')}}">
                        <i class="far fa-user"></i> <span>Agent Profiles</span>
                    </a>
                </li>
                @endif
            @endif

            @if(Auth::user()->locality != null)
                @if(checkPermission(['pharmacy']))
                    <li class="{{Route::is('account.pharmacy.profile') ? 'active' : ''}}">
                        <a class="nav-link" href="{{route('account.pharmacy.profile')}}">
                            <i class="far fa-user"></i> <span>Being a Pharmacy</span>
                        </a>
                    </li>
                @endif
            @endif

            @if(Auth::user()->locality != null)
                @if(checkPermission(['diagnostics']))
                <li class="{{Route::is('account.diagnostics.profile') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.diagnostics.profile')}}">
                        <i class="far fa-user"></i> <span>Being a Diagnostics</span>
                    </a>
                </li>
                @endif
            @endif

            @if(Auth::user()->locality != null)
                @if(checkPermission(['clinic']))
                <li class="{{Route::is('account.clinic.profile') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.clinic.profile')}}">
                        <i class="far fa-user"></i> <span>Being a Clinic</span>
                    </a>
                </li>
                @endif
            @endif

            @if(Auth::user()->locality != null)
                @if(checkPermission(['hospital']))
                <li class="{{Route::is('account.hospital.profile') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.hospital.profile')}}">
                        <i class="far fa-user"></i> <span>Being a Hospital</span>
                    </a>
                </li>
                @endif
            @endif

            @if(!userProfileStatusMessage())
                @if(checkPermission(['doctor','agent','clinic','hospital','diagnostics', 'pharmacy']))
                <li class="{{Route::is('account.user.bank.account') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.user.bank.account')}}">
                        <i class="fas fa-university"></i> <span>Verify Bank Account</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['doctor','agent','clinic','hospital','diagnostics', 'pharmacy']))
                <li class="nav-item dropdown @if(Route::is('account.user.wallet') || Route::is('account.user.wallet.withdraw.history')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-wallet"></i> <span>Wallet</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="@if(Route::is('account.user.wallet')){{'active'}}@endif">
                            <a class="nav-link" href="{{route('account.user.wallet')}}">Wallet Balance</a>
                        </li>
                        <li class="{{Route::is('account.user.wallet.withdraw.history') ? 'active' : ''}}">
                            <a class="nav-link" href="{{route('account.user.wallet.withdraw.history')}}">Withdraw History</a>
                        </li>
                    </ul>
                </li>
                @endif

                @if(checkPermission(['doctor','patient','agent']))
                <li class="@if(Route::is('medical_record.index') || Route::is('medical_record.create') || Route::is('medical_record.edit')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('medical_record.index')}}">
                        <i class="fas fa-notes-medical"></i> <span>Medical Record</span>
                    </a>
                </li>

                <li class="@if(Route::is('myAppointment')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('myAppointment')}}">
                        <i class="fas fa-calendar-check"></i> <span>My Appointments</span>
                    </a>
                </li>

                <li class="{{Route::is('account.myDoctors') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.myDoctors')}}">
                        <i class="fas fa-user-md"></i> <span>My Doctor</span>
                    </a>
                </li>
                @endif
                @if(checkPermission(['doctor','diagnostics']))
                <li class="{{Route::is('account.calendar') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.calendar')}}">
                        <i class="fas fa-calendar-check"></i> <span>Calendar</span>
                    </a>
                </li>
                @endif
                @if(checkPermission(['doctor','clinic','hospital','manager','diagnostics']))
                <li class="@if(Route::is('account.patients.index') || Route::is('account.patients.appointment') || Route::is('account.patients.appointment.detail') || Route::is('account.patients.diagnostics.appointment')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('account.patients.index')}}">
                        <i class="far fa-user"></i> <span>My Patients</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['doctor']) && Auth::user()->as_doctor_verified == 2)
                <li class="nav-item dropdown @if(Route::is('account.practice.index') || Route::is('account.practice.create') || Route::is('account.practice.edit')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-clinic-medical"></i> <span>Practice Manager</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="@if(Route::is('account.practice.index') || Route::is('account.practice.edit')){{'active'}}@endif">
                            <a class="nav-link" href="{{route('account.practice.index')}}">Practices</a>
                        </li>
                        <li class="{{Route::is('account.practice.create') ? 'active' : ''}}"><a class="nav-link" href="{{route('account.practice.create')}}">Practice Create</a></li>
                    </ul>
                </li>
                @endif
                @if(checkPermission(['diagnostics']) && Auth::user()->as_diagnostics_verified == 2)
                <li class="{{Route::is('account.practice.create') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.practice.create')}}">
                        <i class="fas fa-calendar-check"></i> <span>Timing</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['doctor','clinic','hospital', 'pharmacy']))
                <li class="nav-item dropdown @if(Route::is('account.staff.index') || Route::is('account.staff.create') || Route::is('account.staff.edit')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-users"></i> <span>Staff Manager</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="@if(Route::is('account.staff.index') || Route::is('account.staff.edit')){{'active'}}@endif">
                            <a class="nav-link" href="{{route('account.staff.index')}}">Staffs</a>
                        </li>
                        <li class="{{Route::is('account.staff.create') ? 'active' : ''}}"><a class="nav-link" href="{{route('account.staff.create')}}">Add Staff</a></li>
                    </ul>
                </li>
                @endif

                @if(checkPermission(['doctor','clinic','hospital','manager','accountant','patient','agent','diagnostics']))
                <li class="nav-item dropdown @if(Route::is('payment.pay') || Route::is('payment.received')){{'active'}} @endif">
                    <a class="nav-link has-dropdown" href="javascript:;">
                        <i class="fas fa-money-bill-alt"></i> <span>Payments</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if(checkPermission(['doctor','patient','agent']))
                        <li class="@if(Route::is('payment.pay')){{'active'}} @endif">
                            <a class="nav-link" href="{{route('payment.pay')}}">Paid</a>
                        </li>
                        @endif
                        @if(checkPermission(['doctor','clinic','hospital','manager','accountant','diagnostics']))
                        <li class="@if(Route::is('payment.received')){{'active'}} @endif">
                            <a class="nav-link" href="{{route('payment.received')}}">Received</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                @if(checkPermission(['doctor']))
                <li class="{{Route::is('account.drug.index') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.drug.index')}}">
                        <i class="fas fa-capsules"></i> <span>Drugs</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['doctor','clinic','hospital','pharmacy', 'diagnostics']))
                <li class="{{Route::is('account.healthfeed.index') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.healthfeed.index')}}">
                        <i class="far fa-newspaper"></i> <span>Health Feed</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['pharmacy']))
                <li class="@if(Route::is('account.prescription.index') || Route::is('account.prescription.show')){{'active'}} @endif">
                    <a class="nav-link" href="{{route('account.prescription.index')}}">
                        <i class="fas fa-file-prescription"></i> <span>Prescription Request</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['diagnostics']) && Auth::user()->as_diagnostics_verified == 2)
                <li class="{{Route::is('account.diagnostics_services.index') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.diagnostics_services.index')}}">
                        <i class="fas fa-diagnoses"></i> <span>Services</span>
                    </a>
                </li>
                @endif

                @if(isAuthorize('account.rating.index'))
                <li class="{{Route::is('account.rating.index') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.rating.index')}}">
                        <i class="fas fa-star-half-alt"></i> <span>Rating</span>
                    </a>
                </li>
                @endif

                @if(checkPermission(['doctor','patient','agent']) && Auth::user()->as_agent_verified == 2 && Auth::user()->is_bank_verified == 2)
                <li class="{{Route::is('account.agent.refferal.users') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.agent.refferal.users')}}">
                        <i class="fas fa-users"></i> <span>Referral Users</span>
                    </a>
                </li>
                @endif

                <li class="{{Route::is('account.notification.index') ? 'active' : ''}}">
                    <a class="nav-link" href="{{route('account.notification.index')}}">
                        <i class="far fa-bell"></i> <span>Notification</span>
                    </a>
                </li>
            @endif
            
            <li class="@if(Route::is('account.setting.index') || Route::is('account.setting.general') || Route::is('account.setting.consultant')){{'active'}} @endif">
                <a class="nav-link" href="{{route('account.setting.index')}}">
                    <i class="fas fa-cog"></i> <span>Setting</span>
                </a>
            </li>

        </ul>
    @endif
</aside>