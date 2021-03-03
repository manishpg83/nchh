@extends('account.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Overview</h2>
        <p class="section-lead">
            Organize and adjust all settings about this site.
        </p>

        <div class="row">
            <div class="col-lg-6">
                <div class="card card-large-icons">
                    <div class="card-icon bg-primary text-white">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="card-body">
                        <h4>General Setting</h4>
                        <p>General settings such as Change Password, Set Password so on.</p>
                        <a href="{{Route('account.setting.general')}}" class="card-cta">Setting <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
            
            @if(isAuthorize('account.setting.consultant') && checkPermission(['doctor','diagnostics'])) 
            <div class="col-lg-6">
                <div class="card card-large-icons">
                    <div class="card-icon bg-primary text-white">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="card-body">
                        <h4>Consultant Setting</h4>
                        <p>Consultant settings such as consultant, duration, visibility.</p>
                        <a href="{{Route('account.setting.consultant')}}" class="card-cta">Setting <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</section>

@endsection
@section('scripts')
<script type="text/javascript">
</script>
@endsection
