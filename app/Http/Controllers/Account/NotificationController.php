<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Notification;

class NotificationController extends BaseController
{
    protected $random;
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission');
     }

    public function index(Request $request)
    { 
        $this->_setPageTitle('Notification');
        $data = [
            'title' => 'Notification',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $notification = Notification::where('receiver_id', Auth::id())->orderBy('id', 'DESC')->get();
            return Datatables::of($notification)
                ->addColumn('sender_by', function ($data) {
                    return $data->sender->name;
                })->addColumn('title', function ($data) {
                    return isset($data->title)?'<p class="p-0 m-0 d-inline-flax ws-break-spaces">'.$data->title.'</p>': '-';
                })->addColumn('message', function ($data) {
                    return isset($data->message)?'<p class="p-0 m-0 d-inline-flax ws-break-spaces">'.$data->message.'</p>': '-';
                })->addColumn('action', function ($data) {
                    if ($data->type == 'add_staff') {
                        $btn = '<div class="buttons">
                        <a href="javascript:;" class="btn btn-icon btn-success" onclick="staffInvitationReply(' . $data->action_id . ',1,' .$data->id. ')" data-toggle="tooltip" data-placement="top" title="Accept"><i class="fas fa-check"></i></a>
                        <a href="javascript:;" class="btn btn-icon btn-danger" onclick="staffInvitationReply(' . $data->action_id . ',0,' .$data->id. ')" data-toggle="tooltip" data-placement="top" title="Reject"><i class="fas fa-times"></i></a>
                       </div>';
                    } else {
                        $btn = '-';
                    }

                    return $btn;
                })
                ->rawColumns(['sender_by', 'message', 'action','title'])
                ->make(true);
        }
        Notification::where('receiver_id', Auth::id())->update(['is_read' => '1']);
        return view('account.notification.index')->with($data);
    }
}
