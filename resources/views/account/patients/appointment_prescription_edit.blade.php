<form class="w-100" action="{{route('account.prescription.store')}}" method="post" id="prescriptionForm">
    @csrf
    <input type="hidden" name="appointment_id" value="{{$appointment->id}}">
    <div class="col-12 mb-2">
        <span class="bullet"></span>
        <span class="text-job">Prescription</span>
    </div>
    <div class="col-12">
        <table class="table prescription">
            <tbody id="drugsTable">
                <tr class="border-bottom small">
                    <th>DRUG</th>
                    <th>FREQUENCY</th>
                    <th>INTAKE</th>
                    <th>DURATION(Days)</th>
                    <th>INSTRUCTION</th>
                    <th></th>
                </tr>
                @if($appointment->prescriptions)
                @foreach($appointment->prescriptions as $p)
                <tr id="{{$p->id}}">
                    <td>
                        <input type="hidden" name="detail[{{$p->id}}][appointment_id]" value="{{$appointment->id}}">
                        <input type="hidden" id="drug_{{$p->id}}" name="detail[{{$p->id}}][drug]" value="{{$p->drug}}" readonly class="form-control">
                        <p class="small l-0">{{$p->drug}}</p>
                    </td>
                    <td style="width:23%">
                        <select id="frequency_{{$p->id}}" name="detail[{{$p->id}}][frequency]" class="form-control pr-1 pl-1" required>
                            <option hidden value="">Select frequency</option>
                            @if(config('view.Frequency'))
                            @foreach(config('view.Frequency') as $key => $value)
                            <option value="{{$value}}" @if($value==$p->frequency){{'selected'}}@endif>{{$value}}</option>
                            @endforeach
                            @endif
                        </select>
                    </td>
                    <td style="width:17%">
                        <select id="intake_{{$p->id}}" name="detail[{{$p->id}}][intake]" class="form-control pr-1 pl-1">
                            <option value="After Food" @if($p->intake == 'After Food'){{'selected'}}@endif>After Food</option>
                            <option value="Before Food" @if($p->intake == 'Before Food'){{'selected'}}@endif>Before Food</option>
                        </select>
                    </td>
                    <td style="width:13%">
                        <div class="form-group mb-0">
                            <div class="input-group day">
                                <input type="number" id="duration_{{$p->id}}" name="detail[{{$p->id}}][duration]" value="{{$p->duration}}" class="form-control pr-1 pl-1" required>
                            </div>
                        </div>
                    </td>
                    <td style="width:25%">
                        <div class="form-group mb-0">
                            <input type="text" id="intake_instruction_{{$p->id}}" name="detail[{{$p->id}}][intake_instruction]" value="{{$p->intake_instruction}}" class="form-control" placeholder="Add instruction">
                        </div>
                    </td>
                    <td style="width:5%"><a href="javascript:;" onclick="removeRow({{$p->id}})"><i class="fas fa-times"></i></a></td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
    <div class="col-12">
        <div class="col-md-3 col-sm-3 mb-3 float-left">
            <select id="drugs" class="form-control select2">
                <option hidden value="">Select Drug</option>
                @foreach ($drugs as $key => $value)
                <option value="{{$value->drug_name}}">{{$value->name}} ({{$value->strength}}{{$value->unit}})
                </option>
                @endforeach
            </select>
        </div>
        <a class="btn btn-icon icon-left btn-primary mb-3 text-white" onclick="addDrugs()">Add New Drug</a>
        <button type="submit" class="btn btn-icon icon-left btn-primary mb-3 float-right btn-submit">Save</button>
         <a href="javascript:;" class="btn btn-icon icon-left btn-secondary mb-3 text-white float-right mr-2 cancelEvent">Cancel</a>
    </div>
</form>