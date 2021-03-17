<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Appointment;
use App\Chat;
use App\DiagnosticsService;
use App\Mail\BookAppointment;
use App\Mail\SendAppointmentToDoctor;
use App\Notification;
use App\Payment;
use App\PracticeManager;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Yajra\DataTables\Facades\DataTables;
use App\Wishlist;
use App\Jobs\BookAppointmentJob;
use App\Jobs\BookDiagnosticsAppointmentJob;
use App\Jobs\CancelAppointmentJob;
use App\Jobs\SendAppointmentToDiagnosticsJob;
use App\Jobs\SendAppointmentToDoctorJob;
use App\Mail\CancelAppointment;
use App\UserApp;
use App\SharePrescription;
use App\UserWallet;

class AppointmentController extends BaseController
{
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        $this->middleware('checkPermission', ['only' => ['index', 'myAppointment', 'cancelAppointment']]);
        $this->api = new Api(config('razorpay.razor_key'), config('razorpay.razor_secret'));
    }

    /* Function for view profile 
    * id = doctor
    */
    public function index(Request $request, $id, $slug, $parent_id = '', $parent_slug = '')
    {
        $this->_setPageTitle('Book Appointment');
        $data = [];

        /* User as doctor / clinic / hospital / pharmacy */
        $user = User::find($id);

        if (!$user) {
            return view('errors.404');
        }
        if ($parent_id && !User::where('id', $parent_id)->whereHas('role', function ($role) {
            $role->whereIn('keyword', ['clinic', 'hospital']);
        })->exists()) {
            return abort(404);
        }

        $data['title'] = ($user->role->keyword == "doctor") ? "In Clinic Appointment" : '';

        if ($parent_id) {
            $data['profile'] = $user->with(['practice' => function ($practice) use ($parent_id) {
                $practice->where('added_by', $parent_id);
            }])->where('id', $id)->first();
        } else {
            $data['profile'] = $user;
        }

        $data['user'] = Auth::user();

        return view('front.appointment.index')->with($data);
    }

    /* Function for online consultant */
    public function onlineConsult(Request $request, $id, $slug)
    {
        $this->_setPageTitle('Book An Appointment');
        $data = [];
        $doctor = User::find($id);
        if (!$doctor) {
            return view('errors.404');
        }
        $data['title'] = "Video Consultation";
        $data['doctor'] = $doctor;
        $data['user'] = Auth::user();

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(15);
        $period = CarbonPeriod::create($startDate, $endDate);
        $startTimeWithTimeZone = Carbon::now(Auth::user()->timezone)->addMinute(60)->format('H:i');

        $consultant_duration = isset($doctor->setting->consultant_duration) ? $doctor->setting->consultant_duration : 30;
        $booked_appointment = Appointment::where('doctor_id', $doctor->id)->whereNotIn('status', ['pending', 'cancelled'])->get(['id', 'date', 'start_time', 'end_time'])->map(function ($appointment) {
            $appointment->start_time = Carbon::parse($appointment->start_time)->format('g:i A');
            $appointment->end_time = Carbon::parse($appointment->end_time)->format('g:i A');
            return $appointment;
        })->toArray();

        // Iterate over the period
        $array_big = [];
        if (!empty($doctor->practice)) {
            foreach ($doctor->practice as $key => $practice) {
                if (!empty($practice->timing) && !empty($array_big)) {
                    $days = json_decode($practice->timing);
                    foreach ($days as $d_key => $d) {
                        if (!empty($d->periods)) {
                            foreach ($d->periods as $p) {
                                $p->practice_id = $practice->id;
                            }
                            $array_big[$d_key]->periods = array_merge($array_big[$d_key]->periods, $d->periods);
                        }
                    }
                } elseif (!empty($practice->timing)) {
                    $days = json_decode($practice->timing);
                    foreach ($days as $d_key => $d) {
                        if (!empty($d->periods)) {
                            foreach ($d->periods as $p) {
                                $p->practice_id = $practice->id;
                            }
                        }
                    }
                    $array_big = array_merge($array_big, $days);
                }
            }
        }

        foreach ($period as $p_key => $date) {

            $subArray = array_filter($booked_appointment, function ($ar) use ($date) {

                return ($ar['date'] == $date->format('Y-m-d'));
            });

            $dayname = $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : date("D, d M ", strtotime($date)));
            $day_number = weekDayNumber(date("l", strtotime($date)));
            $temp_slot = [];
            if (!empty($array_big[$day_number]->periods)) {
                foreach ($array_big[$day_number]->periods as $key => $p) {

                    $StartTime    = strtotime($p->start);
                    $EndTime      = strtotime($p->end);
                    $AddMins  = $consultant_duration * 60;
                    $slotEndTime = $StartTime;
                    while ($StartTime < $EndTime) //Run loop
                    {
                        $slotEndTime += $AddMins;
                        if ($date->isToday()) {
                            if ($StartTime >= strtotime($startTimeWithTimeZone)) {
                                $temp_slot[] = [
                                    'time' => [
                                        'start_time' => date("g:i A", $StartTime),
                                        'end_time' => date("g:i A", $slotEndTime),
                                    ],
                                    'practice_id' => $p->practice_id,

                                ];
                            }
                        } else {
                            $temp_slot[] = [
                                'time' => [
                                    'start_time' => date("g:i A", $StartTime),
                                    'end_time' => date("g:i A", $slotEndTime),
                                ],
                                'practice_id' => $p->practice_id,

                            ];
                        }
                        $StartTime += $AddMins; //Endtime check
                    }
                }
            }

            /* Sorting the time */
            usort($temp_slot, function ($a, $b) {
                return (strtotime($a['time']['start_time']) > strtotime($b['time']['start_time']));
            });

            $schedule[] = [
                'id' => uniqid(),
                'title' => $dayname,
                'date' => $date->format('Y-m-d'),
                'slot' => array_unique($temp_slot, SORT_REGULAR),
                'slot_available' => (count($temp_slot) > 0) ? count($temp_slot) . ' Slots Available' : 'No Slots Available',
                'booked_slot' => $subArray
            ];
        }

        $data['schedule'] = $schedule;
        return view('front.appointment.online_consult')->with($data);
    }

    public function orderCreate(Request $request)
    {
        $input = $request->except('_token');
        try {

            DB::beginTransaction();
            $input['start_time'] = $input['date'] . ' ' . date('H:i:s', strtotime($input['time']));
            $practice = PracticeManager::find($input['practice_id']);
            $input['start_time'] = $input['date'] . ' ' . date('H:i:s', strtotime($input['time']));
            $consultant_duration = isset($practice->doctor->setting->consultant_duration) ? $practice->doctor->setting->consultant_duration : 30;
            $input['end_time'] = Carbon::parse($input['start_time'])->addMinutes($consultant_duration);
            $orderPrameters = [
                'receipt' => '#' . Auth::id() . strtotime(Carbon::now()),
                'amount' => $practice->fees * 100,
                'currency' => 'INR',
                'payment_capture' => 1 // auto capture
            ];

            //check appointment already booked or not
            $appointment = Appointment::where('doctor_id', $practice->doctor_id)
                ->where('practice_id', $practice->id)
                ->where('start_time', $input['start_time'])
                ->where('date', $input['date'])->whereHas('payment', function ($payment) {
                    $payment->where('status', 'paid');
                })->first();

            if (!$appointment) {
                $razorpayOrder = $this->api->order->create($orderPrameters);

                if ($razorpayOrder) {
                    $paymentParameter = [
                        'user_id' => Auth::id(),
                        'receipt_id' => $orderPrameters['receipt'],
                        'order_id' => $razorpayOrder->id,
                        'customer_id' => isset($razorpayOrder->customer_id) ? $razorpayOrder->customer_id : '',
                        'txn_date' => Carbon::createFromTimestamp($razorpayOrder->created_at),
                        'amount' => $practice->fees,
                        'payable_amount' => $practice->fees,
                        'status' => $razorpayOrder->status
                    ];
                    $input['patient_id'] = Auth::id();
                    $payment = Payment::create($paymentParameter);
                    $input['payment'] = $payment;
                    $input['payment_id'] = $payment->id;
                    $input['appointment_from'] = 'Web';
                    Appointment::create($input);
                    //add doctor into wishlist
                    Wishlist::updateOrCreate(['user_id' => Auth::id(), 'doctor_id' => $input['doctor_id']], ['user_id' => Auth::id(), 'doctor_id' => $input['doctor_id']]);

                    DB::commit();
                    $result = ["status" => $this->success, "message" => "Order created.", "result" => $input];
                } else {
                    DB::rollback();
                    $this->status = 401;
                    $result = ["status" => $this->error, "message" => $this->exception_message];
                }
            } else {
                DB::rollback();
                $this->status = 401;
                $result = ["status" => $this->error, "message" => "This appointment time already booked. Please try another time!"];
            }
        } catch (Exception $e) {
            DB::rollback();
            $status = 401;
            $result = ["status" => $this->error, "message" => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function orderVerify(Request $request, Notification $notification)
    {
        try {
            DB::beginTransaction();
            $attributes = array(
                'razorpay_order_id' => $request->get('razorpay_order_id'),
                'razorpay_payment_id' => $request->get('razorpay_payment_id'),
                'razorpay_signature' => $request->get('razorpay_signature')
            );

            $this->api->utility->verifyPaymentSignature($attributes);
            $orderStatus = $this->api->order->fetch($request->get('razorpay_order_id'));
            if (!empty($orderStatus) && isset($orderStatus->id)) {

                $payment = Payment::where('order_id', $request->get('razorpay_order_id'))->first();
                $orderData = getOrderID();
                $payment->payment_id = $request->get('razorpay_payment_id');
                $payment->status = $orderStatus->status;
                $payment->order_no = $orderData['order_no'];
                $payment->invoice_id = $orderData['invoice_id'];
                $payment->save();
                if ($payment) {
                    $appointment = Appointment::where('payment_id', $payment->id)->first();
                    $appointment->update(['status' => 'create']);
                }

                if ($appointment) {

                    $record = [
                        'subject' => '(Appointment Booked) ' . $appointment->doctor->name . ' on ' . date('l jS F, \a\t h:i a', strtotime($appointment->start_time)) . ' EST',
                        'recipient_name' => $appointment->patient_name,
                        'recipient_email' => $appointment->patient_email,
                        'recipient_phone' => $appointment->patient_phone,
                        'text' => "Your Appointment booked successfully.",
                        'title' => "Your appointment has been scheduled!",
                        'invoice' => '',
                        'invoice_filename' => '',
                        'doctor_email' => $appointment->doctor->email,
                        'doctor_name' => $appointment->doctor->name,
                        'manager' => $appointment->doctor->manager()->pluck('email', 'name'),
                        'payment_id' => $payment->id,
                        'appointment_id' => $appointment->id,
                        'appointment_time' => $appointment->start_time,
                        'practice_name' => $appointment->practice->name,
                        'practice_address' => $appointment->practice->full_address,
                        'practice_phone' => $appointment->practice->phone,
                    ];
                    if ($appointment->patient_email) {
                        dispatch(new BookAppointmentJob($record)); //add mail to queue
                    }
                    if ($appointment->doctor->email) {
                        dispatch(new SendAppointmentToDoctorJob($record)); //add mail to queue
                    }

                    $ids = $appointment->doctor->manager()->pluck('id');
                    $ids[] = $appointment->doctor_id;
                    $input = [];
                    foreach ($ids as $id) {
                        $input[] = [
                            'sender_id' => Auth::id(),
                            'receiver_id' => $id,
                            'title' => "Appointment Booked ",
                            'type' => "book_appointment",
                            'message' => ucfirst(Auth::user()->name) . " has booked an appointment with you on " . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ".",
                        ];
                    }
                    Notification::insert($input);

                    $chat = Chat::where('sender_id', Auth::id())->where('recipient_id', $appointment->doctor_id)
                        ->orWhere('sender_id', $appointment->doctor_id)->where('recipient_id', Auth::id())
                        ->get();
                    if ($chat->isEmpty()) {
                        $chat = Chat::create(['sender_id' => Auth::id(), 'recipient_id' => $appointment->doctor_id]);
                    }

                    //send notification to doctor
                    $androidToken = UserApp::where('user_id', $appointment->doctor_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                    if (!empty($androidToken)) {
                        $subject = "Book Appointment";
                        $sms_push_text = ucfirst(Auth::user()->name) . " has booked an appointment with you on " . date('l jS F, \a\t h:i a', strtotime($appointment->start_time)) . ".";
                        $extra = ['id' => $appointment->patient_id, 'type' => 'book_appointment', 'user_type' => 'doctor', 'appointment_status' => 'create'];

                        $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                    }

                    //start wallet management

                    $wallet = [];
                    $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                        $q->where('keyword', 'admin');
                    })->first();
                    if ($is_admin->commission->neucrad_commission) {
                        $admin_fee = $appointment->payment->amount * ($is_admin->commission->neucrad_commission / 100);

                        if ($appointment->practice->added_by) {
                            $payable_commission = (100 - $is_admin->commission->neucrad_commission) / 100;
                            $payable_amount = $appointment->payment->amount * $payable_commission;
                            $wallet[] = [
                                'user_id' => $appointment->practice->added_by,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $payable_amount,
                            ];
                        }

                        if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1 && $appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
                            $wallet[] = [
                                'user_id' => $appointment->patient->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
                            ];
                            $wallet[] = [
                                'user_id' => $appointment->practice->addedBy->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
                            ];

                            $admin_commission = 100 - ($is_admin->commission->other_agent + $is_admin->commission->patient_agent);
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($admin_commission / 100),
                            ];
                        } else if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1) {
                            $wallet[] = [
                                'user_id' => $appointment->patient->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
                            ];
                            $admin_commission = 100 - $is_admin->commission->patient_agent;
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($admin_commission / 100),
                            ];
                        } else if ($appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
                            $wallet[] = [
                                'user_id' => $appointment->practice->addedBy->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
                            ];
                            $admin_commission = 100 - $is_admin->commission->other_agent;
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($admin_commission / 100),
                            ];
                        } else {
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee,
                            ];
                        }

                        UserWallet::insert($wallet);
                    }
                    //end wallet management

                    $url = url('thankyou');
                    DB::commit();
                    $result = ["status" => $this->success, "message" => 'Appointment booked successfully.', 'url' => $url];
                } else {
                    DB::rollBack();
                    $this->status = 401;
                    $result = ["status" => $this->error, "message" => $this->exception_message];
                }
            }
        } catch (SignatureVerificationError $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ["status" => $this->error, "message" => 'Your payment has been failed.', "result" => $e->getMessage()];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ["status" => $this->error, "message" => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    /* Doctor schedule practive vise
    *   Default duration : 30 minute
    */
    function loadPracticeTiming(Request $request)
    {
        try {
            $data = [];
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addDays(15);
            $period = CarbonPeriod::create($startDate, $endDate);

            $startTimeWithTimeZone = Carbon::now(Auth::user()->timezone)->addMinute(60)->format('H:i');
            $schedule = [];
            $practiceManager = PracticeManager::find($request->get('practice_id'));
            $timing = $practiceManager->timing ? json_decode($practiceManager->timing) : [];
            if (isset($practiceManager->doctor_id)) {
                $consultant_duration = isset($practiceManager->doctor->setting->consultant_duration) ? $practiceManager->doctor->setting->consultant_duration : 30;
                $availability = isset($practiceManager->doctor->setting->availability) ? $practiceManager->doctor->setting->availability : 1;
            } else {
                $consultant_duration = isset($practiceManager->addedBy->setting->consultant_duration) ? $practiceManager->addedBy->setting->consultant_duration : 30;
                $availability = isset($practiceManager->addedBy->setting->availability) ? $practiceManager->addedBy->setting->availability : 1;
            }

            $booked_appointment = Appointment::where('practice_id', $request->get('practice_id'))->whereNotIn('status', ['pending', 'cancelled'])->get(['id', 'date', 'start_time', 'end_time'])->map(function ($appointment) {
                $appointment->start_time = Carbon::parse($appointment->start_time)->format('g:i A');
                $appointment->end_time = Carbon::parse($appointment->end_time)->format('g:i A');
                return $appointment;
            })->toArray();

            foreach ($period as $p_key => $date) {

                $subArray = array_filter($booked_appointment, function ($ar) use ($date) {
                    return ($ar['date'] == $date->format('Y-m-d'));
                });

                $dayname = $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : date("D, d M ", strtotime($date)));
                $day_number = weekDayNumber(date("l", strtotime($date)));
                $temp_slot = [];
                if (!empty($timing[$day_number]->periods)) {
                    foreach ($timing[$day_number]->periods as $key => $p) {
                        // p($p, 0);
                        $StartTime    = strtotime($p->start);
                        $EndTime      = strtotime($p->end);
                        $AddMins  = $consultant_duration * 60;
                        $slotEndTime = $StartTime;
                        while ($StartTime < $EndTime) //Run loop
                        {
                            $slotEndTime += $AddMins;
                            if ($date->isToday()) {
                                if ($StartTime >= strtotime($startTimeWithTimeZone)) {
                                    $temp_slot[] = [
                                        'start_time' => date("g:i A", $StartTime),
                                        'end_time' => date("g:i A", $slotEndTime),
                                    ];
                                }
                            } else {
                                $temp_slot[] = [
                                    'start_time' => date("g:i A", $StartTime),
                                    'end_time' => date("g:i A", $slotEndTime),
                                ];
                            }
                            $StartTime += $AddMins; //Endtime check
                        }
                    }
                }

                $temp_slot = timeSort($temp_slot);

                if (isset($practiceManager->doctor_id) && $availability == 0 && ($date->format('Y-m-d') >= $practiceManager->doctor->setting->unavailability_start_date) && ($date->format('Y-m-d') <= $practiceManager->doctor->setting->unavailability_end_date)) {
                    $schedule[] = [
                        'id' => uniqid(),
                        'title' => $dayname,
                        'date' => $date->format('Y-m-d'),
                        'slot' => [],
                        'slot_available' => 'No Slots Available',
                        'booked_slot' => []
                    ];
                } else if ($availability == 0 && ($date->format('Y-m-d') >= $practiceManager->addedBy->setting->unavailability_start_date) && ($date->format('Y-m-d') <= $practiceManager->addedBy->setting->unavailability_end_date)) {
                    $schedule[] = [
                        'id' => uniqid(),
                        'title' => $dayname,
                        'date' => $date->format('Y-m-d'),
                        'slot' => [],
                        'slot_available' => 'No Slots Available',
                        'booked_slot' => []
                    ];
                } else {
                    $schedule[] = [
                        'id' => uniqid(),
                        'title' => $dayname,
                        'date' => $date->format('Y-m-d'),
                        'slot' => array_unique($temp_slot, SORT_REGULAR),
                        'slot_available' => (count($temp_slot) > 0) ? count($temp_slot) . ' Slots Available' : 'No Slots Available',
                        'booked_slot' => $subArray
                    ];
                }
            }
            $data['schedule'] = $schedule;
            $html = view('front.ajax._practiceSchedule', $data)->render();
            $result = ['status' => $this->success, 'message' => ">Load schedule", 'html' => $html];
        } catch (Exception $e) {
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }



    /* Account - My Appointment */
    function myAppointment(Request $request, Appointment $appointment)
    {
        try {

            $this->_setPageTitle('My Appointments');
            $data = [];

            if ($request->ajax()) {
                try {

                    $query = new Appointment;

                    $query = $query->where('patient_id', Auth::id())->whereHas('payment', function ($payment) {
                        $payment->whereIn('status', ['paid', 'refunded']);
                    })->orderBy('id', 'DESC');

                    if ($request->get('appointment_type') && $request->get('appointment_type') !== "all") {
                        if($request->get('appointment_type') == 'create' || $request->get('appointment_type') == 'completed' || $request->get('appointment_type') == 'cancelled')
                            $query = $query->where('status', $request->get('appointment_type'));
                        else
                            $query = $query->where('appointment_type', $request->get('appointment_type'));
                    }

                    $datatable = DataTables::of($query->get());

                    $datatable = $datatable->addColumn('doctor_name', function ($q) {
                        return isset($q->doctor->name) ? $q->doctor->name : $q->diagnostics->name;
                    });

                    $datatable = $datatable->addColumn('date', function ($q) {
                        return isset($q->date) ? date('d M, Y', strtotime($q->date)) : 'No mentioned';
                    });

                    $datatable = $datatable->addColumn('start_time', function ($q) {
                        return isset($q->start_time) ? date('d M, Y h:i a', strtotime($q->start_time)) : 'No mentioned';
                    });

                    $datatable = $datatable->addColumn('appointment_type', function ($q) {
                        if ($q->appointment_type == 'ONLINE') {
                            $btn = 'Video Consultant';
                        } else {
                            $btn = 'In Person';
                            if ($q->is_sample_pickup && $q->is_sample_pickup == 1) {
                                $btn .= ' <span class="badge badge-pill badge-info" data-toggle="tooltip" data-original-title="Sample Pickup From Home"><i class="fas fa-home"></i></span>';
                            };
                        }
                        return isset($q->appointment_type) ? $btn : 'No mentioned';
                    });

                    $datatable = $datatable->addColumn('address', function ($q) {
                        return isset($q->practice->address) ? '<span class="ws-break-spaces">' . $q->practice->address . '</span>' : '';
                    });

                    $datatable = $datatable->addColumn('status', function ($q) {
                        return $q->getStatus($q->status);
                    });

                    $datatable = $datatable->addColumn('action', function ($q) {

                        $button = '';

                        if ($q->status == 'pending' || $q->status == 'create') {
                            $button .= '<a href="javascript:;" class="f-16 m-l-5 m-r-5" onclick="deleteAppointment(' . $q->id . ')" data-toggle="tooltip" data-original-title="Cancel Appointment"><i class="far fa-calendar-times"></i></a>';
                        }
                        if ($q->status == 'attempt' || $q->status == 'completed') {
                            $button .= '<a href="javascript:;" class="f-16 m-l-5 m-r-5" onclick="viewAppointment(' . $q->id . ')" data-toggle="tooltip" data-original-title="view Appointment"><i class="far fa-eye"></i></a>';
                        }
                        return $button;
                    });

                    $datatable = $datatable->rawColumns(['name', 'email', 'phone', 'locality', 'address', 'status', 'action', 'appointment_type']);

                    $datatable = $datatable->make(true);
                } catch (Exception $e) {
                    $datatable = Datatables::of(PracticeManager::select()->take(0)->get());
                    $datatable = $datatable->make(true);
                }
                return $datatable;
            }

            return view('account.appointments.index')->with($data);
        } catch (Exception $e) {
        }
    }

    function cancelAppointment($id, Notification $notification)
    {
        try {
            DB::beginTransaction();
            $appointment = Appointment::find($id);
            if ($appointment->status != 'cancelled') {
                $startTimeWithTimeZone = Carbon::now(Auth::user()->timezone)->addMinute(60);
                if ($startTimeWithTimeZone < $appointment->start_time) {
                    if ($appointment->payment->payment_id) {
                        $refundParameters = [
                            'amount' => $appointment->payment->amount * 100,
                            'speed' => 'optimum'
                        ];
                        $payment = $this->api->payment->fetch($appointment->payment->payment_id);
                        $refund = $payment->refund($refundParameters);
                        if ($refund) {
                            $appointment->update([
                                'status' => 'cancelled',
                                'cancelled_by' => Auth::id()
                            ]);
                            Payment::where('id', $appointment->payment_id)->update([
                                'status' => 'refunded',
                                'refunded_date' => Carbon::createFromTimestamp($refund->created_at)->format('Y-m-d'),
                            ]);
                        }
                        if (isset($appointment->doctor_id)) {
                            $receiver_id = ($appointment->doctor_id == Auth::id()) ? $appointment->patient_id : $appointment->doctor_id;
                            $notify_message = ($appointment->doctor_id == Auth::id()) ? 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled. Payment will be credited to your account within 5 to 7 working days' : 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled.';
                            $type = ($appointment->doctor_id == Auth::id()) ? 'cancelled_appointment_by_doctor' : 'cancelled_appointment_by_patient';
                        } else {
                            $receiver_id = ($appointment->diagnostics_id == Auth::id()) ? $appointment->patient_id : $appointment->diagnostics_id;
                            $notify_message = ($appointment->diagnostics_id == Auth::id()) ? 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled. Payment will be credited to your account within 5 to 7 working days' : 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled.';
                            $type = ($appointment->diagnostics_id == Auth::id()) ? 'cancelled_appointment_by_diagnostics' : 'cancelled_appointment_by_patient';
                        }
                        $data = [
                            'sender_id' => Auth::id(),
                            'receiver_id' => $receiver_id,
                            'title' => 'Cancelled Appointment',
                            'type' => $type,
                            'message' => $notify_message,
                        ];
                        Notification::create($data);
                        if (isset($appointment->doctor_id)) {
                            $receiver = ($appointment->doctor_id == Auth::id()) ? $appointment->patient : $appointment->doctor;
                        } else {
                            $receiver = ($appointment->diagnostics_id == Auth::id()) ? $appointment->patient : $appointment->diagnostics;
                        }

                        if ($receiver->email) {
                            $mailInfo = ([
                                'recipient_email' => $receiver->email,
                                'recipient_name' => $receiver->name,
                                'title' => 'Your appointment has been cancelled.',
                                'subject' => '(Appointment Cancellation) ' . Auth::user()->name . ' on ' . date('d M, Y h:i a', strtotime($appointment->start_time)) . ' EST',
                                'content' => 'Your appointment with ' . Auth::user()->name . ' scheduled for ' . date('d M, Y h:i a', strtotime($appointment->start_time)) . ' EST has been cancelled.',
                                'reason' => '',
                                'sender_name' => Auth::user()->name,
                            ]);
                            dispatch(new CancelAppointmentJob($mailInfo)); //add mail to queue
                        }

                        /* start notification*/
                        //send notification to app 
                        if (isset($appointment->doctor_id)) {

                            $androidToken = UserApp::where('user_id', $receiver_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();
                            $userType = ($appointment->doctor_id == Auth::id()) ? 'patient' : 'doctor';

                            if (!empty($androidToken)) {
                                $subject = 'Cancelled Appointment';
                                $extra = ['id' => $appointment->patient_id, 'type' => $type, 'user_type' => $userType, 'appointment_status' => 'cancelled'];
                                $sms_push_text = $notify_message;
                                $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                            }
                            /* end notification */
                        }

                        if (isset($appointment->diagnostics_id)) {
                            $androidToken = UserApp::where('user_id', $receiver_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();
                            $userType = 'patient';

                            if (!empty($androidToken)) {
                                $subject = 'Cancelled Appointment';
                                $extra = ['id' => $appointment->patient_id, 'type' => $type, 'user_type' => $userType, 'appointment_status' => 'cancelled'];
                                $sms_push_text = $notify_message;
                                $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                            }
                            /* end notification */
                        }

                        UserWallet::where('appointment_id', $appointment->id)->update(['status' => 'refunded']);
                        UserWallet::where('appointment_id', $appointment->id)->delete();

                        DB::commit();
                        $result = [
                            'status' => $this->success,
                            'message' => 'Your appointment has been cancelled.',
                        ];
                    } else {
                        DB::rollBack();
                        $result = ['status' => $this->error, "message" => "Your Payment not available."];
                    }
                } else {
                    DB::rollBack();
                    $result = ['status' => $this->error, "message" => "You can not cancel your appointment."];
                }
            } else {
                DB::rollBack();
                $result = ['status' => $this->error, "message" => "Your Payment was already refunded."];
            }
        } catch (Exception $e) {
            DB::rollBack();
            $result = ['status' => $this->error, "message" => $this->exception_message];
        }
        return Response::json($result);
    }

    //view all details of appointment
    public function viewAppointmentDetail($id)
    {

        try {
            $appointment = Appointment::find($id);
            $share_pharmacy = SharePrescription::where('appointment_id', $id)->first();
            $data = ['title' => 'Appointment Detail', 'appointment' => $appointment, 'share_pharmacy' => $share_pharmacy];
            if (isset($appointment->doctor_id)) {
                $html = view('account.appointments.show', $data)->render();
            }
            if (isset($appointment->diagnostics_id)) {
                $services_ids = stringToArray($appointment->services_ids);
                $data['services'] = DiagnosticsService::whereIn('id', $services_ids)->get();
                $html = view('account.appointments.diagnostics_appointment_show', $data)->render();
            }
            $result = ['status' => $this->success, 'message' => "Appointment detail.", 'html' => $html];
        } catch (Exception $e) {
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function diagnosticsAppointmentBook(Request $request, $id, $slug)
    {
        $this->_setPageTitle('Book Diagnostics Appointment');
        $data = [];
        $user = User::find($id);

        if (!$user) {
            return view('errors.404');
        }

        $data['title'] = "In Diagnostics Appointment";
        $data['profile'] = $user;
        $data['practice'] = $user->practiceAsStaff->first();

        $data['user'] = Auth::user();

        return view('front.appointment.diagnostics_appointment_book')->with($data);
    }

    public function diagnosticsOrderCreate(Request $request)
    {
        $input = $request->except('_token');
        try {
            DB::beginTransaction();
            $input['start_time'] = $input['date'] . ' ' . date('H:i:s', strtotime($input['time']));
            $practice = PracticeManager::find($input['practice_id']);
            $consultant_duration = isset($practice->addedBy->setting->consultant_duration) ? $practice->addedBy->setting->consultant_duration : 30;
            $input['end_time'] = Carbon::parse($input['start_time'])->addMinutes($consultant_duration);
            if ($request->get('services_ids')) {
                $services_ids = arrayToString($request->get('services_ids'));
            }
            $input['services_ids'] = isset($services_ids) ? $services_ids : '';
            $services_fee = DiagnosticsService::whereIn('id', $request->get('services_ids'))->sum('price');
            if ($request->get('sample_pickup') && $request->get('sample_pickup') == 1) {
                $input['is_sample_pickup'] = $request->get('sample_pickup');
                $sample_pickup_charge = isset($practice->addedBy->setting->sample_pickup_charge) ? $practice->addedBy->setting->sample_pickup_charge : 0;
                $services_fee = $services_fee + $sample_pickup_charge;
            }
            $orderPrameters = [
                'receipt' => '#' . Auth::id() . strtotime(Carbon::now()),
                'amount' => $services_fee * 100,
                'currency' => 'INR',
                'payment_capture' => 1 // auto capture
            ];


            //check appointment already booked or not
            $appointment = Appointment::where('diagnostics_id', $input['diagnostics_id'])
                ->where('practice_id', $practice->id)
                ->where('start_time', $input['start_time'])
                ->where('date', $input['date'])->whereHas('payment', function ($payment) {
                    $payment->where('status', 'paid');
                })->first();

            if (!$appointment) {
                $razorpayOrder = $this->api->order->create($orderPrameters);

                if ($razorpayOrder) {
                    $paymentParameter = [
                        'user_id' => Auth::id(),
                        'receipt_id' => $orderPrameters['receipt'],
                        'order_id' => $razorpayOrder->id,
                        'customer_id' => isset($razorpayOrder->customer_id) ? $razorpayOrder->customer_id : '',
                        'txn_date' => Carbon::createFromTimestamp($razorpayOrder->created_at),
                        'amount' => $services_fee,
                        'payable_amount' => $services_fee,
                        'status' => $razorpayOrder->status
                    ];
                    $input['patient_id'] = Auth::id();
                    $payment = Payment::create($paymentParameter);
                    $input['payment'] = $payment;
                    $input['payment_id'] = $payment->id;
                    $input['appointment_from'] = 'Web';

                    Appointment::create($input);

                    DB::commit();
                    $result = ["status" => $this->success, "message" => "Order created.", "result" => $input];
                } else {
                    DB::rollback();
                    $this->status = 401;
                    $result = ["status" => $this->error, "message" => $this->exception_message];
                }
            } else {
                DB::rollback();
                $this->status = 401;
                $result = ["status" => $this->error, "message" => "This appointment time already booked. Please try another time!"];
            }
        } catch (Exception $e) {
            DB::rollback();
            $status = 401;
            $result = ["status" => $this->error, "message" => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function diagnosticsOrderVerify(Request $request, Notification $notification)
    {
        try {
            DB::beginTransaction();
            $attributes = array(
                'razorpay_order_id' => $request->get('razorpay_order_id'),
                'razorpay_payment_id' => $request->get('razorpay_payment_id'),
                'razorpay_signature' => $request->get('razorpay_signature')
            );

            $this->api->utility->verifyPaymentSignature($attributes);
            $orderStatus = $this->api->order->fetch($request->get('razorpay_order_id'));
            if (!empty($orderStatus) && isset($orderStatus->id)) {

                $payment = Payment::where('order_id', $request->get('razorpay_order_id'))->first();
                $orderData = getOrderID();
                $payment->payment_id = $request->get('razorpay_payment_id');
                $payment->status = $orderStatus->status;
                $payment->order_no = $orderData['order_no'];
                $payment->invoice_id = $orderData['invoice_id'];
                $payment->save();
                if ($payment) {
                    $appointment = Appointment::where('payment_id', $payment->id)->first();
                    $appointment->update(['status' => 'create']);
                }

                if ($appointment) {

                    $record = [
                        'subject' => '(Appointment Booked) ' . $appointment->diagnostics->name . ' on ' . date('l jS F, \a\t h:i a', strtotime($appointment->start_time)) . ' EST',
                        'recipient_name' => $appointment->patient_name,
                        'recipient_email' => $appointment->patient_email,
                        'recipient_phone' => $appointment->patient_phone,
                        'text' => "Your Appointment booked successfully.",
                        'title' => "Your appointment has been scheduled!",
                        'invoice' => '',
                        'invoice_filename' => '',
                        'doctor_email' => $appointment->diagnostics->email,
                        'doctor_name' => $appointment->diagnostics->name,
                        'manager' => $appointment->diagnostics->manager()->pluck('email', 'name'),
                        'payment_id' => $payment->id,
                        'appointment_id' => $appointment->id,
                        'appointment_time' => $appointment->start_time,
                        'practice_name' => $appointment->practice->name,
                        'practice_address' => $appointment->practice->full_address,
                        'practice_phone' => $appointment->practice->phone,
                    ];
                    if ($appointment->patient_email) {
                        dispatch(new BookAppointmentJob($record)); //add mail to queue
                    }
                    if ($appointment->diagnostics->email) {
                        dispatch(new SendAppointmentToDoctorJob($record)); //add mail to queue
                    }

                    $ids = $appointment->diagnostics->manager()->pluck('id');
                    $ids[] = $appointment->diagnostics_id;
                    $input = [];
                    foreach ($ids as $id) {
                        $input[] = [
                            'sender_id' => Auth::id(),
                            'receiver_id' => $id,
                            'title' => "Appointment Booked ",
                            'type' => "book_appointment",
                            'message' => ucfirst(Auth::user()->name) . " has booked an appointment with you on " . date('l j M, Y \a\t h:i a', strtotime($appointment->start_time)) . ".",
                        ];
                    }
                    Notification::insert($input);

                    //start wallet management
                    $wallet = [];
                    $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                        $q->where('keyword', 'admin');
                    })->first();
                    if ($is_admin->commission->neucrad_commission) {
                        $admin_fee = $appointment->payment->amount * ($is_admin->commission->neucrad_commission / 100);

                        if ($appointment->practice->added_by) {
                            $payable_commission = (100 - $is_admin->commission->neucrad_commission) / 100;
                            $payable_amount = $appointment->payment->amount * $payable_commission;
                            $wallet[] = [
                                'user_id' => $appointment->practice->added_by,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $payable_amount,
                            ];
                        }

                        if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1 && $appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
                            $wallet[] = [
                                'user_id' => $appointment->patient->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
                            ];
                            $wallet[] = [
                                'user_id' => $appointment->practice->addedBy->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
                            ];

                            $admin_commission = 100 - ($is_admin->commission->other_agent + $is_admin->commission->patient_agent);
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($admin_commission / 100),
                            ];
                        } else if ($appointment->patient->referrer_id && $appointment->patient->referrer_id != 1) {
                            $wallet[] = [
                                'user_id' => $appointment->patient->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->patient_agent / 100),
                            ];
                            $admin_commission = 100 - $is_admin->commission->patient_agent;
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($admin_commission / 100),
                            ];
                        } else if ($appointment->practice->addedBy->referrer_id && $appointment->practice->addedBy->referrer_id != 1) {
                            $wallet[] = [
                                'user_id' => $appointment->practice->addedBy->referrer_id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($is_admin->commission->other_agent / 100),
                            ];
                            $admin_commission = 100 - $is_admin->commission->other_agent;
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee * ($admin_commission / 100),
                            ];
                        } else {
                            $wallet[] = [
                                'user_id' => $is_admin->id,
                                'appointment_id' => $appointment->id,
                                'payment_id' => $appointment->payment_id,
                                'price' => $admin_fee,
                            ];
                        }

                        UserWallet::insert($wallet);
                    }
                    //end wallet management

                    $url = url('thankyou');
                    DB::commit();
                    $result = ["status" => $this->success, "message" => 'Appointment booked successfully.', 'url' => $url];
                } else {
                    DB::rollBack();
                    $this->status = 401;
                    $result = ["status" => $this->error, "message" => $this->exception_message];
                }
            }
        } catch (SignatureVerificationError $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ["status" => $this->error, "message" => 'Your payment has been failed.', "result" => $e->getMessage()];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ["status" => $this->error, "message" => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }
}
