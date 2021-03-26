<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">Verified Document</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body pb-0">
            @foreach($documents as $document)
            <div class="accordion">
                <div class="accordion-header" aria-expanded="true">
                    <h4>{{ $document->title }}</h4>
                </div>
                <div class="accordion-body collapse show" id="panel-body-1" data-parent="#accordion" style="">
                    <div class="row">
                        <div class="col-10">
                            <img alt="Identity Proof" style="max-width: 150px;" src="{{ $document->agent_document_src }}">
                        </div>
                        <div class="col-md-2">
                            <a href="{{ $document->agent_document_src }}" target="_blank" class="text-right"><i class="fa fa-download"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>