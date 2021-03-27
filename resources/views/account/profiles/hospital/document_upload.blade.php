<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="hospitalProfileForm" action="{{route('account.hospital.profile.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                @if(count($documents) > 0)
                    @foreach($documents as $key => $document)
                        <input type="hidden" name="document_id[]" value="{{ $document->id }}">
                    @endforeach
                    <div class="documentDiv">
                        @foreach($documents as $key => $document)
                            <div class="doc_div doc_div_{{ $key }}" data-id="{{ $key }}">
                                <div class="form-group mb-0 row">
                                    <div class="col-sm-12 mb-2">
                                        <label>Document Name*</label>
                                        <input type="text" name="document_name[{{ $key }}]" id="document_name[{{ $key }}]" class="form-control document_name" placeholder="Document Name"
                                            value="{{ $document->title }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-11 mt-3">
                                        <label>Document* (jpeg, png, jpg)</label>
                                        <input class="form-control document_proof" type="file" name="document_proof[{{ $key }}]" id="document_proof[{{ $key }}]" placeholder="Document Proof">
                                        <label class="document_file_name">{{ $document->doc_orig_name }}</label>
                                    </div>
                                    <div class="col-sm-1">
                                        @if($loop->first)
                                            <a href="javascript:void(0)" class="btn btn-primary btn-submit mt-5" onclick="addDocDiv()"><i class="fa fa-plus"></i></a>
                                        @else
                                            <a href="javascript:void(0)" class="btn btn-danger btn-submit mt-5" onclick="removeDocDiv({{ $key }})"><i class="fa fa-times"></i></a>
                                        @endif
                                    </div>
                                </div>
                                <hr/>
                            </div>
                        @endforeach
                    </div>
                @else
                    <input type="hidden" name="document_id[]" value="">
                    <div class="documentDiv">
                        <div class="doc_div doc_div_0" data-id="0">
                            <div class="form-group mb-0 row">
                                <div class="col-sm-12 mb-2">
                                    <label>Document Name*</label>
                                    <input type="text" name="document_name[0]" id="document_name[0]" class="form-control document_name" placeholder="Document Name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-11 mt-3">
                                    <label>Document* (jpeg, png, jpg)</label>
                                    <input class="form-control document_proof" type="file" name="document_proof[0]" id="document_proof[0]" placeholder="Document Proof">
                                </div>
                                <div class="col-sm-1">
                                    <a href="javascript:void(0)" class="btn btn-primary btn-submit mt-5" onclick="addDocDiv()"><i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                            <hr/>
                        </div>
                    </div>
                @endif
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" value="1" name="agree" id="agree">
                    <label class="custom-control-label" for="agree">I have read and agree to the <a href="{{ route('terms', ['type' => 'agent']) }}" class="terms" target="_blank">Terms and Conditions</a>.</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-submit"><i id="loader" class=""></i>Submit</button>
                <button type="button" class="btn btn-secondary close-button" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>