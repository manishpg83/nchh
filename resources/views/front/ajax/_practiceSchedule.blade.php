<div class="row card-body">
    <div class="col-md-12">
        @if($schedule)
        <ul class="nav nav-tabs scroll_tabs_theme_light" role="tablist">
            @foreach($schedule as $key => $s)
            <a href="javascript:;" data-id="#{{$s['id']}}" data-value="{{$s['date']}}" role="tab" data-toggle="tab" class="{{$key == 0 ? 'active': ''}}">
                <li>{{$s['title']}}</li>
            </a>
            @endforeach
        </ul>
        <div class="tab-content">
            @foreach($schedule as $key => $s)
            <div role="tabpanel" class="tab-pane {{$key == 0 ? 'active show': ''}}" id="{{$s['id']}}">
                <div class="border p-2">
                    @if(!empty($s['slot']))
                    <div class="schedule_time">
                        @foreach($s['slot'] as $slot)
                        @php
                        @endphp
                        @if(in_array($slot['start_time'],array_column($s['booked_slot'], 'start_time')) || in_array($slot['end_time'],array_column($s['booked_slot'], 'end_time')))
                        <span class="font-weight-bold badge badge-primary badge-outlined p-2 m-1 allocated">{{$slot['start_time']}}</span>
                        @else
                        <a href="javascript:;" class="font-weight-bold badge badge-primary badge-outlined p-2 m-1" data-value="{{$slot['start_time']}}">{{$slot['start_time']}}</a>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <h6 class="text-center m-3">{{$s['slot_available']}}</h6>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active show">
                <div class="border p-2">
                    <h6 class="text-center m-3">No Slots Available</h6>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>