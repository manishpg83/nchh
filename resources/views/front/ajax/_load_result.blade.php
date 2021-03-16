@if(isset($render_product))

@forelse($users as $user)
<div class="col-md-12 col-sm-12 mb-4">
    <div class="card">
        <div class="row no-gutters">
            <div class="col-">
                <img class="card-img" src="{{$user->profile_picture}}" alt="{{$user->name}}" style="width: 220px;">
            </div>
            <div class="col-sm-7">
                <div class="card-body">
                    <h5 class="card-title mb-1">{{$user->name}}</h5>
                    @if(isset($user->detail->specialty_name))
                    <div class="font-15px text-secondary w-100">{{$user->detail->specialty_name}}</div>
                    @endif

                    @if(isset($user->detail->experience))
                    <div class="font-13px text-dark w-100">{{$user->detail->experience}} {{$user->detail->experience > 1 ? 'Years of experience' : 'Year of experience' }}</div>
                    @endif

                    @php
                    if($user->role->keyword == "doctor"){
                    $practice = !empty($user->practice->count()) ? $user->practice : [];
                    }else{
                    $practice = ($user->practiceAsStaff->count() > 0) ? $user->practiceAsStaff : [];
                    }
                    @endphp
                    @if(!empty($practice))
                    <div class="clinic_details font-14px mt-2">
                        <strong>{{$practice[0]->locality.', '.$practice[0]->city}}</strong>
                        <span class="bullet"></span> {{$practice[0]->name}}
                        @php
                        if($practice[0]->doctor_id == $practice[0]->added_by){
                        $at = "clinic";
                        }else{
                        $at = isset($practice[0]->addedBy->role->name) ? strtolower($practice[0]->addedBy->role->name) : '';
                        }
                        @endphp
                        <div class="text-mute">{{($practice->min('fees') == $practice->max('fees')) ?  '₹'.$practice->min('fees') : ('₹'.$practice->min('fees') .' ~ ₹'.$practice->max('fees'))}} Consultation fee at {{$at}}</div>
                    </div>
                    @endif

                    <!-- @if(isset($user->practice[0]))
                    <div class="clinic_details font-14px mt-2">
                        <strong>{{$user->practice[0]->locality.', '.$user->practice[0]->city}}</strong>
                        <span class="bullet"></span> {{$user->practice[0]->name}}
                        @php
                        if($user->practice[0]->doctor_id == $user->practice[0]->added_by){
                        $at = "clinic";
                        }else{
                        $at = isset($user->practice[0]->addedBy->role->name) ? strtolower($user->practice[0]->addedBy->role->name) : '';
                        }
                        @endphp
                        <div class="text-mute">₹{{$user->practice[0]->fees}} Consultation fee at {{$at}}</div>
                    </div>
                    @endif -->
                </div>

                <div class="card-footer">
                    <a href="{{route('appointment.index',[$user->id,$user->name_slug])}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Book Appointment</a>
                    <a href="{{route('appointment.online_consult',[$user->id,$user->name_slug])}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Video Consultation</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <div class="col-md-4 col-sm-12 mb-4">
    <div class="card">
        <img class="card-img-top" src="{{$user->profile_picture}}" alt="{{$user->name}}">
        <div class="card-body p-2">
            <h5 class="card-title mb-1">{{$user->name}}</h5>
            @if(isset($user->detail->specialty_name))
            <div class="font-15px text-secondary w-100">{{$user->detail->specialty_name}}</div>
            @endif

            @if(isset($user->detail->experience))
            <div class="font-13px text-dark w-100">{{$user->detail->experience}} {{$user->detail->experience > 1 ? 'Years of experience' : 'Year of experience' }}</div>
            @endif

            @if(isset($user->practice[0]))
            <div class="clinic_details font-14px mt-2">
                <strong>{{$user->practice[0]->locality.', '.$user->practice[0]->city}}</strong>
                <span class="bullet"></span> {{$user->practice[0]->name}}
                @php
                if($user->practice[0]->doctor_id == $user->practice[0]->added_by){
                $at = "clinic";
                }else{
                $at = isset($user->practice[0]->addedBy->role->name) ? strtolower($user->practice[0]->addedBy->role->name) : '';
                }
                @endphp
                <div class="text-mute">₹{{$user->practice[0]->fees}} Consultation fee at {{$at}}</div>
            </div>
            @endif

        </div>
        <div class="card-footer">
            <a href="{{route('appointment.index',[$user->id,$user->name_slug])}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Book Appointment</a>
            <a href="{{route('appointment.online_consult',[$user->id,$user->name_slug])}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Video Consultation</a>
        </div>
    </div>
</div> -->
@empty
<div class="col-sm-12 mt-3">
    <!-- <h3 style="text-align: center"><i class="far fa-clock mr-3"></i>No data fount. Please try again...</h3> -->
    <h5 class="text-warning m-5 text-center">No Record Found. Please try again...</h5>
