@extends('layouts.app')

@section('content')
<section class="bg-grey padding pt-5 appointment_container">
    <div class="container">
        <div class="row mt-5">
            <div class="col-12 col-md-4">
                <div class="card user_card">
                    <div class="card-header w-100">{{$title}}</div>
                    <div class="row card-body">
                        <div class="col-md-3 p-0"><img src="{{$profile->profile_picture}}" alt="" class="w-100"></div>
                        <div class="col-md-9">
                            <h6>{{$profile->name}}</h6>
                            <div class="card-text">
                                <div>{{$profile->detail->degree}}</div>
                                @if(!empty($profile->detail->specialty_ids))
                                <span class="m-0">{{$profile->detail->specialty_name}}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(!empty($profile->practice))
                    <ul class="list-group list-group-flush">
                        @foreach($profile->practice as $key => $practice)
                        <li class="list-group-item p-0">
                            <label class="btn_select_clinic">
                                <input type="checkbox" name="practice_id" value="{{$practice->id}}" {{$key == 0 ? 'checked' : ''}}>
                            </label>
                            <div class="row card-body">
                                <div class="col-md-3 p-0"><img src="{{$practice->logo}}" alt="{{$practice->name}}" class="w-100"></div>
                                <div class="col-md-9">
                                    <h6>{{$practice->name}}</h6>
                                    <div class="card-text">
                                        <div>{{$practice->address}}</div>
                                        @if(!empty($profile->detail->specialty_ids))
                                        <span class="m-0">{{$profile->detail->specialty_name}}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="card patient_card">
                    <div class="card-header w-100">Patient Details</div>
                    @if($profile->practiceAvailable($profile->id))
                    <div id="practice_schedule"></div>
                    @else
                    <div class="border p-2">
                       <h6 class="text-center m-3">No Slots Available</h6>
                   </div>
                    @endif
                   <div class="row card-body">

                        <div class="col-md-12 col-sm-12">
                            <h6 class="mb-3">Please provide following information:</h6>
                            <form id="appointmentForm" action="POST" action="{{route('account.practice.store')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="doctor_id" name="doctor_id" value="{{$profile->id}}">
                                <input type="hidden" id="date" name="date" value="{{date('Y-m-d')}}">
                                <input type="hidden" id="time" name="time" value="">
                                <div class="form-group row">
                                    <!-- <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Are you consultant as*</label>
                                        <div class="radio">
                                            <label class="mr-1"><input type="radio" name="appointment_type" value="ONLINE" checked> Online</label>
                                            <label><input type="radio" name="appointment_type" value="INPERSON"> In Person</label>
                                        </div>

                                        @error('name')
                                        <label id="patient_name-error" class="error" for="patient_name">{{ $message }}</label>
                                        @enderror
                                    </div> -->
                                    <div class="col-md-6 col-sm-12 mb-4">
                                        <label for="name">Patient Name*</label>
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
                                    @if($profile->practiceAvailable($profile->id))
                                        <input type="submit" value="Confirm" class="btn btn-primary btn-submit">
                                    @endif
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
<script src="{{ asset('js/page/book_appointment.js')}}"></script>
<script type="text/javascript">
    var appointmentForm = $('#appointmentForm')
    var practice_schedule_div = $('#practice_schedule');
    var doctor_id = '{{$profile->id}}'
    var practice_id = $('.btn_select_clinic input:checkbox:checked').val();
    var load_practice_timing = "{{route('appointment.practice.timing.load')}}";
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