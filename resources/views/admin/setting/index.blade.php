@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('admin.dashboard.index')}}">Dashboard</a></div>
            <div class="breadcrumb-item">{{$pageTitle}}</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Overview</h2>
        <p class="section-lead">
            Organize and adjust all settings about this site.
        </p>

        <div class="row">
            @if(checkPermission(['admin']))
            <div class="col-lg-6">
                <div class="card card-large-icons">
                    <div class="card-icon bg-primary text-white">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="card-body">
                        <h4>Commission Setting</h4>
                        <p>Commission settings such as which agent how many percentage payment distribute so on.</p>
                        <a href="{{Route('admin.setting.commission')}}" class="card-cta">Commission Setting <i class="fas fa-chevron-right"></i></a>
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