@if(!empty($doctors && $doctors->count() > 0))
<div class="list-header">Doctor</div>
@foreach($doctors as $doctor)
<li class="list-item">
    <a class="menu-list" href="{{Route('front.user.result',['id' => $doctor->id, 'type' => 'user', 'name' => $doctor->name])}}" onclick="setValue('{{$search}}')">
        <img class="search-profile" src="@if(!empty($doctor->profile_picture)){{$doctor->profile_picture}}@endif">
        <span>{{$doctor->name}}</span>
        <span class="text-muted role-tag">{{$doctor->role->name}}</span>
    </a>
</li>
@endforeach
@endif

@if(!empty($hospitals && $hospitals->count() > 0))
<div class="list-header">Hospital</div>
@foreach($hospitals as $hospital)
<li class="list-item">
    <a class="menu-list" href="{{Route('front.user.result',['id' => $hospital->id, 'type' => 'user', 'name' => $hospital->name])}}" onclick="setValue('{{$search}}')">
        <img class="search-profile" src="@if(!empty($hospital->detail->image_name)){{$hospital->detail->profile_picture}} @else {{asset('images/default.png')}} @endif">
        <span>{{$hospital->name}}</span>
        <span class="text-muted role-tag">{{$hospital->role->name}}</span>
    </a>
</li>
@endforeach
@endif

@if(!empty($clinics && $clinics->count() > 0))
<div class="list-header">Clinic</div>
@foreach($clinics as $clinic)
<li class="list-item">
    <a class="menu-list" href="{{Route('front.user.result',['id' => $clinic->id, 'type' => 'user', 'name' => $clinic->name])}}" onclick="setValue('{{$search}}')"><img class="search-profile" src="@if(!empty($clinic->detail->image_name)){{$clinic->detail->profile_picture}} @else {{asset('images/default.png')}} @endif"><span>{{$clinic->name}}</span>
        <span class="text-muted role-tag">{{$clinic->role->name}}</span>
    </a>
</li>
@endforeach
@endif

@if(!empty($specialities && $specialities->count() > 0))
<div class="list-header">Specialty</div>
@foreach($specialities as $specialty)
<li class="list-item">
    <a class="menu-list" href="{{Route('front.user.result',['id' => $specialty->id, 'type' => 'specialty', 'name' => $specialty->title])}}" onclick="setValue('{{$search}}')">
        <i class="ion-search menu-icon" aria-hidden="true"></i><span>{{$specialty->title}}</span>
        <span class="text-muted role-tag">Specialty</span>
    </a>
</li>
@endforeach
@endif

<li class="list-item">
    <a class="menu-list" href="{{Route('front.user.result',['id' => 0, 'type' => 'doctor', 'name' => $search])}}" onclick="setValue('{{$search}}')">
        <i class="ion-search menu-icon" aria-hidden="true"></i>
        <span>Doctor search with.. {{$search}}</span>
    </a>
</li>
<li class="list-item">
    <a class="menu-list" href="{{Route('front.user.result',['id' => 0, 'type' => 'clinic', 'name' => $search])}}" onclick="setValue('{{$search}}')"><i class="ion-search menu-icon" aria-hidden="true"></i>
        <span>Clinic search with.. {{$search}}</span>
    </a>
</li>
<li class="list-item">
    <a class="menu-list" href="{{Route('front.user.result',['id' => 0, 'type' => 'hospital', 'name' => $search])}}" onclick="setValue('{{$search}}')"><i class="ion-search menu-icon" aria-hidden="true"></i>
        <span>Hospital search with.. {{$search}}</span>
    </a>
</li>