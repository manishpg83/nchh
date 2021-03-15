<div class="modal-dialog modal-lg" role="document" id="verification_detail_modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Profile Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="card m-0">
                <div class="card-body neucrad_wizard">
                    <div class="wizard-content mt-2">
                        <div class="wizard-pane">
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Name</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->name }}
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Specialty</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->specialty_name }}
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 mt-2">Gender</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->gender }}
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Registration number</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->registration_number }}
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Registration Year</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->registration_year }}</label>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Liecence number</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->liecence_number }}</label>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Degree</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->degree }}</label>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">College/Institute</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->collage_or_institute }}</label>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Year Of completion</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->year_of_completion }}</label>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-sm-3">Year of experience</label>
                                <div class="col-sm-9">
                                    <label class="control-label">{{ $user->detail->experience }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>