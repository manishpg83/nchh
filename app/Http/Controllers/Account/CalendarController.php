<?php

namespace App\Http\Controllers\Account;

use App\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use DataTables;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use DB;
use \Carbon\Carbon;

class CalendarController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission')->except(['eventDetail']);
        $this->random = Str::random(12);
    }

    public function index(Request $request)
    {

        $this->_setPageTitle('Calendar');
        $data = [
            'title' => 'Calendar',
            'user' => Auth::user(),
        ];
        if ($request->start) {
            $res = [];
            if(checkPermission(['doctor'])){
                $appointments = Appointment::where('doctor_id', Auth::id())->whereNotIn('status', ['pending', 'cancelled']) ->whereBetween('date', [$request->start, $request->end])->get();
            }
            if(checkPermission(['diagnostics'])){
                $appointments = Appointment::where('diagnostics_id', Auth::id())->whereNotIn('status', ['pending', 'cancelled']) ->whereBetween('date', [$request->start, $request->end])->get();
            }
             if ($appointments) {
                foreach ($appointments as $a) {
                    if ($a->status == 'create') {
                        $backgroundColor = '#0328fc';
                        $borderColor = '#0328fc';
                        $textColor = '#fff';
                    } elseif ($a->status == 'attempt') {
                        $backgroundColor = '#fcd303';
                        $borderColor = '#fcd303';
                        $textColor = '#000';
                    } elseif ($a->status == 'completed') {
                        $backgroundColor = '#1cfc03';
                        $borderColor = '#1cfc03';
                        $textColor = '#000';
                    } else {
                        $backgroundColor = '#000';
                        $borderColor = '#000';
                        $textColor = '#fff';
                    }

                    $res[] = [
                        'appointment_id' => $a->id,
                        'title' => $a->patient->name,
                        'start' => $a->start_time,
                        'end' => $a->end_time,
                        'backgroundColor' => $backgroundColor,
                        'borderColor' => $borderColor,
                        'textColor' => $textColor,
                        'description' => $a->patient->name . ' has booked an appointment with you on ' . date("l jS F,  h:i a", strtotime($a->start_time)) . ' to ' . date("h:i a", strtotime($a->end_time)) . '.Practice Address is ' . $a->practice->address . ', ' . $a->practice->locality . '. Patient Phone number +91' . $a->patient->phone . '.',
                    ];
                }
            }
            return Response::json($res);
        }

        return view('account.calendar.index')->with($data);
    }

    public function eventDetail(Request $request)
    {
        $data = ['title' => 'Appointment Detail'];
        $appointment = Appointment::find($request->id);
        $startTime = Carbon::parse($appointment->start_time);
        $endTime = Carbon::parse($appointment->end_time);
        $totalDuration = $startTime->diff($endTime)->format('%I');
        $data['appointment'] = $appointment;
        $data['totalDuration'] = $totalDuration;
        $html = view('account.calendar.event-detail', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load appointment data.', 'html' => $html];
        return Response::json($result);
    }
}
