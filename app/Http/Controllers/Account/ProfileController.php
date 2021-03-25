<?php

namespace App\Http\Controllers\Account;

use App\User;
use App\Country;
use App\Jobs\DoctorProfileVerificationJob;
use App\Jobs\AgentProfileVerificationJob;
use App\Jobs\BankAccountVerificationJob;
use App\Jobs\DiagnosticsProfileVerificationJob;
use App\Specialty;
use App\Notification;
use App\Repositories\UploadRepository;
use App\TimingManager;
use App\Upload;
use App\UserBankAccount;
use App\UserDetail;
use App\UserWallet;
use App\UserWithdrawHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Image;
use Exception;
use Razorpay\Api\Api;
use Yajra\DataTables\Facades\DataTables;

class ProfileController extends BaseController
{
    protected $random;
    protected $uploadRepository;
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong, please try again.";

    public function __construct(UploadRepository $uploadRepository)
    {
        // $this->middleware('auth');
        // $this->random = Str::random(12);
        $this->middleware('checkPermission', ['only' => ['index']]);
        $this->api = new Api(config('razorpay.razor_key'), config('razorpay.razor_secret'));
        $this->uploadRepository = $uploadRepository;
    }

    /* Function for Profiles Page */
    public function index()
    {
        $this->_setPageTitle('Profile');
        $data = [
            'title' => 'Profile',
            'user' => Auth::user(),
            'country' => Country::pluck('name', 'id')->toArray(),
            'specialty' => Specialty::pluck('title', 'id')->toArray(),
            'step' => dedicateProfileSteps()
        ];

        return view("account.profiles.index")->with($data);
    }

