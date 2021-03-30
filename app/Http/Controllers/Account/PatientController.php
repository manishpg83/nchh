<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\User;
use App\Notification;
use Exception;
use Image;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Appointment;
use App\AppointmentFile;
use App\AppointmentPrescription;
use App\DiagnosticsService;
use App\Drug;
use App\UserApp;
use App\SharePrescription;

class PatientController extends BaseController
{
    protected $random;
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission')->except(['appointmentFile', 'appointmentFileStore', 'appointmentFileDelete', 'prescriptionAppend', 'prescriptionEdit', 'prescriptionStore', 'sendPrescription']);
        $this->random = Str::random(12);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Patients');
        $data = [
            'title' => 'Patients',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $patient_ids = Appointment::whereIn('status', ['attempt', 'completed']);
            if (checkPermission(['clinic', 'hospital'])) {
                $practice_ids = Auth::user()->practiceAsStaff->pluck('id')->toArray();
                $patient_ids = $patient_ids->whereIn('practice_id', $practice_ids);
            }

            if (checkPermission(['doctor'])) {
                $patient_ids = $patient_ids->where('doctor_id', Auth::id());
            }

            if (checkPermission(['diagnostics'])) {
                $patient_ids->where('diagnostics_id', Auth::id());
            }

            if (checkPermission(['manager'])) {
                if (Auth::user()->addedBy->role->keyword == 'doctor') {
                    $patient_ids = $patient_ids->where('doctor_id', Auth::user()->addedBy->id);
                } else {
                    $practice_ids = Auth::user()->addedBy->practiceAsStaff->pluck('id')->toArray();
                    $patient_ids = $patient_ids->whereIn('practice_id', $practice_ids);
                }
            }

            if (checkPermission(['clinic', 'hospital', 'manager', 'doctor', 'diagnostics'])) {
                $patient_ids = $patient_ids->pluck('patient_id')->toArray();
            } else {
                $patient_ids = ['0'];
            }

            $patients = User::whereIn('id', $patient_ids)->orderBy('name', 'DESC')->get();
            return Datatables::of($patients)
                ->addColumn('patient_name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle float-left mr-2" style="width: 35px;min-height: 35px;"/> <span><h6 class="mb-0">' . $data->name . '</h6><p class="mb-0 l-0 f-12">';
                        $btn .= isset($data->locality) ? $data->locality . ', ' : '';
                        $btn .= isset($data->city) ? $data->city : '';
                        $btn .= '</p></span>';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('patient_contact', function ($data) {
                    $btn = isset($data->phone) ? '<i class="fas fa-phone"></i> +' . $data->dialcode . $data->phone : '';
                    $btn .= isset($data->email) ? '<br><i class="far fa-envelope"></i> ' . $data->email : '';
                    return $btn;
                })->addColumn('detail', function ($data) {
                    $btn = '<p class="mb-0 l-0">';
                    $btn .= isset($data->gender) ? $data->gender : '';
                    $btn .= isset($data->dob) ? ' - ' . Carbon::parse($data->dob)->age . ' Years' : '';
                    $btn .= isset($data->blood_group) ? ' - ' . $data->blood_group : '';
                    $btn .= '</p>';
                    return $btn;
                })->addColumn('action', function ($data) {
                    if (checkPermission(['diagnostics'])) {
                        $btn = '<a href="' . route('account.patients.diagnostics.appointment', [$data->id, $data->name_slug]) . '" class=" " id="' . $data->id . '"><i class="fas fa-calendar-check" data-toggle="tooltip" title="View Patient`s Appointment"></i></a>';
                    } else {
                        $btn = '<a href="' . route('account.patients.appointment', [$data->id, $data->name_slug]) . '" class=" " id="' . $data->id . '"><i class="fas fa-calendar-check" data-toggle="tooltip" title="View Patient`s Appointment"></i></a>';
                        $btn .= '<a href="' . route('account.show-shared-medical-record', [$data->id]) . '" class="ml-3" id="' . $data->id . '"><i class="fas fa-file" data-toggle="tooltip" title="View Shared Medical Records"></i></a>';
                    }
                    return $btn;
                })->rawColumns(['patient_name', 'patient_contact', 'detail', 'action'])
                ->make(true);
        }
        return view('account.patients.index')->with($data);
    }

