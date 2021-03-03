<div class="modal-dialog modal-xl modal-xxl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body bg-whitesmoke">
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-6">
                    <div class="card author-box card-primary">
                        <div class="card-body">
                            <div class="author-box-left">
                                <img alt="image" src="{{asset('images/default.png')}}" class="rounded-circle author-box-picture">
                                <div class="clearfix"></div>
                            </div>
                            <div class="author-box-details">
                                <div class="author-box-name">
                                    <a href="javascript:;">{{$appointment->patient_name}}</a>
                                </div>
                                <div class="author-box-job"><i class="fas fa-phone small"></i>
                                    {{$appointment->patient_phone}}</div>
                                <div class="author-box-job"><i class="far fa-envelope"></i>
                                    {{$appointment->patient_email}}</div>
                                <div class="author-box-description">
                                    <p class="mb-0">
                                        <h6 class="mb-0">{{date('d M, Y h:i a', strtotime($appointment->start_time) )}}
                                        </h6>
                                        <p class="text-success mb-0">{{$appointment->appointment_type}}</p>
                                        <p class="text-info mb-0">
                                            {!!getAppointmentStatus($appointment->status)!!}
                                            @if($appointment->is_sample_pickup && $appointment->is_sample_pickup == 1)
                                            <span class="badge badge-pill badge-info">Sample Pickup From Home</span>
                                            @endif
                                        </p>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-12 col-lg-6">
                    <div class="card profile-widget">
                        <div class="profile-widget-header mb-0">
                            <img alt="image" src="{{$appointment->practice->addedBy->profile_picture}}" class="rounded-circle profile-widget-picture">
                        </div>
                        <div class="profile-widget-description pb-0 pt-0">
                            <div class="profile-widget-name">Appointment At
                                <div class="text-muted d-inline font-weight-normal">
                                    <div class="slash"></div> {{$appointment->practice->name}}
                                </div>
                            </div>
                            <div class="author-box-job"><i class="fas fa-phone small"></i>
                                {{$appointment->practice->phone}}</div>
                            <div class="author-box-job"><i class="far fa-envelope"></i>
                                {{$appointment->practice->email}}</div>
                            <div class="author-box-job"><i class="fas fa-map-marker-alt"></i>
                                {{ucwords($appointment->practice->locality)}}, {{ucwords($appointment->practice->city)}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="activities">
                        <div class="activity">
                            <div class="activity-detail w-100">
                                <div class="row" id="appointmentPrescription">
                                    <div class="col-12 mb-2">
                                        <span class="bullet"></span>
                                        <span class="text-job">Prescription</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover table-md">
                                                <tr class="small">
                                                    <th>SERVICE</th>
                                                    <th class="text-center">Information</th>
                                                    <th class="text-center">Price</th>
                                                </tr>
                                                @foreach($services as $s)
                                                <tr class="small">
                                                    <td>{{$s->name}}</td>
                                                    <td class="text-center">{{$s->information}}</td>
                                                    <td class="text-center">{{$s->price}}</td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="activities">
                            <div class="activity">
                                <div class="activity-detail w-100">
                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <span class="bullet"></span>
                                            <span class="text-job">Files</span>
                                        </div>
                                        <div class="col-12" id="appointmentFile">
                                            @if($appointment->files)
                                            <div class="gallery">
                                                <div class="row col-md-12 chocolat-parent">
                                                    @foreach($appointment->files as $file)
                                                    <a class="chocolat-image col-1 p-1" href="{{$file->filename}}" title="">
                                                        <img src="{{$file->filename}}" class="img-thumbnail" width="100%">
                                                    </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>