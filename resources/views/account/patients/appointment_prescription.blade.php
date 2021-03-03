<div class="col-12 mb-2">
    <span class="bullet"></span>
    <span class="text-job">Prescription</span>
</div>
<div class="col-12">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-md">
            <tr class="border-bottom small">
                <th>DRUG</th>
                <th class="text-center">FREQUENCY</th>
                <th class="text-center">DURATION</th>
                <th>INSTRUCTION</th>
            </tr>
            @if(!$appointment->prescriptions->isEmpty())
            @foreach($appointment->prescriptions as $p)
            <tr class="small">
                <td>{{$p->drug}}</td>
                <td class="text-center">{{$p->frequency}}</td>
                <td class="text-center">{{$p->duration}} day(s)</td>
                <td>{{$p->intake}}, {{$p->intake_instruction}}</td>
            </tr>
            @endforeach
            @endif
        </table>
    </div>
</div>
<div class="col-12">
    @if(checkPermission(['doctor']))
    <div id="send_prescription">
        @if($appointment->isPrescriptionShare($appointment->id))
        <div class="col-md-3 col-sm-3 mb-3 float-left">
            <select id="pharmacy_id" class="form-control select2">
                <option hidden value="">Select Pharmacy</option>
                @foreach ($pharmacy as $key => $value)
                <option value="{{$value->id}}">{{$value->name}} <small>({{$value->locality}})</small></option>
                @endforeach
            </select>
        </div>
        @if(!$appointment->prescriptions->isEmpty())
        <a class="btn btn-icon icon-left btn-primary mb-3 text-white send-prescription pointer" onclick="sendToPharmacy('{{$appointment->id}}')">Send Prescription To Pharmacy</a>
        @endif
        @else
        <p class="float-left ml-2 text-warning"><b>Recommanded pharmacy is {{$share_pharmacy->pharmacy->name}}, {{$share_pharmacy->pharmacy->address}}, {{$share_pharmacy->pharmacy->locality}}.<b></p>
        @endif
    </div>
    <button type="submit" class="btn btn-icon icon-left btn-primary mb-3 float-right btn-submit" onclick="editPrescription()">@if(!$appointment->prescriptions->isEmpty()) Edit @else Add @endif</button>
    @endif
</div>