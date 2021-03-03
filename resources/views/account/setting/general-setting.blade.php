@extends('account.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="javascript:;">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{route('account.setting.index')}}">Settings</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">All About General Settings</h2>
        <p class="section-lead">
            You can adjust all general settings here.
        </p>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Jump To</h4>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-pills flex-column">
                            @if($user->password)
                            <li class="nav-item">
                                <a class="nav-link active" id="changePassword-tab" data-toggle="pill" href="#changePassword" role="tab" aria-controls="changePassword" aria-selected="true">
                                    Change Password</a>
                            </li>
                            @else
                            <li class="nav-item">
                                <a class="nav-link active" id="setPassword-tab" data-toggle="pill" href="#setPassword" role="tab" aria-controls="setPassword" aria-selected="true">
                                    set Password</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="tab-content" id="v-pills-tabContent">
                    @if($user->password)
                    <div class="tab-pane fade show active" id="changePassword" role="tabpanel" aria-labelledby="changePassword-tab">
                        <div class="card" id="settings-card">
                            <div class="card-header">
                                <h4>Change Password</h4>
                            </div>
                            <form id="changePasswordForm" action="{{route('account.setting.change-password')}}" method="post">
                                @csrf
                                <div class="card-body">
                                    <p class="text-muted">General settings such as, site title, site description,
                                        address and so on.</p>
                                    <div class="form-group row align-items-center">
                                        <label for="old_password" class="form-control-label col-sm-3 text-md-right">Old
                                            Password</label>
                                        <div class="col-sm-6 col-md-9">
                                            <input type="password" name="old_password" class="form-control" id="old_password">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="password" class="form-control-label col-sm-3 text-md-right">New
                                            Password</label>
                                        <div class="col-sm-6 col-md-9">
                                            <input type="password" name="password" class="form-control" id="password">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="confirm_password" class="form-control-label col-sm-3 text-md-right">Confirm Password</label>
                                        <div class="col-sm-6 col-md-9">
                                            <input type="password" name="confirm_password" class="form-control" id="confirm_password">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-whitesmoke text-md-right">
                                    <button type="submit" class="btn btn-success btn-submit"><i id="loader" class=""></i>Save
                                        Change</button>
                                    <button type="reset" class="btn btn-secondary close-button">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="tab-pane fade show active" id="setPassword" role="tabpanel" aria-labelledby="setPassword-tab">
                        <div class="card" id="settings-card">
                            <div class="card-header">
                                <h4>Set Password</h4>
                            </div>
                            <form id="setPasswordForm" action="{{route('account.setting.set-password')}}" method="post">
                                @csrf
                                <div class="card-body">
                                    <p class="text-muted">General settings such as, site title, site description,
                                        address and so on.</p>
                                    <div class="form-group row align-items-center">
                                        <label for="password" class="form-control-label col-sm-3 text-md-right">New
                                            Password</label>
                                        <div class="col-sm-6 col-md-9">
                                            <input type="password" name="password" class="form-control" id="password">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="confirm_password" class="form-control-label col-sm-3 text-md-right">Confirm Password</label>
                                        <div class="col-sm-6 col-md-9">
                                            <input type="password" name="confirm_password" class="form-control" id="confirm_password">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-whitesmoke text-md-right">
                                    <button type="submit" class="btn btn-success btn-submit"><i id="loader" class=""></i>Save
                                        Change</button>
                                    <button type="reset" class="btn btn-secondary close-button">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
</section>

@endsection
@section('scripts')
<script src="{{ asset('account/js/page/setting.js')}}"></script>
<script type="text/javascript">
    var changePasswordForm;
    var setPasswordForm;
</script>
@endsection