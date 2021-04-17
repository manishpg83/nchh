<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modellabel">{{$title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form id="drugForm" action="{{route('account.drug.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-body pb-0">
                <div class="form-group mb-0 row">
                    <div class="col-sm-3 mb-2">
                        <label for="name">Name*</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter Drug Name">
                        <span class="text-danger">
                            <strong id="name-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-2 mb-2">
                        <label>Type*</label>
                        <select class="form-control" name="type" data-live-search="true" data-style="bg-white rounded-pill px-4 py-3 shadow-sm" onchange="showOtherType()">
                            <option value="">Select Type</option>
                            @foreach ($type as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2 mb-2 d-none showOtherTypeDiv">
                        <label>Other Type</label>
                        <input type="text" class="form-control" name="other_type" placeholder="Enter Other Type" required>
                    </div>                   
                    <div class="col-sm-2 mb-2">
                        <label>Unit*</label>
                        <select class="form-control" name="unit" id="unit" data-live-search="true" data-style="bg-white rounded-pill px-4 py-3 shadow-sm" onchange="showOtherUnit()">
                            <option value="">Select Unit</option>
                            @foreach ($unit as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2 mb-2 showOtherUnitDiv d-none">
                        <label>Other Unit</label>
                        <input type="text" class="form-control" name="other_unit" placeholder="Enter Other Unit" required>
                    </div>
                     <div class="col-sm-2 mb-2">
                        <label for="strength">Strength*</label>
                        <input type="number" name="strength" class="form-control" id="strength" placeholder="Enter Drug strength">
                        <span class="text-danger">
                            <strong id="strength-error"></strong>
                        </span>
                    </div>
                    <div class="col-sm-3 mb-2">
                        <label for="instructions">Instructions</label>
                        <textarea class="form-control p-0" name="instructions" id="instructions"></textarea>
                        <span class="text-danger">
                            <strong id="instructions-error"></strong>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-submit"><i id="loader" class=""></i>Submit</button>
                <button type="button" class="btn btn-secondary close-button" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>