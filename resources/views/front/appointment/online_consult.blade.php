@extends('layouts.app')

@section('content')
<section class="bg-grey padding pt-5 appointment_container">
    <div class="container">
        <div class="row mt-5">

            <div class="col-12 col-md-6">
                <div class="card user_card">
                    <div class="card-header w-100">{{$title}}
                    </div>
                    <div class="row card-body m-0">
                        <div class="col-md-3 p-0"><img src="{{$doctor->profile_picture}}" alt="" class="w-100"></div>
                        <div class="col-md-9">
                            <h6>{{$doctor->name}}</h6>
                            <div class="card-text">
                                <div>{{$doctor->detail->degree}}</div>
                                @if(!empty($doctor->detail->specialty_ids))
                                <span class="m-0">{{$doctor->detail->specialty_name}}</span>
                                @endif
                                @if($doctor->detail->experience)
                                <h6 class="title f-sm">{{$doctor->detail->experience}} years of experience</h6>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row card-body" id="practice_schedule">
                    <div class="col-md-12 p-0">
                        @if($schedule)
                        <ul class="nav nav-tabs scroll_tabs_theme_light" role="tablist">
                            @foreach($schedule as $key => $s)
                            <a href="javascript:;" data-id="#{{$s['id']}}" data-value="{{$s['date']}}" role="tab" data-toggle="tab" class="{{$key == 0 ? 'active': ''}}">
                                <li>{{$s['title']}}</li>
                            </a>
                            @endforeach
                        </ul>
                        <div class="tab-content">
                            @foreach($schedule as $key => $s)
                            <div role="tabpanel" class="tab-pane {{$key == 0 ? 'active show': ''}}" id="{{$s['id']}}">
                                <div class="border p-2">
                                    @if(!empty($s['slot']))
                                    <div class="schedule_time">
                                        @foreach($s['slot'] as $slot)
                                        @if(in_array($slot['time']['start_time'],array_column($s['booked_slot'], 'start_time')) || in_array($slot['time']['end_time'],array_column($s['booked_slot'], 'end_time')))
                                        <span class="font-weight-bold badge badge-primary badge-outlined p-2 m-1 allocated">{{$slot['time']['start_time']}}</span>
                                        @else
                                        <a href="javascript:;" class="font-weight-bold badge badge-primary badge-outlined p-2 m-1" data-id="{{$slot['practice_id']}}" data-value="{{$slot['time']['start_time']}}">{{$slot['time']['start_time']}}</a>
                                        @endif
                                        @endforeach
                                    </div>
                                    @else
                                    <h6 class="text-center m-3">{{$s['slot_available']}}</h6>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active show">
                                <div class="border p-2">
                                    <h6 class="text-center m-3">No Slots Available</h6>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

            </div>

            <div class="col-12 col-md-6">
                <div class="card patient_card">
                    <div class="card-header w-100">
                        Patient Details
                        <br><small>Please provide following information</small>
                    </div>

                    <div class="row card-body">

                        <div class="col-md-12 col-sm-12">
                            <form id="bookVideoConsultForm" action="POST" action="{{route('account.practice.store')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="doctor_id" name="doctor_id" value="{{$doctor->id}}">
                                <div class="form-group row">
                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Full Name*</label>
                                        <input type="text" name="patient_name" class="form-control" placeholder="Enter your name" value="{{$user->name}}">
                                        @error('name')
                                        <label id="patient_name-error" class="error" for="patient_name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Mobile*</label>
                                        <input type="text" name="patient_phone" class="form-control disable" placeholder="Enter your phone number" value="+{{$user->dialcode.''.$user->phone}}">
                                        @error('phone')
                                        <label id="patient_phone-error" class="error" for="patient_phone">{{ $message }}</label>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Email*</label>
                                        <input type="text" name="patient_email" class="form-control" placeholder="Enter your email address" value="{{$user->email}}">
                                        @error('email')
                                        <label id="patient_email-error" class="error" for="patient_email">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <input type="submit" value="Confirm" class="btn btn-primary btn-submit">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection
@section('page_script')
<script src="{{ asset('js/page/book_video_consult.js')}}"></script>
<script type="text/javascript">
    var bookVideoConsultForm = $('#bookVideoConsultForm')
    var practice_schedule_div = $('#practice_schedule');
    var doctor_id = '{{$doctor->id}}'
    load_timeSlot();

    var orderCreateUrl = "{{route('appointment.payment.order.create')}}";
    var orderVerifyUrl = "{{route('appointment.payment.order.verify')}}";
    var key_id = "{{config('razorpay.razor_key')}}";
    var options = {
        "key": key_id,
        "name": "{{config('razorpay.merchant_name','NC Health Hub')}}",
        "image": "{{asset('images/favicon/neucrad.png')}}",
        "prefill": {
            "name": "{{Auth::user()->name}}",
            "email": "{{Auth::user()->email}}"
        },
        "theme": {
            "color": "#398bf7"
        }
    };
</script>
@endsection