<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use App\Specialty;
use App\Notification;
use App\UserDetail;
use Carbon\Carbon;
use App\UserRole;
use Timezone;
use App\Country;
use App\Service;
use Yajra\DataTables\DataTables;
use App\State;
use App\User;
use App\City;
use App\Jobs\DoctorProfileVerificationJob;
use App\Jobs\AgentProfileVerificationJob;
use App\Jobs\BankAccountVerificationJob;
use App\Jobs\DiagnosticsProfileVerificationJob;
use Auth;
use DB;
use App\Setting;
use App\UserApp;
use Exception;
use Razorpay\Api\Api;
use Razorpay\Api\VirtualAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class PageController extends BaseController
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->api = new Api(config('razorpay.razor_key'), config('razorpay.razor_secret'));
    }

    //get all doctors list
    public function getDoctor(Request $request)
    {
        $this->_setPageTitle('Doctors');
        $data = ['title' => 'Doctors', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::whereHas('role', function ($d) {
                $d->where('keyword', 'doctor');
            })->where('as_doctor_verified', '2')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('email', function ($data) {
                    return isset($data->email) ? $data->email : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('specialty', function ($data) {
                    return isset($data->detail->specialty_name) ? $data->detail->specialty_name : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('experience', function ($data) {
                    return isset($data->detail->experience) ? $data->detail->experience . ' Years' : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('reg_date', function ($data) {
                    return isset($data->created_at) ? date("D, d M Y", strtotime($data->created_at)) : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->rawColumns(['name', 'phone', 'email', 'specialty', 'experience', 'reg_date'])
                ->make(true);
        }

        return view('admin.user.get_doctor')->with($data);
    }

    //get doctor profile verification pending list
    public function doctorProfileVerification(Request $request)
    {
        $this->_setPageTitle('Doctors');
        $data = ['title' => 'Doctors', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::whereHas('role', function ($q) {
                $q->whereIn('keyword', ['patient', 'doctor']);
            })->where('as_doctor_verified', '1')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    return $data->name;
                })->addColumn('profile', function ($data) {
                    return '<img src="' . $data->profile_picture . '" class="rounded" style="width:40px"/>';
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('location', function ($data) {
                    return isset($data->city) ? $data->city : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('action', function ($data) {
                    $btn = '<button type="button" onclick="checkDoctorDetail(' . $data->id . ');" id="' . $data->id . '" class="btn btn-mat btn-success btn-sm">Preview</button>';
                    return $btn;
                })->rawColumns(['name', 'profile', 'phone', 'location', 'action'])
                ->make(true);
        }

        Notification::where('receiver_id', Auth::id())->where('type', 'doctor_profile_verification')->update(['is_read' => '1']);

        return view('admin.user.doctor_verification')->with($data);
    }

    //view doctor profile
    public function checkDoctorDetail($id)
    {
        $data['user'] = User::find($id);
        $html = view('admin.user.doctor-details', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load user data.', 'html' => $html];
        return Response::json($result);
    }

    //verify doctor profile
    public function doctorProfileVerify(Request $request, Notification $notification)
    {
        $rules = [
            'id' => 'required|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $user = User::find($request->id);
                if ($request->get('action') == 'approved') {
                    $doctorRoleId = UserRole::where('keyword', 'doctor')->pluck('id')->first(); //get role id of doctor role
                    $user->update(['role_id' => $doctorRoleId, 'as_doctor_verified' => 2]);
                    Setting::updateOrCreate(['user_id' => $request->id], ['user_id' => $request->id]);
                    $title = 'Your profile has been verified';
                    $type = 'doctor_profile_verification_verify';
                    $content = 'Your profile with the name of ' . $user->name . ' has been approved.';
                    $sms_push_text =  $content;
                    $mail_title = 'Your profile has been approved.';
                    $mail_subject = 'Profile Approved on ' . $user->updated_at->format('d M, Y h:i a') . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                    Your profile with the name of ' . $user->name . ' has been approved.<br>
                    <br>
                    Thank you for apply,<br>
                    NC Health Hub';
                } else {
                    $user->update(['as_doctor_verified' => '3', 'doctor_rejection_reason' => $request->get('message')]);
                    $title = 'Your profile has been rejected';
                    $type = 'doctor_profile_verification_reject';
                    $content = 'Your profile with the name of ' . $user->name . ' has been rejected.' . $request->get('message');
                    $sms_push_text = $content;
                    $mail_title = 'Your profile has been rejected.';
                    $mail_subject = 'Profile Rejected on ' . date('d M, Y h:i a', strtotime($user->updated_at)) . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                    Your profile with the name of ' . $user->name . ' has been rejected.<br>
                    <br>
                    Thank you for apply,<br>
                    NC Health Hub';
                }

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request->id,
                    'title' => $title,
                    'type' => $type,
                    'message' => $request->get('message'),
                ];

                Notification::create($data);

                if ($user->email) {
                    $mailInfo = ([
                        'receiver_email' => $user->email,
                        'receiver_name' => $user->name,
                        'title' => $mail_title,
                        'subject' => $mail_subject,
                        'content' => $mail_content,
                    ]);
                    dispatch(new DoctorProfileVerificationJob($mailInfo)); //add mail to queue
                }

                /* start notification*/
                //send notification to app 
                $androidToken = UserApp::where('user_id', $user->id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                if (!empty($androidToken)) {
                    $subject = $title;
                    $extra = ['id' => $user->id, 'type' => $type];

                    $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                }
                /* end notification */

                $result = ['status' => $this->success, 'message' => 'Status change successfully.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    //get all clinic list
    public function getClinic(Request $request)
    {
        $this->_setPageTitle('Clinics');
        $data = ['title' => 'Clinics', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::whereHas('role', function ($c) {
                $c->where('keyword', 'clinic');
            })
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('email', function ($data) {
                    return isset($data->email) ? $data->email : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('locality', function ($data) {
                    return isset($data->full_address) ? $data->full_address : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('specialty', function ($data) {
                    return isset($data->detail->specialty_name) ? $data->detail->specialty_name : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('doctors', function ($data) {
                    $doctor = '';
                    if ($data->practiceAsStaff) {
                        foreach ($data->practiceAsStaff as $d) {
                            $doctor .= '<img alt="image" src="' . $d->doctor->profile_picture . '" class="rounded-circle mr-1" width="35" data-toggle="tooltip" title="' . $d->doctor->name . ' as a Specialist of ' . $d->doctor->detail->specialty_name . '">';
                        }
                    }
                    return $doctor;
                })->addColumn('gallery', function ($data) {
                    $gallery  = '';
                    if ($data->gallery) {
                        $gallery  .= '<ul id="portfolio" class="clearfix">';
                        foreach ($data->gallery as $g) {
                            $gallery .= '<li><a href="' . $g->image . '"><img alt="image" src="' . $g->image . '" class=" mr-1" width="50" data-toggle="tooltip" title=""></a></li>';
                        }
                        $gallery  .= '</ul>';
                    }
                    return $gallery;
                })->rawColumns(['name', 'phone', 'email', 'locality', 'specialty', 'doctors', 'gallery'])
                ->make(true);
        }

        return view('admin.user.get_clinic')->with($data);
    }
    //get all hospital list
    public function getHospital(Request $request)
    {
        $this->_setPageTitle('Hospitals');
        $data = ['title' => 'Hospitals', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::whereHas('role', function ($c) {
                $c->where('keyword', 'hospital');
            })
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('email', function ($data) {
                    return isset($data->email) ? $data->email : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('locality', function ($data) {
                    return isset($data->full_address) ? $data->full_address : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('specialty', function ($data) {
                    return isset($data->detail->specialty_name) ? $data->detail->specialty_name : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('services', function ($data) {
                    return isset($data->detail->services_list_name) ? $data->detail->services_list_name : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('doctors', function ($data) {
                    $doctor = '';
                    if ($data->practiceAsStaff) {
                        foreach ($data->practiceAsStaff as $d) {
                            $doctor .= '<img alt="image" src="' . $d->doctor->profile_picture . '" class="rounded-circle mr-1" width="35" data-toggle="tooltip" title="' . $d->doctor->name . ' as a Specialist of ' . $d->doctor->detail->specialty_name . '">';
                        }
                    }
                    return $doctor;
                })->addColumn('gallery', function ($data) {
                    $gallery  = '';
                    if ($data->gallery) {
                        $gallery  .= '<ul id="portfolio" class="clearfix">';
                        foreach ($data->gallery as $g) {
                            $gallery .= '<li><a href="' . $g->image . '"><img alt="image" src="' . $g->image . '" class=" mr-1" width="50" data-toggle="tooltip" title=""></a></li>';
                        }
                        $gallery  .= '</ul>';
                    }
                    return $gallery;
                })->rawColumns(['name', 'phone', 'email', 'locality', 'specialty', 'services', 'doctors', 'gallery'])
                ->make(true);
        }

        return view('admin.user.get_hospital')->with($data);
    }
    //get all pharmacy list
    public function getPharmacy(Request $request)
    {
        $this->_setPageTitle('Pharmacies');
        $data = ['title' => 'Pharmacies', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::whereHas('role', function ($c) {
                $c->where('keyword', 'pharmacy');
            })
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('email', function ($data) {
                    return isset($data->email) ? $data->email : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('action', function ($data) {
                    $btn = '<button type="button" onclick="checkDoctorDetail(' . $data->id . ');" id="' . $data->id . '" class="btn btn-mat btn-success btn-sm">Preview</button>';
                    return $btn;
                })->rawColumns(['name', 'phone', 'email', 'action'])
                ->make(true);
        }

        return view('admin.user.get_pharmacy')->with($data);
    }

    //get all agent list
    public function getAgent(Request $request)
    {
        $this->_setPageTitle('Agents');
        $data = ['title' => 'Agents', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::where('as_agent_verified', '2')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('email', function ($data) {
                    return isset($data->email) ? $data->email : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('locality', function ($data) {
                    return isset($data->locality) ? $data->locality . ',' . $data->city : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('reg_date', function ($data) {
                    return isset($data->created_at) ? date("D, d M Y", strtotime($data->created_at)) : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->rawColumns(['name', 'phone', 'email', 'locality', 'reg_date'])
                ->make(true);
        }

        return view('admin.user.get_agent')->with($data);
    }

    //get agent profile verification pending list
    public function agentProfileVerification(Request $request)
    {
        $this->_setPageTitle('Agents');
        $data = ['title' => 'Agents', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::where('as_agent_verified', '1')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    return $data->name;
                })->addColumn('profile', function ($data) {
                    return '<img src="' . $data->profile_picture . '" class="rounded" style="width:40px"/>';
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('location', function ($data) {
                    return isset($data->city) ? $data->city : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('action', function ($data) {
                    $btn = '<button type="button" onclick="checkAgentDetail(' . $data->id . ');" id="' . $data->id . '" class="btn btn-mat btn-success btn-sm">Preview</button>';
                    return $btn;
                })->rawColumns(['name', 'profile', 'phone', 'location', 'action'])
                ->make(true);
        }

        Notification::where('receiver_id', Auth::id())->where('type', 'agent_profile_verification')->update(['is_read' => '1']);

        return view('admin.user.agent_verification')->with($data);
    }


    //view agent profile
    public function checkAgentDetail($id)
    {
        $data['user'] = User::find($id);
        $html = view('admin.user.agent_details', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load user data.', 'html' => $html];
        return Response::json($result);
    }

    //verify agent profile
    public function agentProfileVerify(Request $request, Notification $notification)
    {
        $rules = [
            'id' => 'required|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $user = User::find($request->id);
                if ($request->get('action') == 'approved') {
                    $user->update(['as_agent_verified' => 2]);
                    $title = 'Your requested agent profile has been verified';
                    $type = 'agent_profile_verification_verify';
                    $content = 'Your requested agent profile with the name of ' . $user->name . ' has been approved.';
                    $sms_push_text =  $content;
                    $mail_title = 'Your requested agent profile has been approved.';
                    $mail_subject = 'Agent profile Approved on ' . $user->updated_at->format('d M, Y h:i a') . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                    Your requested agent profile with the name of ' . $user->name . ' has been approved.<br>
                    <br>
                    Thank you for apply,<br>
                    NC Health Hub';
                } else {
                    $user->update(['as_agent_verified' => '3']);
                    $title = 'Your requested agent profile has been rejected';
                    $type = 'agent_profile_verification_reject';
                    $content = 'Your requested agent profile with the name of ' . $user->name . ' has been rejected.' . $request->get('message');
                    $sms_push_text = $content;
                    $mail_title = 'Your requested agent profile has been rejected.';
                    $mail_subject = 'Agent profile Rejected on ' . date('d M, Y h:i a', strtotime($user->updated_at)) . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                    Your requested agent profile with the name of ' . $user->name . ' has been rejected.<br>
                    <br>
                    Thank you for apply,<br>
                    NC Health Hub';
                }

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request->id,
                    'title' => $title,
                    'type' => $type,
                    'message' => $request->get('message'),
                ];

                Notification::create($data);

                if ($user->email) {
                    $mailInfo = ([
                        'receiver_email' => $user->email,
                        'receiver_name' => $user->name,
                        'title' => $mail_title,
                        'subject' => $mail_subject,
                        'content' => $mail_content,
                    ]);
                    dispatch(new AgentProfileVerificationJob($mailInfo)); //add mail to queue
                }

                /* start notification*/
                //send notification to app 
                $androidToken = UserApp::where('user_id', $user->id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                if (!empty($androidToken)) {
                    $subject = $title;
                    $extra = ['id' => $user->id, 'type' => $type];

                    $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                }
                /* end notification */

                $result = ['status' => $this->success, 'message' => 'Status change successfully.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    //get all diagnostics list
    public function getDiagnostics(Request $request)
    {
        $this->_setPageTitle('Diagnostics');
        $data = ['title' => 'Diagnostics', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::where('as_diagnostics_verified', '2')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('email', function ($data) {
                    return isset($data->email) ? $data->email : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('locality', function ($data) {
                    return isset($data->locality) ? $data->locality . ',' . $data->city : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('reg_date', function ($data) {
                    return isset($data->created_at) ? date("D, d M Y", strtotime($data->created_at)) : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->rawColumns(['name', 'phone', 'email', 'locality', 'reg_date'])
                ->make(true);
        }

        return view('admin.user.get_diagnostics')->with($data);
    }

    //get diagnostics profile verification pending list
    public function diagnosticsProfileVerification(Request $request)
    {
        $this->_setPageTitle('Diagnostics');
        $data = ['title' => 'Diagnostics', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::where('as_diagnostics_verified', '1')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    return $data->name;
                })->addColumn('profile', function ($data) {
                    return '<img src="' . $data->profile_picture . '" class="rounded" style="width:40px"/>';
                })->addColumn('phone', function ($data) {
                    return isset($data->phone) ? $data->phone : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('location', function ($data) {
                    return isset($data->city) ? $data->city : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('action', function ($data) {
                    $btn = '<button type="button" onclick="checkDiagnosticsDetail(' . $data->id . ');" id="' . $data->id . '" class="btn btn-mat btn-success btn-sm">Preview</button>';
                    return $btn;
                })->rawColumns(['name', 'profile', 'phone', 'location', 'action'])
                ->make(true);
        }

        Notification::where('receiver_id', Auth::id())->where('type', 'diagnostics_profile_verification')->update(['is_read' => '1']);

        return view('admin.user.diagnostics_verification')->with($data);
    }


    //view diagnostics profile
    public function checkDiagnosticsDetail($id)
    {
        $data['user'] = User::find($id);
        $html = view('admin.user.diagnostics_details', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load user data.', 'html' => $html];
        return Response::json($result);
    }

    //verify diagnostics profile
    public function diagnosticsProfileVerify(Request $request, Notification $notification)
    {
        $rules = [
            'id' => 'required|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $user = User::find($request->id);
                Setting::updateOrCreate(['user_id' => $request->id], ['user_id' => $request->id]);
                if ($request->get('action') == 'approved') {
                    $user->update(['as_diagnostics_verified' => 2]);
                    $title = 'Your requested diagnostics profile has been verified';
                    $type = 'diagnostics_profile_verification_verify';
                    $content = 'Your requested diagnostics profile with the name of ' . $user->name . ' has been approved.';
                    $sms_push_text =  $content;
                    $mail_title = 'Your requested diagnostics profile has been approved.';
                    $mail_subject = 'Diagnostics profile Approved on ' . $user->updated_at->format('d M, Y h:i a') . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                    Your requested diagnostics profile with the name of ' . $user->name . ' has been approved.<br>
                    <br>
                    Thank you for apply,<br>
                    NC Health Hub';
                } else {
                    $user->update(['as_diagnostics_verified' => '3']);
                    $title = 'Your requested diagnostics profile has been rejected';
                    $type = 'diagnostics_profile_verification_reject';
                    $content = 'Your requested diagnostics profile with the name of ' . $user->name . ' has been rejected.' . $request->get('message');
                    $sms_push_text = $content;
                    $mail_title = 'Your requested diagnostics profile has been rejected.';
                    $mail_subject = 'Diagnostics profile Rejected on ' . date('d M, Y h:i a', strtotime($user->updated_at)) . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                    Your requested diagnostics profile with the name of ' . $user->name . ' has been rejected.<br>
                    <br>
                    Thank you for apply,<br>
                    NC Health Hub';
                }

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request->id,
                    'title' => $title,
                    'type' => $type,
                    'message' => $request->get('message'),
                ];

                Notification::create($data);

                if ($user->email) {
                    $mailInfo = ([
                        'receiver_email' => $user->email,
                        'receiver_name' => $user->name,
                        'title' => $mail_title,
                        'subject' => $mail_subject,
                        'content' => $mail_content,
                    ]);
                    dispatch(new DiagnosticsProfileVerificationJob($mailInfo)); //add mail to queue
                }

                $result = ['status' => $this->success, 'message' => 'Status change successfully.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    //get all user bank account list
    public function getUserBankAccount(Request $request)
    {
        $this->_setPageTitle('User Bank Account');
        $data = ['title' => 'User Bank Account', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::where('is_bank_verified', '2')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    if ($data->name != null) {
                        $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('bank_name', function ($data) {
                    return isset($data->bankDetail->bank_name) ? $data->bankDetail->bank_name : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('account_number', function ($data) {
                    return isset($data->bankDetail->account_number) ? $data->bankDetail->account_number : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('ifsc_code', function ($data) {
                    return isset($data->bankDetail->ifsc_code) ? $data->bankDetail->ifsc_code : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('account_type', function ($data) {
                    return isset($data->bankDetail->account_type) ? $data->bankDetail->account_type : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('beneficiary_name', function ($data) {
                    return isset($data->bankDetail->beneficiary_name) ? $data->bankDetail->beneficiary_name : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->rawColumns(['name', 'bank_name', 'account_number', 'ifsc_code', 'account_type', 'beneficiary_name'])
                ->make(true);
        }

        return view('admin.user.get_user_bank_account')->with($data);
    }

    //get diagnostics profile verification pending list
    public function userBankAccountVerification(Request $request)
    {
        $this->_setPageTitle('User Bank Account');
        $data = ['title' => 'User Bank Account', 'user' => Auth::user()];
        if ($request->ajax()) {
            $users = User::where('is_bank_verified', '1')
                ->orderBy('id', 'DESC')->get();
            return Datatables::of($users)
                ->addColumn('name', function ($data) {
                    return $data->name;
                })->addColumn('bank_name', function ($data) {
                    return isset($data->bankDetail->bank_name) ? $data->bankDetail->bank_name : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('account_number', function ($data) {
                    return isset($data->bankDetail->account_number) ? $data->bankDetail->account_number : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('ifsc_code', function ($data) {
                    return isset($data->bankDetail->ifsc_code) ? $data->bankDetail->ifsc_code : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('account_type', function ($data) {
                    return isset($data->bankDetail->account_type) ? $data->bankDetail->account_type : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
                })->addColumn('action', function ($data) {
                    $btn = '<button type="button" onclick="verifyBankAccountDetail(' . $data->id . ', `approved`)" id="' . $data->id . '" class="btn btn-mat btn-success btn-sm">Approved</button>
                    <button type="button" onclick="rejectBankAccountDetail(' . $data->id . ')" id="' . $data->id . '" class="btn btn-mat btn-danger btn-sm ml-2">Reject</button>';
                    return $btn;
                })->rawColumns(['name', 'bank_name', 'account_number', 'ifsc_code', 'account_type', 'action'])
                ->make(true);
        }

        Notification::where('receiver_id', Auth::id())->where('type', 'bank_account_verification')->update(['is_read' => '1']);

        return view('admin.user.user_bank_account_verification')->with($data);
    }

    //verify diagnostics profile
    public function userBankAccountVerify(Request $request, Notification $notification)
    {
        $rules = [
            'id' => 'required|exists:users,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $user = User::find($request->id);
                Setting::updateOrCreate(['user_id' => $request->id], ['user_id' => $request->id]);
                if ($request->get('action') == 'approved') {
                    $BankDetails = [
                        "name" => preg_replace('/[^A-Za-z0-9\-]/', ' ', $user->name),
                        "email" => $user->email,
                        "tnc_accepted" => true,
                        "account_details" => [
                            "business_name" => "NC Health Hub",
                            "business_type" => "individual"
                        ],
                        "bank_account" => [
                            "ifsc_code" => $user->bankDetail->ifsc_code,
                            "beneficiary_name" => $user->bankDetail->beneficiary_name,
                            "account_type" => $user->bankDetail->account_type,
                            "account_number" => $user->bankDetail->account_number
                        ]
                    ];

                    $authentication = [
                        config('razorpay.razor_key'),
                        config('razorpay.razor_secret')
                    ];

                    $client = new Client();
                    $response = $client->request(
                        'POST',
                        'https://api.razorpay.com/v1/beta/accounts',
                        [
                            'auth' => $authentication,
                            "Content-type" => " application/json",
                            'form_params' => $BankDetails,
                        ]
                    );

                    $data = json_decode($response->getBody()->getContents());
                    /* dd($data->id); */

                    $user->update(['is_bank_verified' => 2, 'account_id' => $data->id]);
                    $title = 'Your requested bank account details has been verified';
                    $type = 'bank_account_verification_verify';
                    $content = 'Your requested bank account details with the name of ' . $user->bankDetail->bank_name . ' has been approved.';
                    $sms_push_text =  $content;
                    $mail_title = 'Your requested bank account details has been approved.';
                    $mail_subject = 'Bank Account Details Approved on ' . $user->updated_at->format('d M, Y h=>i a') . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                     Your requested bank account details with the name of ' . $user->bankDetail->bank_name . ' has been approved.<br>
                     <br>
                     Thank you for apply,<br>
                     NC Health Hub';
                } else {
                    $user->update(['is_bank_verified' => '3', 'rejection_reason' => $request->rejection_reason]);
                    $title = 'Your requested bank account details has been rejected';
                    $type = 'bank_account_verification_reject';
                    $content = 'Your requested bank account details with the name of ' . $user->bankDetail->bank_name . ' has been rejected.';
                    $sms_push_text = $content;
                    $mail_title = 'Your requested bank account details has been rejected.';
                    $mail_subject = 'Bank account details Rejected on ' . date('d M, Y h:i a', strtotime($user->updated_at)) . ' EST';
                    $mail_content = 'Hey ' . $user->name . ',<br>
                     Your requested bank account details with the name of ' . $user->bankDetail->bank_name . ' has been rejected.<br>
                     <br>
                     Thank you for apply,<br>
                     NC Health Hub';
                }

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => $request->id,
                    'title' => $title,
                    'type' => $type,
                    'message' => $request->get('message'),
                ];

                Notification::create($data);

                if ($user->email) {
                    $mailInfo = ([
                        'receiver_email' => $user->email,
                        'receiver_name' => $user->name,
                        'title' => $mail_title,
                        'subject' => $mail_subject,
                        'content' => $mail_content,
                    ]);
                    dispatch(new BankAccountVerificationJob($mailInfo)); //add mail to queue
                }

                /* start notification*/
                //send notification to app 
                $androidToken = UserApp::where('user_id', $user->id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                if (!empty($androidToken)) {
                    $subject = $title;
                    $extra = ['id' => $user->id, 'type' => $type];

                    $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                }
                /* end notification */

                $result = ['status' => $this->success, 'message' => 'Status change successfully.'];
            } catch (ClientException $e) {
                $response = $e->getResponse();
                $data = json_decode($response->getBody()->getContents());
                $result = ['status' => $this->error, 'message' => $data->error->description];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
            return Response::json($result);
        }
    }
}
