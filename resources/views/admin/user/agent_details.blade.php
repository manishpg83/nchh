<div class="modal-dialog modal-lg" role="document" id="viewuser">
    <div class="modal-content">
        <div class="modal-header pt-2 pb-2">
            <h5 class="modal-title" id="modellabel">{{$user->name}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-2 mb-3">
                        <div id="imagePreview">
                            <img src="{{$user->profile_picture}}" class="imagePreview thumbnail rounded-circle img-75"
                                id="preview" alt="{{$user->name}}" />
                        </div>
                    </div>
                    <div class="col-sm-10 mb-3">
                        <div class="row">
                            @if(!empty($user->phone))
                            <div class="col-sm-12">
                                <p><strong>Phone</strong> : {{$user->phone}}</p>
                            </div>
                            @endif
                            @if(!empty($user->email))
                            <div class="col-sm-12">
                                <p><strong>Email</strong> : {{$user->email}}</p>
                            </div>
                            @endif
                            @if(!empty($user->detail->address))
                            <div class="col-sm-12">
                                <p><strong>Address</strong> : {{$user->detail->address}}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Account Details</strong>
                         <p class="mt-2">Gender : {{$user->gender}}</p>
                        <p class="mt-2">City : {{$user->city}}</p>
                    </div>
                    <div class="col-sm-6">
                    </div>
                </div>
                <hr>
                <div class="row">
                    @foreach ($documents as $document)
                        <div class="col-sm-4 mt-3">
                            <a href="{{$document->agent_document_src}}" target="_blank"><strong>{{ $document->title }}</strong></a>
                            <img src="{{$document->agent_document_src}}" class="imagePreview thumbnail pt-2 w-100"
                                id="preview" alt="Identity Proof" />
                        </div>
                    @endforeach
                    <div class="col-sm-2">
                    </div>
                    <div class="col-sm-6 mt-3">
                        <strong>Message</strong>

                        <p class="small">Note* : If the profile is to be disapproved, please enter a reason for
                            disapproval</p>
                        <textarea class="form-control valid mt-2" type="text" name="message" spellcheck="false"
                            aria-invalid="false" id="reject-message" required></textarea>
                        <div class="small error mt-1" id="error-message"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="verifyAgentDetail({{$user->id}},'approved');"
                class="btn btn-mat btn-success btn-sm">Approved</button>
            <button type="button" onclick="verifyAgentDetail({{$user->id}},'reject');"
                class="btn btn-mat btn-danger btn-sm">Reject</button>
        </div>
    </div>
</div>