</div>
@endforelse
@else
@if(!empty($users) && $users->count() > 0)
<section class="row" id="child_container">
    @forelse($users as $user)
    <div class="col-md-12 col-sm-12 mb-4">
        <div class="card">
            <div class="row no-gutters">
                <div class="col-">
                    <img class="card-img" src="{{$user->profile_picture}}" alt="{{$user->name}}" style="width: 230px;">
                </div>
                <div class="col-sm-9">
                    <div class="card-body">
                        <a href="{{$user->getProfileUrl($user->role->keyword)}}" class="text-dark" style="text-decoration: none;">
                            <h5 class="card-title mb-1">{{$user->name}}</h5>
                        </a>

                        @if(isset($user->detail->specialty_name))
                        <div class="font-15px text-secondary w-100">{{$user->detail->specialty_name}}</div>
                        @endif

                        @if(isset($user->detail->experience))
                        <div class="font-13px text-dark w-100">{{$user->detail->experience}} {{$user->detail->experience > 1 ? 'Years of experience' : 'Year of experience' }}</div>
                        @endif

                        @php
                        if($user->role->keyword == "doctor"){
                        $practice = !empty($user->practice->count()) ? $user->practice : [];
                        }else{
                        $practice = ($user->practiceAsStaff->count() > 0) ? $user->practiceAsStaff : [];
                        }
                        @endphp
                        @if(!empty($practice))
                        <div class="clinic_details font-14px mt-2">
                            <strong>{{$practice[0]->locality.', '.$practice[0]->city}}</strong>
                            <span class="bullet"></span> {{$practice[0]->name}}
                            @php
                            if($practice[0]->doctor_id == $practice[0]->added_by){
                            $at = "clinic";
                            }else{
                            $at = isset($practice[0]->addedBy->role->name) ? strtolower($practice[0]->addedBy->role->name) : '';
                            }
                            @endphp
                            <div class="text-mute">{{($practice->min('fees') == $practice->max('fees')) ?  '₹'.$practice->min('fees') : ('₹'.$practice->min('fees') .' ~ ₹'.$practice->max('fees'))}} Consultation fee at {{$at}}</div>
                        </div>
                        @endif
                        
                        <div class="rating_box mt-1" data-rating="{{$user->average_rating}}"></div>

                        <!-- @if(!empty($user->ratings) && $user->ratings->count() > 0)
                        <div class="font-13px text-dark mt-1">
                            <span class="text-success font-weight-bold"><i class="ion ion-thumbsup"></i> {{$user->rating_percent}}%</span>
                            <span class="text-secondary">({{$user->ratings->count() > 1 ? $user->ratings->count().' rates' : $user->ratings->count().' rate'}})</span>
                        </div>
                        @endif -->
                    </div>

                    <div class="card-footer">
                        <a href="{{$user->getProfileUrl($user->role->keyword)}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Book Appointment</a>
                        <a href="{{route('appointment.online_consult',[$user->id,$user->name_slug])}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Video Consultation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="col-md-4 col-sm-12 mb-4">
        <div class="card">
            <img class="card-img-top" src="{{$user->profile_picture}}" alt="{{$user->name}}">
            <div class="card-body p-2">
                <h5 class="card-title mb-1">{{$user->name}}</h5>
                @if(isset($user->detail->specialty_name))
                <div class="font-15px text-secondary w-100">{{$user->detail->specialty_name}}</div>
                @endif

                @if(isset($user->detail->experience))
                <div class="font-13px text-dark w-100">{{$user->detail->experience}} {{$user->detail->experience > 1 ? 'Years of experience' : 'Year of experience' }}</div>
                @endif

                @if(isset($user->practice[0]))
                <div class="clinic_details font-14px mt-2">
                    <strong>{{$user->practice[0]->locality.', '.$user->practice[0]->city}}</strong>
                    <span class="bullet"></span> {{$user->practice[0]->name}}
                    @php
                    if($user->practice[0]->doctor_id == $user->practice[0]->added_by){
                    $at = "clinic";
                    }else{
                    $at = isset($user->practice[0]->addedBy->role->name) ? strtolower($user->practice[0]->addedBy->role->name) : '';
                    }
                    @endphp
                    <div class="text-mute">₹{{$user->practice[0]->fees}} Consultation fee at {{$at}}</div>
                </div>
                @endif

            </div>
            <div class="card-footer">
                <a href="{{route('appointment.index',[$user->id,$user->name_slug])}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Book Appointment</a>
                <a href="{{route('appointment.online_consult',[$user->id,$user->name_slug])}}" class="btn btn-outline-primary btn-sm mb-1"><i class="far fa-comments"></i> Video Consultation</a>
            </div>
        </div>
    </div> -->
    @empty
    <div class="col-sm-12 mt-3">
        <!-- <h3 style="text-align: center"><i class="far fa-clock mr-3"></i>No data fount. Please try again...</h3> -->
        <h5 class="text-warning m-5 text-center">No Record Found. Please try again...</h5>
    </div>
    @endforelse
</section>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
@else
<div class="row">
    <div class="col-md-12 col-lg-12">
        <h5 class="text-warning m-5 text-center">No Record Found. Please try again...</h5>
    </div>
</div>
@endif
@endif