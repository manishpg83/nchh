<div class="modal-dialog modal-lg" role="document" id="establishment_modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="card m-0">
                <div class="card-body neucrad_wizard_document">
                    <form id="establishmentForm" action="{{route('account.edit-profile',[$user->id])}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <h3>
                            <div class="wizard">
                                <div class="wizard-step">
                                    <div class="wizard-step-icon">
                                        <i class="fas fa-file-medical"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Location
                                    </div>
                                </div>
                            </div>
                        </h3>
                        <fieldset>
                            <div class="wizard-content mt-2">
                                <div class="wizard-pane">
                                    <input type="hidden" name="establishment_latitude" id="establishment_latitude">
                                    <input type="hidden" name="establishment_longitude" id="establishment_longitude">

                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Name*</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="establishment_name" value="@if(!empty($user->detail->establishment_name)){{$user->detail->establishment_name}}@endif" class="form-control required">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Address*</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="establishment_address" value="@if(!empty($user->detail->establishment_address)){{$user->detail->establishment_address}}@endif" class="form-control required">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label class="col-sm-2 text-md-right text-left">Location*</label>
                                        <div class="col-md-9">
                                            <small>Drag n drop the pin to your location:</small>
                                            <div class="location-map" id="location-map">
                                                <div style="height: 400px;" id="map_canvas"></div>
                                            </div>
                                        </div>
                                        <!-- <div class="col-sm-9">
                                            <div id="map" style="width: 100%;"></div>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <h3>
                            <div class="wizard">
                                <div class="wizard-step">
                                    <div class="wizard-step-icon">
                                        <i class="far fa-id-badge"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Timing
                                    </div>
                                </div>
                            </div>
                        </h3>
                        <fieldset>
                            <div id="timing_chart" class=""></div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script type="text/javascript">
    var userTimings = '{!! ($user->timing->schedule) !!}';
    // console.log(userTimings);
</script>
@endsection