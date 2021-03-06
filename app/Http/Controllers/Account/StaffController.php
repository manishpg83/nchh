<?php

namespace App\Http\Controllers\Account;

use App\Country;
use App\Jobs\StaffApprovalMailJob;
use App\Jobs\StaffRegisterAsDoctorJob;
use App\Jobs\StaffRegisterJob;
use App\Notification;
use App\UserDetail;
use App\User;
use App\StaffManager;
use App\UserRole;

use App\Mail\StaffRegister;
use App\Mail\StaffRegisterAsDoctor;
use App\PracticeManager;
use App\UserApp;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Timezone;

class StaffController extends BaseController
{
    protected $random;
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission')->except(['invitationReply']);
        $this->random = Str::random(12);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Staff');
        $data = [
            'title' => 'Staff',
            'user' => Auth::user(),
        ];

        if ($request->ajax()) {

            try {
                $user = StaffManager::where('added_by', Auth::id())->orderBy('id', 'DESC');

                $datatable = DataTables::of($user->get());

                $datatable = $datatable->addColumn('name', function ($data) {
                    return $data->user->name;
                });

                $datatable = $datatable->addColumn('email', function ($data) {
                    return isset($data->user->email) ? $data->user->email : 'Not Mentioned';
                });

                $datatable = $datatable->addColumn('phone', function ($data) {
                    return isset($data->user->phone_with_dialcode) ? $data->user->phone_with_dialcode : 'Not Mentioned';
                });

                $datatable = $datatable->addColumn('role', function ($data) {
                    return isset($data->user->role->name) ? $data->user->role->role_badge : 'Not Mentioned';
                });

                $datatable = $datatable->addColumn('fees', function ($data) {
                    return (isset($data->user->role->keyword) && $data->user->role->keyword == "doctor" && isset($data->practice->fees)) ? $data->practice->fees : '-';
                });

                $datatable = $datatable->addColumn('action', function ($data) {
                    $button = '';
                    $as_doctor = 0;
                    if (isset($data->user->role->keyword) && $data->user->role->keyword == "doctor") {
                        $as_doctor = 1;
                    }
                    if ($data->practice && $data->practice->status == 2 && $as_doctor) {
                        $button .= '<a href="' . route('account.staff.edit', [$data->id]) . '" class="mr-3" id="' . $data->id . '" data-toggle="tooltip" data-placement="top" title="Request accepted by ' . $data->practice->doctor['name'] . '"><i class="fas fa-highlighter"></i></a>';
                    }

                    if ($data->practice && $data->practice->status == 1 || !$as_doctor) {
                        $button .= '<a href="' . route('account.staff.edit', [$data->id]) . '" class="mr-3" id="' . $data->id . '" data-toggle="tooltip" data-placement="top" title="Edit"><i class="far fa-edit"></i></a><a href="javascript:;" class="" id="' . $data->id . '" data-toggle="tooltip" data-placement="top" title="Delete" onclick="deleteStaff(' . $data->id . ');"><i class="far fa-trash-alt"></i></a>';
                    }

                    if ($data->practice && $data->practice->status == 0 && $as_doctor) {
                        $button .= '<span class="badge badge-light">Request Pending</span>';
                    }

                    return $button;
                });

                $datatable = $datatable->rawColumns(['name', 'role', 'notification', 'action']);
                $datatable = $datatable->make(true);
                return $datatable;
            } catch (Exception $e) {
                $datatable = Datatables::of(PracticeManager::select()->take(0)->get());
                $datatable = $datatable->make(true);
            }
        }

        return view('account.staff.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (checkPermission(['clinic', 'hospital', 'pharmacy'])) {
            if (User::where('id', Auth::id())->where(function ($q) {
                return $q->whereNull('address')->orWhereNull('locality')->orWhereNull('city')->orWhereNull('state');
            })->exists()) {
                return redirect()->route('account.show-profile-form')->with('warning', "Please fill up your profile details before add staff.");
            }
        }