    public function appointment(Request $request, $id, $name)
    {
        $this->_setPageTitle('Appointment');
        $data = [
            'title' => 'Appointment',
            'user' => Auth::user(),
            'id' => $id,
            'name_slug' => $name,
            'name' => ucwords(str_replace('-', ' ', $name))
        ];

        if ($request->ajax()) {
            $appointment = Appointment::whereIn('status', ['attempt', 'create', 'pending', 'cancelled', 'completed']);
            if (checkPermission(['clinic', 'hospital'])) {
                $practice_ids = Auth::user()->practiceAsStaff->pluck('id')->toArray();
                $appointment = $appointment->whereIn('practice_id', $practice_ids);
            }

            if (checkPermission(['doctor'])) {
                $practice_ids = Auth::user()->practiceAsStaff->pluck('id')->toArray();
                $appointment = $appointment->where('doctor_id', Auth::id());
            }

            if (checkPermission(['manager'])) {
                if (Auth::user()->addedBy->role->keyword == 'doctor') {
                    $practice_ids = Auth::user()->addedBy->practiceAsStaff->pluck('id')->toArray();
                    $appointment = $appointment->where('doctor_id', Auth::user()->addedBy->id);
                } else {
                    $practice_ids = Auth::user()->addedBy->practiceAsStaff->pluck('id')->toArray();
                    $appointment = $appointment->whereIn('practice_id', $practice_ids);
                }
            }

            if ($request->get('appointment_type') && $request->get('appointment_type') !== "all") {
                if($request->get('appointment_type') == 'create' || $request->get('appointment_type') == 'completed' || $request->get('appointment_type') == 'cancelled')
                    $appointment = $appointment->where('status', $request->get('appointment_type'));
                else
                    $appointment = $appointment->where('appointment_type', $request->get('appointment_type'));
            }

            $appointment = $appointment->where('patient_id', $id)->orderBy('id', 'DESC')->get();
            return Datatables::of($appointment)
                ->addColumn('patient_name', function ($data) {
                    return isset($data->patient_name) ? $data->patient_name : '';
                })->addColumn('patient_contact', function ($data) {
                    $btn = isset($data->patient_phone) ? '<i class="fas fa-phone"></i> ' . $data->patient_phone : '';
                    $btn .= isset($data->patient_email) ? '<br><i class="far fa-envelope"></i> ' . $data->patient_email : '';
                    return $btn;
                })->addColumn('status', function ($data) {
                    return getAppointmentStatus($data->status);
                })->addColumn('appointment_date', function ($data) {
                    return date('d M, Y h:i a', strtotime($data->start_time)) . '<br><h6><span class="badge badge-pill badge-light">' . $data->appointment_type . '</span></h6>';
                })->addColumn('appointment_with', function ($data) {
                    if (checkPermission(['clinic', 'hospital'])) {
                        if ($data->doctor->name != null) {
                            $btn = '<img src=" ' . $data->doctor->profile_picture . '" class="rounded rounded-circle float-left mr-2" style="width: 40px;min-height: 40px;"/> <span><h6 class="mb-0">' . $data->doctor->name . '</h6><p class="mb-0 l-0 f-12">' . $data->doctor->dialcode . $data->doctor->phone . '</p></span>';
                        } else {
                            $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                        }
                    }
                    if (checkPermission(['doctor', 'manager'])) {
                        if ($data->practice->name != null) {
                            if ($data->practice->doctor_id == $data->practice->added_by) {
                                $logo = $data->practice->logo;
                            } else {
                                $logo = $data->practice->addedBy->profile_picture;
                            }
                            $btn = '<img src=" ' . $logo . '" class="rounded rounded-circle float-left mr-2" style="width: 40px;min-height: 40px;"/> <span><h6 class="mb-0">' . $data->practice->name . '</h6><p class="mb-0 l-0 f-12">' . $data->practice->phone . '</p><p class="mb-0 l-0 f-12">' . ucwords($data->practice->locality) . ', ' . ucwords($data->practice->city) . '</p></span>';
                        } else {
                            $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                        }
                    }
                    return $btn;
                })->addColumn('action', function ($data) {
                    // if($data->status == 'completed'){
                        $btn = '<a href="' . route('account.patients.appointment.detail', [$data->patient->id, $data->patient->name_slug, $data->id]) . '" class="mr-3" id="' . $data->id . '" data-toggle="tooltip" data-placement="top" title="View Patient`s Appointment"><i class="fas fa-calendar-check"></i></a>';
                    // }else{
                    //     $btn = '-';
                    // }
                    return $btn;
                })->rawColumns(['patient_name', 'appointment_date', 'patient_contact', 'appointment_with', 'status', 'action'])
                ->make(true);
        }

        return view('account.patients.appointment')->with($data);
    }

