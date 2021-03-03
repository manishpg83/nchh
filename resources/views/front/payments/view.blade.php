<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
        </div>
        <div class="modal-body">
            <div class="card m-0 p-0">
                <h6 class="text-primary mb-4 w-100">Payment Details:</h6>
                <div class="form-group row">

                    @if(isset($payment->user->name))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Name:</label>
                        <div>{{$payment->user->name}}</div>
                    </div>
                    @endif

                    @if($payment->invoice_id)
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Invoice ID:</label>
                        <div>{{$payment->invoice_id}}</div>
                    </div>
                    @endif

                    @if($payment->order_id)
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Order ID:</label>
                        <div>{{$payment->order_id}}</div>
                    </div>
                    @endif

                    @if($payment->payment_id)
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Payment ID:</label>
                        <div>{{$payment->payment_id}}</div>
                    </div>
                    @endif

                    @if($payment->payment_id)
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Amount:</label>
                        <div>{{$payment->payable_amount}}</div>
                    </div>
                    @endif

                </div>

                <h6 class="text-primary mb-4 w-100">Appointment Details:</h6>
                <div class="form-group row">

                    @if(isset($payment->appointment->doctor->name))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Doctor Name:</label>
                        <div>{{$payment->appointment->doctor->name}}</div>
                    </div>
                    @endif

                    @php
                    $details = $payment->appointment->getLocationDetail($payment->appointment);
                    @endphp

                    <!-- Doctor Practice Details -->
                    @if(isset($payment->appointment->practice) && $payment->appointment->appointment_from == "Practice")

                    @if(isset($payment->appointment->practice->name))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Clinic Name:</label>
                        <div>{{$payment->appointment->practice->name}}</div>
                    </div>
                    @endif

                    @if(isset($payment->appointment->practice->phone))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Contact No:</label>
                        <div>{{$payment->appointment->practice->phone}}</div>
                    </div>
                    @endif

                    @endif

                    @if(isset($payment->appointment->start_time))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Appointment Time:</label>
                        <div>{{$payment->appointment->start_time}}</div>
                    </div>
                    @endif

                    @if(isset($payment->appointment->start_time))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Appointment Type:</label>
                        <div>{{($payment->appointment->appointment_type == "INPERSON" ? "In Person" : "Online")}}</div>
                    </div>
                    @endif

                    @php
                    $address = $payment->appointment->getLocationAddress($payment->appointment);
                    @endphp
                    @if(isset($address))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Address:</label>
                        <div>{!!$address!!}</div>
                    </div>
                    @endif
                </div>

                <h6 class="text-primary mb-4 w-100">Patient Details:</h6>
                <div class="form-group row">
                    @if(isset($payment->appointment->patient_name))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Patient Name:</label>
                        <div>{{$payment->appointment->patient_name}}</div>
                    </div>
                    @endif

                    @if(isset($payment->appointment->patient_email))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Patient Email:</label>
                        <div>{{$payment->appointment->patient_email}}</div>
                    </div>
                    @endif

                    @if(isset($payment->appointment->patient_phone))
                    <div class="col-sm-4 mb-3">
                        <label class="font-weight-bold text-dark">Patient Phone:</label>
                        <div>{{$payment->appointment->patient_phone}}</div>
                    </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>