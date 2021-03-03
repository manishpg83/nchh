<tr id="{{$unique_id}}">
    <td>
        <input type="hidden" name="detail[{{$unique_id}}][appointment_id]" value="{{$appointment_id}}">
        <input type="hidden" id="drug_{{$unique_id}}" name="detail[{{$unique_id}}][drug]" value="{{$name}}" readonly class="form-control">
        <p class="small l-0">{{$name}}</p>
    </td>
    <td style="width:23%">
        <select id="frequency_{{$unique_id}}" name="detail[{{$unique_id}}][frequency]" class="form-control pr-1 pl-1" required>
            <option hidden value="">Select frequency</option>
            @if(config('view.Frequency'))
            @foreach(config('view.Frequency') as $key => $value)
            <option value="{{$value}}">{{$value}}</option>
            @endforeach
            @endif
        </select>
    </td>
    <td style="width:17%">
        <select id="intake_{{$unique_id}}" name="detail[{{$unique_id}}][intake]" class="form-control pr-1 pl-1">
            <option value="After Food">After Food</option>
            <option value="Before Food">Before Food</option>
        </select>
    </td>
    <td style="width:13%">
        <div class="form-group mb-0">
            <div class="input-group day">
                <input type="number" id="duration_{{$unique_id}}" name="detail[{{$unique_id}}][duration]" class="form-control pr-1 pl-1" required>
                <div class="invalid-feedback">
                    What's your name?
                </div>
            </div>
        </div>
    </td>
    <td style="width:25%">
        <div class="form-group mb-0">
            <input type="text" id="intake_instruction_{{$unique_id}}" name="detail[{{$unique_id}}][intake_instruction]" class="form-control" placeholder="Add instruction">
        </div>
    </td>
    <td style="width:5%"><a href="javascript:;" onclick="removeRow({{$unique_id}})"><i class="fas fa-times"></i></a></td>
</tr>