    public function diagnosticsAppointment(Request $request, $id, $name)
    {
        $this->_setPageTitle('Appointment');
        $data = [
            'title' => 'Appointment',
            'user' => Auth::user(),
            'id' => $id,
            'name_slug' => $name,
            'name' => ucwords(str_replace('-', ' ', $name))
        ];

        if ($request->ajax()) {
            $appointment = Appointment::whereIn('status', ['attempt', 'completed'])->where('diagnostics_id', Auth::id())->where('patient_id', $id)->orderBy('id', 'DESC')->get();

            return Datatables::of($appointment)
                ->addColumn('patient_name', function ($data) {
                    return isset($data->patient_name) ? $data->patient_name : '';
                })->addColumn('patient_contact', function ($data) {
                    $btn = isset($data->patient_phone) ? '<i class="fas fa-phone"></i> ' . $data->patient_phone : '';
                    $btn .= isset($data->patient_email) ? '<br><i class="far fa-envelope"></i> ' . $data->patient_email : '';
                    return $btn;
                })->addColumn('date', function ($data) {
                    return date('d M, Y', strtotime($data->date));
                })->addColumn('services', function ($data) { 
                    $btn = isset($data->services_name) ? '<p class="p-0 m-0 d-inline-flax ws-break-spaces">' . $data->services_name . ' ' : '<p> -';
                    if($data->is_sample_pickup && $data->is_sample_pickup == 1) {
                        $btn .= ' <span class="badge badge-pill badge-info" data-toggle="tooltip" data-original-title="Sample Pickup From Home"><i class="fas fa-home"></i></span>';
                     };
                     $btn .= '</p>';
                    return $btn;
                })->addColumn('price', function ($data) {
                    return $data->services_price;
                })->addColumn('action', function ($data) {
                    $btn = '<a href="' . route('account.patients.appointment.detail', [$data->patient->id, $data->patient->name_slug, $data->id]) . '" class="mr-3" id="' . $data->id . '" data-toggle="tooltip" data-placement="top" title="View Patient`s Appointment"><i class="fas fa-calendar-check"></i></a>';
                    return $btn;
                })->rawColumns(['patient_name', 'date', 'patient_contact', 'services', 'price', 'action'])
                ->make(true);
        }

        return view('account.patients.diagnostics_appointment')->with($data);
    }

    public function appointmentDetail(Request $request, $id, $name, $appointment_id)
    {
        $appointment = Appointment::find($appointment_id);
        $this->_setPageTitle('Appointment Detail');
 
        if(isset($appointment->diagnostics_id)){
        $services_ids = stringToArray($appointment->services_ids);
        $services = DiagnosticsService::whereIn('id', $services_ids)->get();
         }
        $data = [
            'title' => 'Appointment Detail',
            'user' => Auth::user(),
            'id' => $id,
            'name_slug' => $name,
            'appointment_id' => $appointment_id,
            'name' => ucwords(str_replace('-', ' ', $name)),
            'appointment' => $appointment,
            'drugs' => Drug::all(),
            'pharmacy' => User::whereHas('role', function ($p) {
                $p->where('keyword', 'pharmacy');
            })->get(),
            'share_pharmacy' => SharePrescription::where('appointment_id', $appointment_id)->first(),
            'services' => isset($services)? $services : null ,
        ];
        return view('account.patients.appointment_detail')->with($data);
    }

