<div class="modal-dialog modal-lg" role="document" id="viewuser">
    <div class="modal-content">
        <div class="modal-header pt-2 pb-2">
            <h5 class="modal-title" id="modellabel">{{$user->name}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="card">
            <div class="modal-body">
                <div class="card-body">
                    <div class="row">
                        <div class="author-box-left">
                            <img alt="image" src="{{$user->profile_picture}}"
                                class="img-100 rounded-circle author-box-picture">
                        </div>
                        <div class="form-group mb-0 col-md-6 col-12">
                            <label>Name</label>
                            <p>@if($user->name) {{$user->name}} @else <span class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row primarybox">
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>Email address</label>
                            <p>@if($user->email) {{$user->email}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>Gender</label>
                            <p>@if($user->gender) {{$user->gender}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>Date of birth</label>
                            <p>@if($user->dob) {{$user->dob}} @else <span class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>Blood group</label>
                            <p>@if($user->blood_group) {{$user->blood_group}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>Timezone</label>
                            <p>@if($user->timezone) {{$user->timezone}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                    </div>

                    <div class="card-header pl-0">
                        <h4>Address</h4>
                    </div>
                    <div class="row">
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>House No./ Street Name</label>
                            <p>@if($user->address) {{$user->address}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>City</label>
                            <p>@if($user->city) {{$user->city}} @else <span class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>State</label>
                            <p>@if($user->state) {{$user->state}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>Country</label>
                            <p>@if($user->country) {{$user->country}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                        <div class="form-group mb-0 col-md-4 col-12">
                            <label>Pincode</label>
                            <p>@if($user->pincode) {{$user->pincode}} @else <span
                                    class="badge badge-pill badge-secondary">Not
                                    Mentioned</span> @endif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>