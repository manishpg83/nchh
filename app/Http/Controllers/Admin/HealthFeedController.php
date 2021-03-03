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
use Illuminate\Support\Str;
use App\Specialty;
use DataTables;
use App\User;
use App\HealthFeed;
use App\Notification;
use App\HealthFeedCategory;
use App\Jobs\HealthFeedVerificationResponseJob;
use App\UserApp;
use Exception;
use Auth;

class HealthFeedController extends BaseController
{
    protected $random;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";

    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->random = Str::random(12);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Health Feed Manager');
        $data = ['title' => 'Health Feed Manager', 'user' => Auth::user()];
        if ($request->ajax()) {
            $healthfeeds = HealthFeed::orderBy('id', 'DESC')->get();
            return Datatables::of($healthfeeds)
                ->addColumn('title', function ($data) {
                    return '' . $data->title . '<br><span class="badge badge-pill badge-info" data-toggle="tooltip" data-placement="top" title="Health Feed category">' . $data->category->title . '</span>';
                })->addColumn('image', function ($data) {
                    return '<img src=" ' . $data->cover_photo . '" class="rounded" style="width:100px"/>';
                })->addColumn('status', function ($data) {
                    $btn = getStatus($data->status, $data->feedback_message);
                    return $btn;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="View healthfeed" onclick="viewHealthFeed(' . $row->id . ');"><i class="far fa-eye"></i></a>';
                    return $btn;
                })
                ->rawColumns(['title', 'image', 'status', 'action'])
                ->make(true);
        }
        return view('admin.healthfeed.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data['title'] = 'View Health Feed';
        $data['healthfeed'] = HealthFeed::find($id);
        $html = view('admin.healthfeed.show', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load health feed data.', 'html' => $html];
        return Response::json($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /* view healthfeed reauest  */
    public function getHealthFeedVerification(Request $request)
    {
        $this->_setPageTitle('Health Feed Manager');
        $data = ['title' => 'Health Feed Manager', 'user' => Auth::user()];
        if ($request->ajax()) {
            $healthfeeds = HealthFeed::where('status', 0)->orderBy('id', 'desc')->get();

            return Datatables::of($healthfeeds)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group " role="group">
                <button type="button" onclick="requestHealthFeed(this);" id="' . $row->id . '" value="1" class="btn btn-mat btn-success mr-2">Accept</button>
                <button type="button" onclick="rejectHealthFeed(' . $row->id . ');" id="' . $row->id . '" value="2" class="btn btn-mat btn-danger">Reject</button>
                </div>';
                    return $btn;
                })
                ->addColumn('image', function ($data) {
                    return '<img src="' . $data->cover_photo . '" class="rounded" style="width:100px"/>';
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }

        Notification::where('receiver_id', Auth::id())->where('type', 'health_feed')->update(['is_read' => '1']);

        return view("admin.healthfeed.request")->with($data);
    }

    /* change healthfeed status*/
    public function changeStatus(Request $request, Notification $notification)
    {
        $rules = [
            'id' => 'required|exists:health_feeds,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                $healthfeed = HealthFeed::find($request->id);
                $healthfeed->update($input);

                if ($request->get('status') == '1') {
                    $title = 'Your Feed ' . $healthfeed->title . ' has been approved';
                    $type = 'health_feed_approved';
                    $notification_title = 'Health Feed Approved';
                    $content = 'Your health feed ' . $healthfeed->title . ' has been approved. you can find under the health feed menu inside your account panel.';
                } else {
                    $title = 'Your feed ' . $healthfeed->title . ' has been rejected';
                    $type = 'health_feed_rejected';
                    $notification_title = 'Health Feed rejected';
                    $content = 'Your health feed ' . $healthfeed->title . ' has been rejected. you can find under the health feed menu inside your account panel. <br>'.$request->get('feedback_message');
                }

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => $healthfeed->user_id,
                    'title' => $title,
                    'type' => $type,
                    'message' => $content,
                ];
                Notification::create($data);

                if ($healthfeed->user->email) {
                    $mailInfo = ([
                        'recipient_email' => $healthfeed->user->email,
                        'recipient_name' => $healthfeed->user->name,
                        'title' => '',
                        'subject' => $title,
                        'content' => $content,
                    ]);
                    dispatch(new HealthFeedVerificationResponseJob($mailInfo)); //add mail to queue
                }

                /* start notification*/
                //send notification to app 
                $androidToken = UserApp::where('user_id', $healthfeed->user->id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

                if (!empty($androidToken)) {
                    $subject = $notification_title;
                    $extra = ['id' => $healthfeed->user->id, 'type' => $type];
                    $sms_push_text = $title;
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

    public function reject($id)
    {
        $data['title'] = 'Reject healthfeed with message';
        $data['healthfeed'] = HealthFeed::find($id);
        $html = view('admin.healthfeed.reject', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load healthfeed data.', 'html' => $html];
        return Response::json($result);
    }
}