        $this->_setPageTitle('Add Staff');
        $selected = 'Asia/Kolkata';
        $placeholder = 'Select a Timezone';
        $formAttributes = array('class' => 'form-control select2', 'name' => 'timezone');
        $roles = checkPermission(['doctor']) ? ['manager', 'accountant'] : ['doctor', 'manager', 'accountant'];
        $data = [
            'title' => 'Staff',
            'user' => Auth::user(),
            'roles' => UserRole::whereIn('keyword', $roles)->get(),
            'timezonelist' => Timezone::selectForm($selected, $placeholder, $formAttributes),
            'country' => Country::pluck('name', 'id'),
            'existing_doctor' => StaffManager::whereHas('practice', function ($practice) {
                $practice->where(['status' => 1, 'added_by' => Auth::id()]);
            })->where('added_by', Auth::id())->pluck('user_id')
        ];

        return view('account.staff.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     * Auth = AS Doctor ~ Clinic ~ Hospital
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Notification $notification)
    {

        $rules = ['role_id' => 'required|exists:user_roles,id'];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            $as_doctor = 0;
            if (UserRole::where(['id' => $request->get('role_id'), 'keyword' => 'doctor'])->exists()) {
                $as_doctor = 1;
                $rules += [
                    'user_id' => 'required|exists:users,id',

                ];
            } else {
                $rules += [
                    'name' => 'required',
                    'email' => 'required|email:rfc,strict,dns|max:255',
                    'dialcode' => 'required',
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ];
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Redirect::back()->with('error', $validator->errors()->first());
            } else {
                try {
                    DB::beginTransaction();
                    $input = $request->all();
                    if ($as_doctor) {
                        $user = User::find($request->get('user_id'));
                        $staff = StaffManager::create(['user_id' => $request->get('user_id'), 'added_by' => Auth::id()]);
                        $input['doctor_id'] = $request->get('user_id');
                        $input['name'] = Auth::user()->name;
                        $input['email'] = Auth::user()->email;
                        $input['phone'] = Auth::user()->phone;
                        $input['logo'] = Auth::user()->image_name;
                        $input['address'] = Auth::user()->address;
                        $input['locality'] = Auth::user()->locality;
                        $input['city'] = Auth::user()->city;
                        $input['country'] = Auth::user()->country;
                        $input['pincode'] = Auth::user()->pincode;
                        $input['timing'] = '[{"day":0,"periods":[]},{"day":1,"periods":[]},{"day":2,"periods":[]},{"day":3,"periods":[]},{"day":4,"periods":[]},{"day":5,"periods":[]},{"day":6,"periods":[]}]';
                        $input['latitude'] = Auth::user()->latitude;
                        $input['longitude'] = Auth::user()->longitude;
                        $input['added_by'] = Auth::id();
                        $input['staff_id'] = $staff->id;
                        $input['status'] = 0;
                        /* Check doctor already add practice as clinic/hospital/pharmacy */
                        PracticeManager::updateOrCreate(['doctor_id' => $request->get('user_id'), 'added_by' => Auth::id()], $input);

                        if ($user->email) {
                            $record = [
                                'subject' => 'You have been invited to ' . Auth::user()->role->keyword,
                                'recipient_name' => $user->name,
                                'recipient_email' => $user->email,
                                'content' => "I'm glad to inform you invite as doctor at apollo hospital. Click on blow button to accept invitation,<br>

                            so please kindly request to accept invitaition. and  our team is excited to meet you and look forward to introducing themselves to you.<br>
                            <br>
                            Regards,                            
                            " . Auth::user()->name . " & Team!",
                                'added_by' => Auth::user(),
                                'title' => '',
                            ];

                            dispatch(new StaffRegisterAsDoctorJob($record));
                        }

                        Notification::create([
                            'sender_id' => Auth::id(),
                            'receiver_id' => $user->id,
                            'action_id' => $staff->id,
                            'title' => 'Invitation as Staff',
                            'type' => 'add_staff',
                            'message' => Auth::user()->name . ' has invited to you as a Doctor please accept the invitation.'
                        ]);

                        $androidToken = UserApp::where('user_id', $user->id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                        if (!empty($androidToken)) {
                            $subject = "Invitation as Staff";
                            $sms_push_text = Auth::user()->name . " has invite to you as a " . $user->role->name . ". please accept the invitation.";
                            $extra = ['id' => Auth::id(), 'type' => 'add_staff'];

                            $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                        }

                        DB::commit();
                        return redirect()->route('account.staff.index')->with('success', "Staff Added.");
                    } else {

                        $password = passwordGenerate(12);
                        $input['username'] = str_replace(' ', '', $request->get('name')) . mt_rand(111111, 999999);
                        $input['password'] = Hash::make($password);
                        $input['added_by'] = Auth::id();
                        $user = User::create($input);
                        if ($user) {
                            UserDetail::create(['user_id' => $user->id]);
                            StaffManager::create(['user_id' => $user->id, 'added_by' => Auth::id()]);

                            /* To share staff by email */
                            if ($input['email']) {
                                $user->password = $password;
                                $url = URL::signedRoute('autologin', ['user' => $user]);
                                if ($user->role->keyword == 'manager') {
                                    $work_role =  'manager';
                                } else {
                                    $work_role =  'account';
                                }
                                $record = [
                                    'subject' => 'You have been invited to NC Health Club.',
                                    'recipient_name' => $input['name'],
                                    'recipient_email' => $input['email'],
                                    'content' => "This email is inform to you about invite as " . $user->role->keyword . " member. and " . Auth::user()->name . " has granted to access for manage " . $work_role . " related work. please complete your profile.<br>
                                We are looking forward to working with you and seeing you achieve great things! ",
                                    'user' => $user,
                                    'soft_url' => $url,
                                    'sender_user' => Auth::user()->name,
                                ];
                                dispatch(new StaffRegisterJob($record));
                            }

                            DB::commit();
                            return redirect()->route('account.staff.index')->with('success', "Staff Added.");
                        } else {
                            DB::rollBack();
                            return back()->withError($this->exception_message);
                        }
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    return back()->withError($this->exception_message);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     *  If doctor then get address from the practice table and if manager and account then get from staff->user address
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $staff = StaffManager::find($id);
        if (!$staff) {
            return back()->withError("Record doesn't exist.");
        }
        $this->_setPageTitle('Manage Staff Detail');
        $placeholder = 'Select a Timezone';
        $formAttributes = array('class' => 'form-control select2', 'name' => 'timezone');
        $data = [
            'staff' => $staff,
            'location' => (isset($staff->user->role->keyword) && $staff->user->role->keyword == "doctor") ? $staff->practice : $staff->user,
            'as_doctor' => (isset($staff->user->role->keyword) && $staff->user->role->keyword == "doctor") ? $staff->user->id : 0,
            'timezonelist' => Timezone::selectForm($staff->user->timezone, $placeholder, $formAttributes),
            'country' => Country::pluck('name', 'id'),
        ];

        return view('account.staff.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, Notification $notification)
    {
        $rules = ['role_id' => 'required|exists:user_roles,id'];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            $as_doctor = 0;
            if (UserRole::where(['id' => $request->get('role_id'), 'keyword' => 'doctor'])->exists()) {
                $as_doctor = 1;
                $rules += [
                    'user_id' => 'required|exists:users,id',
                    'timing' => 'required',
                    'fees' => 'required|numeric',
                ];
            } else {
                $rules += [
                    'name' => 'required',
                    'email' => 'required|email:rfc,strict,dns,spoof|max:255',
                    'dialcode' => 'required',
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                    'address' => 'required',
                    'locality' => 'required',
                    'city' => 'required',
                    'state' => 'required',
                    'country' => 'required',
                    'pincode' => 'required'
                ];
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator);
            } else {
                try {
                    DB::beginTransaction();
                    $input = $request->all();
                    $input['doctor_id'] = $request->get('user_id');
                    $staff = StaffManager::find($id);
                    if ($as_doctor) {

                        /* Verify the schedule is added or not */
                        if ($request->get('timing')) {
                            $keys = array_keys_multi(json_decode($request->get('timing'), true));
                            if (!in_array('start', $keys)) {
                                return back()->with('error', "You have to add doctor schedule.");
                            }
                        }

                        $staff->update($input);
                        $input['status'] = 1;
                        PracticeManager::updateOrCreate(['staff_id' => $id], $input);

                        Notification::create([
                            'sender_id' => Auth::id(),
                            'receiver_id' => $request->get('user_id'),
                            'title' => 'Schedule Update',
                            'type' => 'add_schedule',
                            'message' => 'Your daily available time slot has been uploaded by ' . Auth::user()->role->keyword . ', please verify your schedule and contact us if you do need to correction'
                        ]);

                        $practice = PracticeManager::where('staff_id', $id)->first();

                        if ($practice->doctor->email) {
                            $record = [
                                'subject' => 'Update Your Daily Schedule At ' . Auth::user()->name,
                                'recipient_name' => $practice->doctor->name,
                                'recipient_email' => $practice->doctor->email,
                                'content' => "Your daily available time slot has been uploaded by " . Auth::user()->name . ", please verify your schedule and contact us if you do need to correction.
                            <br>
                            Regards,  <br>                          
                            " . Auth::user()->name . " & Team!",
                                'title' => '',
                            ];

                            dispatch(new StaffApprovalMailJob($record));
                        }

                        $androidToken = UserApp::where('user_id', $request->get('user_id'))->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                        if (!empty($androidToken)) {
                            $subject = "Schedule Update";
                            $sms_push_text = Auth::user()->name . " has update your schedule.";
                            $extra = ['id' => $practice->id, 'type' => 'add_schedule'];

                            $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                        }
                    } else {
                        $input['phone'] = preg_replace("/[^A-Za-z0-9]/", "", trim($input['phone']));
                        $staff->updated($input);
                        $staff->user->update($input);
                    }

                    DB::commit();
                    return redirect()->route('account.staff.index')->with('success', "Staff detail change successfully.");
                } catch (Exception $e) {
                    DB::rollBack();
                    return back()->withError($this->exception_message);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /* try { */
        $staff = StaffManager::find($id);
        if ($staff->user->role->keyword == 'doctor') {
            $staff->practice->status = 0;
            $staff->practice->save();
        }
        $staff->delete();
        $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        /* } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        } */
        return Response::json($result);
    }

    //staff invitaion reply by doctor
    public function invitationReply(Request $request)
    {
        try {
            if ($request->get('action') == 'accept') {
                $practice = PracticeManager::where('staff_id', $request->get('action_id'))->first();
                $practice->update(['status' => 1]);
                Notification::find($request->get('notification_id'))->delete();
                Notification::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $practice->added_by,
                    'title' => "Invitation Accept As Staff",
                    'type' => 'staff_invitation_accept',
                    'message' => 'Your invitation accepted by ' . Auth::user()->name . '. so please add doctor everyday schedule.'
                ]);
                $message = "Invitation Accept";

                if ($practice->addedBy->email) {
                    $record = [
                        'subject' => 'Invitation Accept As Staff',
                        'recipient_name' => $practice->addedBy->name,
                        'recipient_email' => $practice->addedBy->email,
                        'content' => "Your invitation accepted by " . Auth::user()->name . ". so please add doctor everyday schedule. 

                        Regards,                            
                        " . Auth::user()->name . " & Team!",
                        'title' => 'Inviatation Accepted',
                    ];

                    dispatch(new StaffApprovalMailJob($record));
                }
            } else {
                $practice = PracticeManager::where('staff_id', $request->get('action_id'))->first();
                $practice->delete();
                StaffManager::where('id', $request->get('action_id'))->delete();
                Notification::find($request->get('notification_id'))->delete();
                Notification::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $practice->added_by,
                    'title' => "Invitation Reject As Staff",
                    'type' => 'staff_invitation_reject',
                    'message' => 'Your invitation rejected by ' . Auth::user()->name . '. please contact to doctor if have any concern.'
                ]);
                $message = "Invitation Reject";

                if ($practice->addedBy->email) {
                    $record = [
                        'subject' => 'Invitation Reject As Staff',
                        'recipient_name' => $practice->addedBy->name,
                        'recipient_email' => $practice->addedBy->email,
                        'content' => "Your invitation rejected by " . Auth::user()->name . ". please contact to doctor if have any concern. 

                        Regards,                            
                        " . Auth::user()->name . " & Team!",
                        'title' => 'Inviatation Rejected',
                    ];

                    dispatch(new StaffApprovalMailJob($record));
                }
            }


            $result = ['status' => $this->success, 'message' => $message];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
