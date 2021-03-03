@if($module->count() > 0)
@foreach($module as $m)
<div class="col-md-12 col-lg-12 mb-3 module_{{$m->id}}">
    <div class="main-checkbox">
        <input type="checkbox" id="selectall_{{$m->id}}" class="all child-checkbox" onmouseover="checkAll('module_{{$m->id}}');"> <label for="selectall_{{$m->id}}"></label>
    </div>
    <strong class="tag-checkbox">{{$m->name}}</strong>
    <hr class="m-25">
    <div class="row">
        @foreach($m->route as $r)
        <div class="col-md-4 col-sm-4 mb-2 mr-3">
            <div><input type="checkbox" name="permission_status[]" class="child-checkbox" value="{{$r->id}}" id="{{$r->id}}" @if(!empty($checkedNodes) && in_array($r->id,$checkedNodes)) {{'checked'}} @else {{''}} @endif>
                <label for="{{$r->id}}" class="d-initial"><span class="tag-checkbox">{{$r->label}}</span></label></div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endif