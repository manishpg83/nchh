<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pb-0">
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-12">
                    <div class="card author-box card-primary">
                        <div class="card-body">
                            <div class="author-box-left">
                                <img alt="image" src="{{asset('images/default.png')}}" class="rounded-circle author-box-picture w-35">
                                <div class="clearfix"></div>
                            </div>
                            <div class="author-box-details ml-55">
                                <div class="author-box-name">
                                    <a href="javascript:;">{{$appointment->patient_name}}</a>
                                </div>
                                <div class="author-box-job"><i class="fas fa-phone small"></i>
                                    {{$appointment->patient_phone}}</div>
                                <div class="author-box-job"><i class="far fa-envelope"></i>
                                    {{$appointment->patient_email}}</div>
                                <div class="author-box-description">
                                    <p class="mb-0">
                                        <h6 class="mb-0">{{date('d M, Y h:i a', strtotime($appointment->start_time) )}} to {{date('h:i a', strtotime($appointment->end_time) )}}
                                        </h6>
                                        <p class="text-success mb-0"> {{$appointment->appointment_type}}</p>
                                        <p class="text-info mb-0">
                                            {!!getAppointmentStatus($appointment->status)!!}
                                            @if($appointment->is_sample_pickup && $appointment->is_sample_pickup == 1)
                                            <span class="badge badge-pill badge-info">Sample Pickup From Home</span>
                                            @endif
                                        </p>
                                    </p>
                                </div>
                                @if(checkPermission(['diagnostics']))
                                <div class="author-box-job"><strong>Services : </strong>{{$appointment->services_name}}</div>
                                @endif
                            </div>
                            <hr>
                            <div class="profile-widget-description pb-0 pt-0">
                                <div class="profile-widget-name">Appointment At
                                    <div class="text-muted d-inline font-weight-normal">
                                        <div class="slash"></div> {{$appointment->practice->name}}
                                    </div>
                                </div>
                                @if(checkPermission(['doctor']))
                                <div class="author-box-job">With <strong>{{$appointment->doctor->name}}</strong> at <strong>{{date('h:i a', strtotime($appointment->start_time) )}} </strong> for <strong> {{$totalDuration}} mins</strong>.</div>
                                @endif
                                @if(checkPermission(['diagnostics']))
                                <div class="author-box-job">at <strong>{{date('h:i a', strtotime($appointment->start_time) )}} </strong> for <strong> {{$totalDuration}} mins</strong>.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer pt-0">
            @if($appointment->status == 'pending' || $appointment->status == 'create')
            <a href="javascript:;" class="btn btn-danger btn-submit btn-sm f-16 m-l-5 m-r-5" onclick="deleteAppointment('{{$appointment->id}}')" data-toggle="tooltip" data-original-title="Cancel Appointment">Cancel Appointment</a>
            @endif
            @if(checkPermission(['doctor']) && ($appointment->status == 'attempt' || $appointment->status == 'completed'))
            <a href="{{route('account.patients.appointment.detail', [$appointment->patient->id, $appointment->patient->name_slug, $appointment->id])}}" class="btn btn-success btn-submit btn-sm f-16 m-l-5 m-r-5" data-toggle="tooltip" data-original-title="view Appointment. You can add prescription and appointment related files">Add Prescription</a>
            @endif
        </div>
        </form>
    </div>
</div>