    public function appointmentFile(Request $request, $id)
    {
        $data = ['title' => 'Add File', 'appointment_id' => $id];
        $html = view('account.patients.file_add', $data)->render();
        $files = AppointmentFile::where('appointment_id', $id)->get();
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $file->file_name = $file->filename_value;
                $file->file_size = File::size(storage_path('app/appointment_prescription_file/' . $file->filename_value));
            }
        }
        $result = ['status' => $this->success, 'message' => 'load file page.', 'html' => $html, 'files' => $files];

        return Response::json($result);
    }

    public function appointmentFileStore(Request $request, $id, Notification $notification)
    {

        try {
            DB::beginTransaction();
            $input = $request->all();
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $key => $file) {
                    $filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
                    Image::make($file)->save(storage_path('app/appointment_prescription_file/' . $filename));
                    $images[] = [
                        'appointment_id' => $id,
                        'filename' => $filename
                    ];
                }
                AppointmentFile::insert($images);
            }
            $appointment = Appointment::find($id);
            $html = view('account.patients.appointment_file')->with(['appointment' => $appointment])->render();

            Notification::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $appointment->patient_id,
                'title' => Auth::user()->name . " has been uploaded the files. please check it",
                'type' => 'upload_file',
                'message' => Auth::user()->name . " has been uploaded the files. please check it."
            ]);

            $androidToken = UserApp::where('user_id', $appointment->patient_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

            if (!empty($androidToken)) {
                $subject = "New File Upload";
                $sms_push_text = Auth::user()->name . " has been uploaded the files. please check it";
                $extra = ['id' => $appointment->id, 'type' => 'upload_file', 'appointment_status' => 'completed'];

                $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
            }

            DB::commit();
            $result = [
                'status' => $this->success,
                'message' => "file uploaded successfully",
                'html' => $html
            ];
        } catch (Exception $e) {
            $this->status = 403;
            DB::rollBack();
            $result = ['status' => $this->error, "message" => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function appointmentFileDelete($id)
    {
        try {
            DB::beginTransaction();
            $files = AppointmentFile::find($id);
            if ($files) {
                $file_path = storage_path('app/appointment_prescription_file/' . $files->filename_value);
                @unlink($file_path);

                $files->delete();
            }
            $appointment = Appointment::find($files->appointment_id);
            $html = view('account.patients.appointment_file')->with(['appointment' => $appointment])->render();
            DB::commit();
            $result = ["status" => $this->success, "message" => "Appointment File Deleted.", 'html' => $html];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function prescriptionAppend($name, $appointment_id)
    {
        try {
            $unique_id = mt_rand(11, 99);
            $html = view('account.patients.prescription_append')->with(['name' => $name, 'unique_id' => $unique_id, 'appointment_id' => $appointment_id])->render();
            $result = ["status" => $this->success, "message" => "Prescription append.", 'html' => $html];
        } catch (Exception $e) {
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function prescriptionEdit($appointment_id)
    {
        /*  try { */
        $data['appointment'] = Appointment::find($appointment_id);
        $data['drugs'] = Drug::all();
        $html = view('account.patients.appointment_prescription_edit')->with($data)->render();
        $result = ["status" => $this->success, "message" => "Prescription edit form", 'html' => $html];
        /*  } catch (Exception $e) {
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        } */
        return Response::json($result, $this->status);
    }

    public function prescriptionStore(Request $request, Notification $notification)
    {
        $rules = [
            'appointment_id' => 'required|exists:appointments,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            /* try { */
            AppointmentPrescription::where('appointment_id', $request->get('appointment_id'))->delete();
            if ($request->get('detail') != null) {
                AppointmentPrescription::insert($request->get('detail'));
            }
            $appointment = Appointment::find($request->get('appointment_id'));
            $pharmacy = User::whereHas('role', function ($p) {
                $p->where('keyword', 'pharmacy');
            })->get();
            $share_pharmacy = SharePrescription::where('appointment_id', $appointment->id)->first();
            $html = view('account.patients.appointment_prescription')->with(['appointment' => $appointment, 'share_pharmacy' => $share_pharmacy, 'pharmacy' => $pharmacy])->render();

            Notification::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $appointment->patient_id,
                'title' => "" . Auth::user()->name . " has been uploaded the prescription. please check it",
                'type' => 'upload_prescription',
                'message' => "" . Auth::user()->name . " has been uploaded the prescription. please check it."
            ]);

            $androidToken = UserApp::where('user_id', $appointment->patient_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

            if (!empty($androidToken)) {
                $subject = "New Prescription Added";
                $sms_push_text = Auth::user()->name . " has been uploaded the prescription. please check it";
                $extra = ['id' => $appointment->id, 'type' => 'upload_prescription', 'appointment_status' => 'completed'];

                $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
            }

            $result = ['status' => $this->success, 'message' => 'Prescription add Successfully..', 'html' => $html];
            /*  } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            } */
        }
        return Response::json($result);
    }

    public function sendPrescription(Request $request, Notification $notification)
    {
        $rules = [
            'appointment_id' => 'required|exists:appointments,id',
            'pharmacy_id' => 'required|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                DB::beginTransaction();
                $input = $request->all();
                $appointment = Appointment::find($request->get('appointment_id'));
                $input['patient_id'] = $appointment->patient_id;
                $input['doctor_id'] = $appointment->doctor_id;
                $share_pharmacy = SharePrescription::create($input);
                $html = '<p class="float-left ml-2 text-warning"><b>Recommanded pharmacy is ' . $share_pharmacy->pharmacy->name . ', ' . $share_pharmacy->pharmacy->address . ', ' . $share_pharmacy->pharmacy->locality . '<b></p>';
                //send to patient
                Notification::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $appointment->patient_id,
                    'title' => Auth::user()->name . " has been send your prescription to " . $share_pharmacy->pharmacy->name . ".",
                    'type' => 'share_prescription',
                    'message' => Auth::user()->name . " has been the prescription to " . $share_pharmacy->pharmacy->name . ". Your prescription id was <strong>#" . $share_pharmacy->id . "</strong>. Pharmacy address was " . $share_pharmacy->pharmacy->address . "," . $share_pharmacy->pharmacy->locality,
                ]);

                $androidToken = UserApp::where('user_id', $appointment->patient_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                if (!empty($androidToken)) {
                    $subject = "Send Prescription To Pharmacy";
                    $sms_push_text = Auth::user()->name . " has been send your prescription to " . $share_pharmacy->pharmacy->name . ". Your prescription id was <strong>#" . $share_pharmacy->id . "</strong>. Pharmacy address was " . $share_pharmacy->pharmacy->address . "," . $share_pharmacy->pharmacy->locality . ".";
                    $extra = ['id' => $appointment->id, 'type' => 'share_prescription', 'appointment_status' => 'completed'];

                    $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                }

                //send to pharmacy
                Notification::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $input['pharmacy_id'],
                    'title' => Auth::user()->name . " has been send the prescription of " . $share_pharmacy->patient->name . ".",
                    'type' => 'share_prescription',
                    'message' => Auth::user()->name . " has been send the prescription of " . $share_pharmacy->patient->name . ". The prescription id was <strong>#" . $share_pharmacy->id . "</strong>.",
                ]);

                DB::commit();
                $result = ['status' => $this->success, 'message' => 'Prescription send Successfully..', 'html' => $html];
            } catch (Exception $e) {
                DB::rollBack();
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
    }
}
