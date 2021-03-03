<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Jobs\SendRefferalInvite;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AgentController extends BaseController
{
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission')->except(['inviteForm','sendInvitation']);
    }

    public function referralUser(Request $request)
    {
        $this->_setPageTitle('Referral Users');

        if ($request->ajax()) {
            try {

                $user = new User();
                $user = $user->where(['referrer_id' => Auth::id()])->orderBy('id', 'DESC');

                $datatable = DataTables::of($user->get());

                $datatable = $datatable->addColumn('name', function ($q) {
                    return '<a href="' . route('account.user.detail.show', [$q->id]) . '">' . $q->name . '</a>';
                });
                $datatable = $datatable->addColumn('email', function ($q) {
                    return $q->email ? $q->email : '<span class="badge badge-light">No mentioned</span>';
                });
                $datatable = $datatable->addColumn('phone', function ($q) {
                    return $q->phone_with_dialcode;
                });
                $datatable = $datatable->addColumn('locality', function ($q) {
                    return $q->locality;
                });
                $datatable = $datatable->addColumn('address', function ($q) {
                    return '<span class="ws-break-spaces">' . $q->address . '</span>';
                });
                $datatable = $datatable->addColumn('register_at', function ($q) {
                    $timezone = $q->timezone ? $q->timezone : 'Asia/Kolkata';
                    return Carbon::parse($q->created_at)->setTimezone($timezone)->format('Y-m-d H:i A');
                });

                $datatable = $datatable->addColumn('commission', function ($q) {
                    return '₹ 0';
                });

                $datatable = $datatable->addColumn('action', function ($q) {
                    $button = '';
                    return $button;
                });

                $datatable = $datatable->rawColumns(['name', 'email', 'phone', 'locality', 'address', 'action']);

                $datatable = $datatable->make(true);
            } catch (Exception $e) {
                $datatable = DataTables::of(User::select()->take(0)->get());
                $datatable = $datatable->make(true);
            }
            return $datatable;
        }

        return view('account.invite_manager.referral_user');
    }

    public function inviteForm(Request $request)
    {
        $this->_setPageTitle('Send Invitation');
        return view('account.invite_manager.invite');
    }

    public function sendInvitation(Request $request)
    {
        $rules = [
            'subject' => 'required',
            'recipient_email' => 'required',
            'recipient_phone' => 'required',
            'content' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                $input['sender_user'] = Auth::user()->name;
                $input['referral_link'] = Auth::user()->referral_link;
                $input['referral_code'] = Auth::user()->referral_code;
                
                dispatch(new SendRefferalInvite($input));

                $result = ['status' => $this->success, 'message' => 'Send Referral Invitation Successfully.', 'data' => $input];
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }

        return Response::json($result);
    } 
    
}
