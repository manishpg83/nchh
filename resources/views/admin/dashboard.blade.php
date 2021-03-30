@extends('admin.layouts.master')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{$pageTitle}}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{route('admin.dashboard.index')}}">{{$pageTitle}}</a></div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$doctors['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$doctors['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$doctors['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$clinic['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-clinic-medical"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4> {{$clinic['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$clinic['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
              <a href="{{$hospital['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$hospital['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$hospital['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
             <a href="{{$pharmacy['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-briefcase-medical"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4> {{$pharmacy['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$pharmacy['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$diagnostics['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$diagnostics['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$diagnostics['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$agent['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$agent['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$agent['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        

        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$doctor_verification_pending['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$doctor_verification_pending['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$doctor_verification_pending['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$clinic_verification_pending['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$clinic_verification_pending['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$clinic_verification_pending['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$hospital_verification_pending['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$hospital_verification_pending['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$hospital_verification_pending['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$pharmacy_verification_pending['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$pharmacy_verification_pending['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$pharmacy_verification_pending['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$diagnostics_verification_pending['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$diagnostics_verification_pending['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$diagnostics_verification_pending['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <a href="{{$agent_verification_pending['navigation']}}">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{$agent_verification_pending['title']}}</h4>
                        </div>
                        <div class="card-body">
                            <span id="count">{{$agent_verification_pending['count']}}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-12 col-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Recent Doctors</h4>
                    <div class="card-header-action">
                        <a href="{{$doctors['navigation']}}" class="btn btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body doctor-list">
                    <ul class="list-unstyled list-unstyled-border">
                        @foreach($recent_doctors as $d)
                        <li class="media mb-0">
                            <img class="mr-3 rounded-circle" width="50" src="{{$d->profile_picture}}" alt="avatar">
                            <div class="media-body">
                                <div class="float-right text-primary small">{{$d->created_at->format('d M')}}</div>
                                <div class="text-dark">{{$d->name}}</div>
                                <div class="small">{{$d->detail->specialty_name}}</div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-12 col-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Statistics</h4>
                    <div class="card-header-action">

                    </div>
                </div>
                <div class="card-body">
                    <canvas id="doctorChart" height="182"></canvas>
                    <div class="statistic-details mt-sm-4">
                        <div class="statistic-details-item">
                            <div class="detail-value" id="today"></div>
                            <div class="detail-name">Today's Register</div>
                        </div>
                        <div class="statistic-details-item">
                            <div class="detail-value" id="week"></div>
                            <div class="detail-name">This Week's Register</div>
                        </div>
                        <div class="statistic-details-item">
                            <div class="detail-value" id="month"></div>
                            <div class="detail-name">This Month's Register</div>
                        </div>
                        <div class="statistic-details-item">
                            <div class="detail-value" id="year"></div>
                            <div class="detail-name">This Year's Register</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>
@endsection
@section('page_script')
<script type="text/javascript" src="{{asset('admin/js/dashboard.js')}}"></script>
<script type="text/javascript">
var doctorChartData = "{{Route('admin.dashboard.doctor.data')}}";
</script>
@endsection