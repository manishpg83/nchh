@extends('account.layouts.master')

@section('content')
<section class="section user-profile-container">
  <div class="section-header">
    <h1>{{$pageTitle}}</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item active"><a href="{{ url('/') }}">Home</a></div>
      <div class="breadcrumb-item">{{$pageTitle}}</div>
    </div>
  </div>
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Appointment Calendar</h4>
          </div>
          <div class="card-body">
            <div id='loading'>loading...</div>
            <div id='myAppointmentCalendar'></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</section>
<!-- Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" mode="center" data-backdrop="static" data-keyboard="false"></div>
@endsection
@section('scripts')
<script src="{{ asset('account/js/page/calendar.js')}}"></script>
<script type="text/javascript">
  var calendarModal = $('#calendarModal');
  var getCalendarData = "{{Route('account.calendar')}}";
  var getEventDetail = "{{Route('account.event.detail')}}";
  var appointmentCancelUrl = "{{Route('appointment.cancel',':slug')}}";
</script>
@endsection