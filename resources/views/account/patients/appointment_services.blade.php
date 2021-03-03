<div class="col-12 mb-2">
    <span class="bullet"></span>
    <span class="text-job">Prescription</span>
</div>
<div class="col-12">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-md">
            <tr class="border-bottom small">
                <th>SERVICE</th>
                <th class="text-center">INFORMATION</th>
                <th class="text-center">PRICE</th>
            </tr>
            @foreach($services as $s)
            <tr class="small">
                <td>{{$s->name}}</td>
                <td class="text-center">{{$s->information}}</td>
                <td class="text-center">{{$s->price}}</td> 
            </tr>
            @endforeach
        </table>
    </div>
</div>