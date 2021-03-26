<?php

namespace App\Http\Controllers\Account;

use App\Jobs\AgentProfileVerificationJob;
use App\Notification;
use App\Repositories\UploadRepository;
use App\Upload;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Exception;

class ClinicController extends BaseController
{
    protected $random;
    protected $uploadRepository;
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong, please try again.";

    public function __construct(UploadRepository $uploadRepository)
    {
        $this->uploadRepository = $uploadRepository;
    }

    public function apply(Request $request)
    {
        $this->_setPageTitle('Clinic Profile');
        $data = [
            'title' => 'Clinic Profile',
            'user' => Auth::user(),
        ];

        return view("account.profiles.clinic.profile")->with($data);
    }

    public function showProfile(Request $request)   {
        
        try {
            $user = Auth::user();
            $documents = $user->clinicDocuments;
            $data = ['title' => 'Clinic Profile', 'user' => $user, 'documents' => $documents];
            if($request->type && $request->type == 'approved-document')
                $html = view('account.profiles.clinic.verified_clinic_document_modal', $data)->render();
            else
                $html = view('account.profiles.clinic.document_upload', $data)->render();

            $result = ["status" => $this->success, "message" => "Form loded", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function storeProfile(Request $request)  {
        
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
                    $params['type'] = 'clinic';
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

                $user->update(['as_clinic_verified' => '1']);

                //get super admin id
                $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                    $q->where('keyword', 'admin');
                })->first();

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => isset($is_admin->id) ? $is_admin->id : null,
                    'title' => 'User Request For Clinic Profile Approval',
                    'type' => 'clinic_profile_verification',
                    'message' => Auth::user()->name . ' has requested for approval profile as a clinic. please verify the details.',
                ];

                Notification::create($data);

                if ($is_admin->email) {
                    $mailInfo = ([
                        'receiver_email' => $is_admin->email,
                        'receiver_name' => 'NC Health HUB',
                        'title' => '',
                        'subject' => 'Apply Profile As Clinic ' . $user->name,
                        'content' => 'I would like to request approval for my profile as a clinic. I have uploaded all the details and documents.<br>
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
}