    /* Function for show profile details */
    public function showProfileDetailsForm(Request $request)
    {
        $data = [
            'title' => 'Accounts',
            'user' => Auth::user(),
            'specialist' => Specialty::pluck('title', 'id')->toArray()
        ];
        try {
            if($request->type && $request->type == 'approved-doctor') 
                $html = view('account.profiles.approved-doctor-detail-modal', $data)->render();
            else
                $html = view('account.profiles.profile-modal', $data)->render();
            
            $result = ["status" => $this->success, "message" => "Form loded", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function editDoctorProfile(Request $request, $id)
    {
        $input = $request->all();
        $rules = [
            'name' => 'required',
            'specialty_ids' => 'required',
            'gender' => 'required',
            'detail.registration_number' => 'required',
            'detail.registration_year' => 'required',
            'detail.liecence_number' => 'required',
            'detail.degree' => 'required',
            'detail.collage_or_institute' => 'required',
            'detail.year_of_completion' => 'required',
            'detail.experience' => 'required',
            'terms_and_condition' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ['status' => $this->error, 'message' => $validator->errors()->first()];
        } else {
            try {
                DB::beginTransaction();
                $user = User::find($id);

                if ($request->get('specialty_ids')) {
                    $user_specialties = arrayToString($request->get('specialty_ids'));
                }

                $user->update($input);
                if ($request->get('detail')) {
                    $detail = $request->get('detail');
                    $detail['specialty_ids'] = isset($user_specialties) ? $user_specialties : '';
                    UserDetail::where(['user_id' => $user->id])->update($detail);
                }

                $result = ['status' => $this->success, 'message' => "Your profile update successfully.", "result" => $user];
                DB::commit();
            } catch (Exception $e) {
                $this->status = 401;
                DB::rollBack();
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }

        return Response::json($result, $this->status);
    }

    /* Function for show profile document form */
    public function showProfileDocumentForm(Request $request)
    {
        try {
            $data = ['title' => 'Accounts', 'user' => Auth::user()];
            if($request->type && $request->type == 'approved-document')
                $html = view('account.profiles.show-verified-document-modal', $data)->render();
            else
                $html = view('account.profiles.upload-document', $data)->render();

            $result = ["status" => $this->success, "message" => "Form loded", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    /* Function for store profile document */
    public function storeProfileDocuments(Request $request)
    {
        try {
            $user = Auth::user();
            $data = [];

            if ($request->has('identity_document') && !empty($request->file('identity_document'))) {

                $identity_document = $request->file('identity_document');
                $filename = time() . uniqId() . '.' . $identity_document->getClientOriginalExtension();
                Image::make($identity_document)->fit(500, 500, function ($constraint) {
                    $constraint->upsize();
                })->save(storage_path('app/document/' . $filename));
                $data['identity_proof'] = $filename;

                /*remove the existing profile picture*/
                $identity_path = storage_path('app/document/' . $user->detail->identity_proof_name);
                if ($user->detail->identity_proof_name != "no_image.png") {
                    @unlink($identity_path);
                }
            }

            if ($request->has('medical_document') && !empty($request->file('medical_document'))) {
                $medical_proof_document = $request->file('medical_document');
                $medical_filename = time() . uniqId() . '.' . $medical_proof_document->getClientOriginalExtension();
                Image::make($medical_proof_document)->fit(500, 500, function ($constraint) {
                    $constraint->upsize();
                })->save(storage_path('app/document/' . $medical_filename));
                $data['medical_registration_proof'] = $medical_filename;

                /*remove the existing profile picture*/
                $medical_proof_path = storage_path('app/document/' . $user->detail->medical_registration_proof_name);
                if ($user->detail->medical_registration_proof_name != "no_image.png") {
                    @unlink($medical_proof_path);
                }
            }

            $user->detail()->update($data);
            $user->update(['as_doctor_verified' => '1']);

            //get super admin id
            $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                $q->where('keyword', 'admin');
            })->first();

            $data = [
                'sender_id' => Auth::id(),
                'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
                'title' => 'User Request For Doctor Profile Approval',
                'type' => 'doctor_profile_verification',
                'message' => Auth::user()->name . ' has requested for approval profile as a doctor. please verify the details.',
            ];

            Notification::create($data);

            if ($is_admin->email) {
                $mailInfo = ([
                    'receiver_email' => $is_admin->email,
                    'receiver_name' => 'NC Health HUB',
                    'title' => '',
                    'subject' => 'Apply Profile As Doctor ' . $user->name,
                    'content' => 'I would like to request approval for my profile as a doctor. I have uploaded all the details and documents.<br>
                    Please click on the button to see all details.<br>
                    Please, let me know if any concerns about the same.<br>
                    <br>
                    <br>
                    Thanks.',

                ]);
                dispatch(new DoctorProfileVerificationJob($mailInfo)); //add mail to queue
                dispatch(new DoctorProfileVerificationJob($mailInfo)); //add mail to queue
            }

            $result = ["status" => $this->success, "message" => "Your Documents uploaded successfully."];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }

        return Response::json($result);
    }

    /* Function for show establishment form */
    public function showEstablishmentDetailsForm()
    {
        try {
            $data = [
                'title' => 'Establishment Details',
                'user' => Auth::user()
            ];
            $html = view('account.profiles.establishment', $data)->render();
            $result = ["status" => $this->success, "message" => "Establishment load.", 'result' => $data, 'html' => $html];
        } catch (Exception $e) {
            $result = ["status" => $this->success, "message" => $this->exception_message];
        }
        return Response::json($result);
    }

    /* Function for store establishment details */
    public function storeEstablishmentDetails(Request $request)
    {
        $input = $request->all();
        try {
            UserDetail::updateOrCreate(['user_id' => Auth::id()], $input);
            $result = ["status" => $this->success, "message" => "Detail uploaded."];
        } catch (Exception $e) {
            $this->status = 401;
            $result = ["status" => $this->success, "message" => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    /* Function for store establishment details */
    public function storeEstablishmentTimings(Request $request)
    {
        $input = $request->all();
        // $timing = (array)json_decode($input['timings']);
        // p($timing);
        try {
            $input['doctor_id'] = Auth::id();
            // p($input);
            TimingManager::updateOrCreate(['doctor_id' => Auth::id()], $input);
            $result = ["status" => $this->success, "message" => "Timing uploaded."];
        } catch (Exception $e) {
            $result = ["status" => $this->success, "message" => $this->exception_message];
        }
        return Response::json($result);
    }

    /* Function for Agent profile */
    public function beingAgent(Request $request)
    {
        $this->_setPageTitle('Agent Profile');
        $data = [
            'title' => 'Agent Profile',
            'user' => Auth::user(),
        ];

        return view("account.profiles.agent_profile")->with($data);
    }

    public function showAgentProfileDetailsForm(Request $request)
    {
        try {
            $user = Auth::user();
            $documents = [];
            if($user->agentDocuments)
                $documents = $user->agentDocuments;
                
            $data = ['title' => 'Agent Profile', 'user' => $user , 'documents' => $documents];
            if($request->type && $request->type == 'approved-document')
                $html = view('account.profiles.verified_agent_document_modal', $data)->render();
            else
                $html = view('account.profiles.agent_document', $data)->render();

            $result = ["status" => $this->success, "message" => "Form loded", 'html' => $html];
        } catch (Exception $e) {
            dd($e);
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function storeAgentDocument(Request $request)
    {

        $rules = [
            'document_name' => 'required',
            'document_name.*' => 'required',
            'document_proof.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'agree' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator->errors()->first()];
        } else {
            try {
                $user = Auth::user();
                $data = [];
                
                foreach($request->document_name as $key => $docName) {
                    $params = [];
                    $params['title'] = $docName;
                    $params['type'] = 'agent';
                    if(isset($request->document_proof[$key]))
                        $params['document'] = $request->document_proof[$key];

                    $documentId = null;    
                    if(isset($request->document_id[$key])){
                        $documentId = $request->document_id[$key];
                    }

                    $params['upload_path'] = config('custom.uploads.agent_doc');

                    $upload = $this->uploadRepository->uploadDocument($params, $user, $documentId);
                }

                $deleteDocId = array_diff_key($request->document_id, $request->document_name);
                $deleteDoc = Upload::whereIn('id', $deleteDocId)->delete();

                $user->update(['as_agent_verified' => '1']);

                //get super admin id
                $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                    $q->where('keyword', 'admin');
                })->first();

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
                    'title' => 'User Request For Agent Profile Approval',
                    'type' => 'agent_profile_verification',
                    'message' => Auth::user()->name . ' has requested for approval profile as a agent. please verify the details.',
                ];

                Notification::create($data);

                if ($is_admin->email) {
                    $mailInfo = ([
                        'receiver_email' => $is_admin->email,
                        'receiver_name' => 'NC Health HUB',
                        'title' => '',
                        'subject' => 'Apply Profile As Agent ' . $user->name,
                        'content' => 'I would like to request approval for my profile as a agent. I have uploaded all the details and documents.<br>
                        Please click on the button to see all details.<br>
                        Please, let me know if any concerns about the same.<br>
                        <br>
                        <br>
                        Thanks.',

                    ]);
                    dispatch(new AgentProfileVerificationJob($mailInfo)); //add mail to queue
                }

                $result = ["status" => $this->success, "message" => "Your Documents uploaded successfully."];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    /* Function for Diagnostics profile */
    public function beingDiagnostics(Request $request)
    {
        $this->_setPageTitle('Diagnostics Profile');
        $data = [
            'title' => 'Diagnostics Profile',
            'user' => Auth::user(),
        ];

        return view("account.profiles.diagnostics_profile")->with($data);
    }

    public function showDiagnosticsProfileDetailsForm(Request $request)
    {
        try {
            $user = Auth::user();
            $documents = [];
            if($user->diagnosticsDocuments)
                $documents = $user->diagnosticsDocuments;

            $data = ['title' => 'Diagnostics Profile', 'user' => Auth::user(), 'documents' => $documents];
            if($request->type && $request->type == 'approved-document')
                $html = view('account.profiles.verified_diagnostics_document_modal', $data)->render();
            else
                $html = view('account.profiles.diagnostics_document', $data)->render();

            $result = ["status" => $this->success, "message" => "Form loded", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function storeDiagnosticsDocument(Request $request)
    {

        $rules = [
            'document_name' => 'required',
            'document_name.*' => 'required',
            'document_proof.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'agree' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator->errors()->first()];
        } else {
            try {
                $user = Auth::user();
                $data = [];
                foreach($request->document_name as $key => $docName) {
                    $params = [];
                    $params['title'] = $docName;
                    $params['type'] = 'diagnostics';
                    if(isset($request->document_proof[$key]))
                        $params['document'] = $request->document_proof[$key];

                    $documentId = null;    
                    if(isset($request->document_id[$key])){
                        $documentId = $request->document_id[$key];
                    }

                    $params['upload_path'] = config('custom.uploads.agent_doc');

                    $upload = $this->uploadRepository->uploadDocument($params, $user, $documentId);
                }

                $deleteDocId = array_diff_key($request->document_id, $request->document_name);
                $deleteDoc = Upload::whereIn('id', $deleteDocId)->delete();

                $user->update(['as_diagnostics_verified' => '1']);

                //get super admin id
                $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                    $q->where('keyword', 'admin');
                })->first();

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
                    'title' => 'User Request For Diagnostics Profile Approval',
                    'type' => 'diagnostics_profile_verification',
                    'message' => Auth::user()->name . ' has requested for approval profile as a diagnostics. please verify the details.',
                ];

                Notification::create($data);

                if ($is_admin->email) {
                    $mailInfo = ([
                        'receiver_email' => $is_admin->email,
                        'receiver_name' => 'NC Health HUB',
                        'title' => '',
                        'subject' => 'Apply Profile As Diagnostics ' . $user->name,
                        'content' => 'I would like to request approval for my profile as a diagnostics. I have uploaded all the details and documents.<br>
                         Please click on the button to see all details.<br>
                         Please, let me know if any concerns about the same.<br>
                         <br>
                         <br>
                         Thanks.',

                    ]);
                    dispatch(new DiagnosticsProfileVerificationJob($mailInfo)); //add mail to queue
                }

                $result = ["status" => $this->success, "message" => "Your Documents uploaded successfully."];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    /* Function for bank account verify */
    public function userBankAccount(Request $request)
    {
        $this->_setPageTitle('Bank Account');
        $data = [
            'title' => 'Bank Account',
            'user' => Auth::user(),
        ];

        return view("account.profiles.bank_account_verify")->with($data);
    }


    public function showBankAccountDetailsForm(Request $request)
    {
        try {
            $data = ['title' => 'Bank Account Verification', 'user' => Auth::user()];
            $html = view('account.profiles.bank_account_verification_form', $data)->render();
            $result = ["status" => $this->success, "message" => "Form loded", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function storeBankAccountDetails(Request $request)
    {

        $rules = [
            'bank_name'  => 'required',
            'account_number'  => 'required',
            'ifsc_code'  => 'required',
            'beneficiary_name'  => 'required|regex:/^[a-zA-Z]+$/u',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator->errors()->first()];
        } else {
            try {
                $input = $request->all();
                $input['user_id'] = Auth::id();
                $user = Auth::user();
                $data = [];
                $user_account = UserBankAccount::updateOrCreate(['user_id' => $input['user_id']], $input);
                $user->update(['is_bank_verified' => 1, 'account_id' => $user_account->id]);  // Added , 'account_id' => $user_account->id By Manish Bhuva

                //get super admin id
                $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                    $q->where('keyword', 'admin');
                })->first();

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
                    'title' => 'User Request For Bank Account Approval',
                    'type' => 'bank_account_verification',
                    'message' => Auth::user()->name . ' has requested for approval bank account details. please verify the details.',
                ];

                Notification::create($data);

                if ($is_admin->email) {
                    $mailInfo = ([
                        'receiver_email' => $is_admin->email,
                        'receiver_name' => 'NC Health HUB',
                        'title' => '',
                        'subject' => 'Apply For Verification Of Bank Details',
                        'content' => 'I would like to request approval for my bank account details. I have uploaded all the details.<br>
                         Please click on the button to see all details.<br>
                         Please, let me know if any concerns about the same.<br>
                         <br>
                         <br>
                         Thanks.',

                    ]);
                    dispatch(new BankAccountVerificationJob($mailInfo)); //add mail to queue
                }

                $result = ["status" => $this->success, "message" => "Your Documents uploaded successfully."];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }

    public function viewBankAccountDetails()
    {
        try {
            $data = ['title' => 'Bank Account Details', 'user' => Auth::user()];
            $html = view('account.profiles.bank_account_verification_detail', $data)->render();
            $result = ["status" => $this->success, "message" => "data loded", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function userWallet(Request $request)
    {
        $this->_setPageTitle('Wallet');

        $total_balance = UserWallet::whereHas('appointment', function ($q) {
            $q->whereIn('status', ['completed', 'create']);
        })->where('user_id', [Auth::id()])->where('status', null)->sum('price');

        $withdrawable_balance = UserWallet::whereHas('appointment', function ($q) {
            $q->where('status', ['completed']);
        })->where('user_id', [Auth::id()])->where('status', null)->sum('price');

        $data = [
            'title' => 'Wallet',
            'user' => Auth::user(),
            'total_balance' => $total_balance,
            'withdrawable_balance' => $withdrawable_balance,
        ];
        if ($request->ajax()) {
            $userWallet = UserWallet::where('user_id', [Auth::id()])->withTrashed()->orderBy('id', 'DESC')->get();
            return DataTables::of($userWallet)
                ->addColumn('name', function ($data) {
                    return $data->appointment->patient_name;
                })->addColumn('date', function ($data) {
                    return isset($data->created_at) ? date('d M, Y h:i a', strtotime($data->created_at)) : 'No mentioned';
                })->addColumn('price', function ($data) {
                    if (isset($data->status) && $data->status == 'refunded') {
                        $btn = '<span class="text-danger">- ' . $data->price . '</span>';
                    } else {
                        if ($data->appointment->status == 'completed') {
                            $btn = '<span class="text-success">+ ' . $data->price . '</span>';
                        } else {
                            $btn = '<span class="text-warning">+ ' . $data->price . '</span>';
                        }
                    }
                    return $btn;
                })->addColumn('status', function ($data) {
                    $btn = isset($data->status) ? ucfirst($data->status) : '-';
                    return $btn;
                })
                ->rawColumns(['name', 'date', 'price', 'status'])
                ->make(true);
        }
        return view('account.user.wallet')->with($data);
    }

    public function userWalletBalanceWithdraw()
    {
        try {
            $withdrawable_balance = UserWallet::whereHas('appointment', function ($q) {
                $q->where('status', ['completed']);
            })->where('user_id', [Auth::id()])->where('status', null)->sum('price');
            // Added Auth::user()->is_bank_verified == 2 By Manish Bhuva
            if ($withdrawable_balance > 0 && Auth::user()->is_bank_verified == 2 && Auth::user()->account_id ) {

                $input = [
                    'account' => Auth::user()->account_id,
                    'amount' => $withdrawable_balance * 100,
                    'currency' => 'INR',
                ];
                $razorpayOrder = $this->api->transfer->create($input);

                if ($razorpayOrder->id) {
                    $history = [
                        'user_id' => Auth::id(),
                        'transfer_id' => $razorpayOrder->id,
                        'source_id' => $razorpayOrder->source,
                        'recipient_id' => $razorpayOrder->recipient,
                        'amount' => $razorpayOrder->amount / 100,
                        'currency' => $razorpayOrder->currency,
                        'fee' => $razorpayOrder->fees / 100,
                        'tax' => $razorpayOrder->tax / 100,
                        'date' => Carbon::createFromTimestamp($razorpayOrder->created_at),
                    ];

                    UserWithdrawHistory::create($history);
                    UserWallet::whereHas('appointment', function ($q) {
                        $q->where('status', ['completed']);
                    })->where('user_id', [Auth::id()])->where('status', null)->update(['status' => 'received']);
                    UserWallet::whereHas('appointment', function ($q) {
                        $q->where('status', ['completed']);
                    })->where('user_id', [Auth::id()])->where('status', 'received')->delete();

                    $total_balance = UserWallet::whereHas('appointment', function ($q) {
                        $q->whereIn('status', ['completed', 'create']);
                    })->where('user_id', [Auth::id()])->where('status', null)->sum('price');

                    $withdrawable_balance = UserWallet::whereHas('appointment', function ($q) {
                        $q->where('status', ['completed']);
                    })->where('user_id', [Auth::id()])->where('status', null)->sum('price');

                    $result = ["status" => $this->success, "message" => "Wallet balance transfer successfully", 'total_balance' => $total_balance, "withdrawable_balance" => $withdrawable_balance];
                } else {
                    $result = ["status" => $this->error, "message" => $this->exception_message];
                }
            } else {
                if (Auth::user()->is_bank_verified != 2) {
                    $message = "Please verify your bank Account";
                }else{
                    $message = "You have not sufficient balance in wallet";
                }
                $result = ["status" => $this->error, "message" => $message];
            }
        } catch (Exception $e) {
            if ($e->getMessage()) {
                $message = $e->getMessage();
            } else {
                $message = $this->exception_message;
            }
            $result = ['status' => $this->error, 'message' => $message];
        }
        return Response::json($result);
    }

    public function userWalletWithdrawHistory(Request $request)
    {
        $this->_setPageTitle('Withdraw History');

        $data = [
            'title' => 'Withdraw History',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $withdra_history = UserWithdrawHistory::where('user_id', [Auth::id()])->orderBy('id', 'DESC')->get();
            return DataTables::of($withdra_history)
                ->addColumn('transfer_id', function ($data) {
                    return $data->transfer_id;
                })->addColumn('date', function ($data) {
                    return isset($data->date) ? date('d M, Y h:i a', strtotime($data->date)) : 'No mentioned';
                })->addColumn('amount', function ($data) {
                    return isset($data->amount) ? '<span class="text-success">+ ' . $data->amount . '</span>' : '-';
                })->addColumn('currency', function ($data) {
                    return isset($data->currency) ? $data->currency : '-';
                })->rawColumns(['transfer_id', 'date', 'amount', 'currency'])
                ->make(true);
        }
        return view('account.user.withdraw_history')->with($data);
    }
}
