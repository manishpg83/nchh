@if(!empty($doctors && $doctors->count() > 0))
<div class="list-header">Doctor</div>
@foreach($doctors as $doctor)
<li class="list-item">
    <a class="menu-list" href="{{$doctor->doctor_profile_url}}" onclick="setValue('{{$search}}')">
        <img class="search-profile p-0" src="@if(!empty($doctor->profile_picture)){{$doctor->profile_picture}}@endif">
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
    <a class="menu-list" href="{{$hospital->getProfileUrl('hospital')}}">
        <img class="search-profile p-0" src="{{$hospital->profile_picture}}">
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
    <a class="menu-list" href="{{$clinic->getProfileUrl('clinic')}}"><img class="search-profile p-0" src="{{$clinic->profile_picture}}"><span>{{$clinic->name}}</span>
        <span class="text-muted role-tag">{{$clinic->role->name}}</span>
    </a>
</li>
@endforeach
@endif

@if(!empty($diagnostics && $diagnostics->count() > 0))
<div class="list-header">Diagnostics</div>
@foreach($diagnostics as $d)
<li class="list-item">
    <a class="menu-list" href="{{$d->getProfileUrl('diagnostics')}}"><img class="search-profile p-0" src="{{$d->profile_picture}}"><span>{{$d->name}}</span>
        <span class="text-muted role-tag">{{$d->role->name}}</span>
    </a>
</li>
@endforeach
@endif

@if(!empty($specialities && $specialities->count() > 0))
<div class="list-header">Specialty</div>
@foreach($specialities as $specialty)
<li class="list-item">
    <a class="menu-list" href="{{Route('home.search',['speciality' => $specialty->id, 'search' => $specialty->title])}}" onclick="setValue('{{$search}}')">
        <!-- <i class="ion-search menu-icon" aria-hidden="true"></i> -->
        <img class="search-profile p-0" src="{{$specialty->image}}">
        <span>{{$specialty->title}}</span>
        <span class="text-muted role-tag">Specialty</span>
    </a>
</li>
@endforeach
@endif

<li class="list-item">
    <a class="menu-list" href="{{Route('home.search',['type' => 'doctor', 'keyword' => $search])}}" onclick="setValue('{{$search}}')">
        <i class="ion-search menu-icon" aria-hidden="true"></i>
        <span>Doctor search with.. {{$search}}</span>
    </a>
</li>
<li class="list-item">
    <a class="menu-list" href="{{Route('home.search',['type' => 'clinic', 'keyword' => $search])}}" onclick="setValue('{{$search}}')"><i class="ion-search menu-icon" aria-hidden="true"></i>
        <span>Clinic search with.. {{$search}}</span>
    </a>
</li>
<li class="list-item">
    <a class="menu-list" href="{{Route('home.search',['type' => 'hospital', 'keyword' => $search])}}" onclick="setValue('{{$search}}')"><i class="ion-search menu-icon" aria-hidden="true"></i>
        <span>Hospital search with.. {{$search}}</span>
    </a>
</li>