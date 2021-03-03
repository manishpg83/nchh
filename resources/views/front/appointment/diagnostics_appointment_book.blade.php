@extends('layouts.app')

@section('content')
<section class="bg-grey padding pt-5 appointment_container">
    <div class="container">
        <div class="row mt-5">
            <div class="col-12 col-md-3">
                <div class="card user_card">
                    <div class="card-header w-100">{{$title}}</div>
                    <div class="row card-body">
                        <div class="col-md-12 p-0"><img src="{{$profile->profile_picture}}" alt="" class="w-100 rounded"></div>
                        <div class="col-md-12">
                            <hr>
                            <h5>{{$profile->name}}</h5>
                            <hr>
                            <div class="card-text">
                                <div><i class="ion ion-ios-telephone"></i> {{$profile->phone}}</div>
                                <hr>
                                <div><i class="ion ion-ios-email"></i> {{$profile->email}}</div>
                                <hr>
                                <div><i class="fas fa-map-marker-alt"></i> {!!$profile->full_address!!}</div>
                            </div>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-9">
                <div class="card patient_card">
                    <form id="diagnosticsAppointmentForm" action="{{route('diagnostics.appointment.payment.order.create')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="diagnostics_id" value="{{$profile->id}}">
                        <input type="hidden" id="date" name="date" value="{{date('Y-m-d')}}">
                        <input type="hidden" id="time" name="time" value="">

                        <div class="card-header w-100">Patient Details</div>
                        @if($profile->practiceAvailable($profile->id))
                        <div id="practice_schedule"></div>
                        @else
                        <div class="border p-2">
                            <h6 class="text-center m-3">No Slots Available</h6>
                        </div>
                        @endif

                        <div class="card-header w-100">
                            <span>Diagnostics Services</span>
                            @if($profile->setting && $profile->setting->is_sample_pickup == 1)
                            <span class="float-right">
                                <div class="form-check">
                                    <input class="form-check-input services" type="checkbox" id="{{$profile->setting->sample_pickup_charge}}" name="sample_pickup" value="1">
                                    <label class="form-check-label text-danger" for="sample_pickup">
                                        Sample Pickup From Home ( {{$profile->setting->sample_pickup_charge}} )
                                    </label>
                                </div>
                            </span>
                            @endif
                        </div>
                        @if(!empty($profile->services) && $profile->services->count() > 0)
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="selectgroup selectgroup-pills">
                                        <div class="row">
                                            @forelse($profile->services as $key => $s)
                                            <label class="selectgroup-item col-sm-6" title="{{$s->information}}" data-toggle="tooltip">
                                                <div class="box-rac">
                                                    <input type="checkbox" name="services_ids[]" value="{{$s->id}}" id="{{$s->price}}" class="selectgroup-input services">
                                                    <span class="selectgroup-button">{{$s->name}}
                                                        <span class="badge badge-primary badge-pill mw-50 badge-price"><i class="fas fa-rupee-sign"></i> {{$s->price}}</span>
                                                    </span>
                                                </div>
                                            </label>
                                            @empty
                                            No Any Record Found.
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="border p-2">
                            <h6 class="text-center m-3">No Services Available</h6>
                        </div>
                        @endif
                        <div class="row card-body">

                            <div class="col-md-12 col-sm-12">
                                <h6 class="mb-3">Please provide following information:</h6>
                                <div class="form-group row">
                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Full Name<span class="text-danger">*</span></label>
                                        <input type="text" name="patient_name" class="form-control" placeholder="Enter your name" value="{{$user->name}}">
                                        @error('name')
                                        <label id="patient_name-error" class="error" for="patient_name">{{ $message }}</label>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Mobile<span class="text-danger">*</span></label>
                                        <input type="text" name="patient_phone" class="form-control disable" placeholder="Enter your phone number" value="+{{$user->dialcode.''.$user->phone}}" readonly>
                                        @error('phone')
                                        <label id="patient_phone-error" class="error" for="patient_phone">{{ $message }}</label>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Email<span class="text-danger">*</span></label>
                                        <input type="text" name="patient_email" class="form-control" placeholder="Enter your email address" value="{{$user->email}}">
                                        @error('email')
                                        <label id="patient_email-error" class="error" for="patient_email">{{ $message }}</label>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6 col-sm-12 mb-4">
                                        @if(!empty($profile->services))
                                        <span class="float-left">Selected Service charges : <strong><i class="fas fa-rupee-sign"></i> <span id="amount">0</span></strong></span>
                                        <input type="submit" value="Confirm" class="btn btn-primary btn-submit float-right">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</section>
@endsection
@section('page_script')
<script src="{{ asset('js/page/diagnostics_appointment_book.js')}}"></script>
<script type="text/javascript">
    var diagnosticsAppointmentForm = $('#diagnosticsAppointmentForm')

    $(".services").click(function(event) {
        var total = 0;
        var price = 0;
        $(".services:checked").each(function() {
            total += parseInt($(this).attr('id'));
        });

        $(".sample_pickup:checked").each(function() {
            price += parseInt($(this).attr('id'));
        });

        if (total == 0 && price == 0) {
            $('#amount').html(total + price);
        } else {
            $('#amount').html(total + price);
        }
    });

    var practice_schedule_div = $('#practice_schedule');
    var doctor_id = '{{$profile->id}}'
    var practice_id = '{{$practice->id}}';
    var load_practice_timing = "{{route('appointment.practice.timing.load')}}";
    var orderCreateUrl = "{{route('diagnostics.appointment.payment.order.create')}}";
    var orderVerifyUrl = "{{route('diagnostics.appointment.payment.order.verify')}}";
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