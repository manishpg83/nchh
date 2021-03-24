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
use App\Jobs\HealthFeedVerificationJob;
use App\Jobs\HealthFeedVerificationResponseJob;
use App\UserApp;
use Image;
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
                    return '' . $data->title . '<br><span class="badge badge-pill badge-info" data-toggle="tooltip" data-placement="top" title="Health Feed category">' . (($data->category->title == 'Other') ? $data->other_category : $data->category->title) . '</span>';
                })->addColumn('image', function ($data) {
                    return '<img src=" ' . $data->cover_photo . '" class="rounded" style="width:100px"/>';
                })->addColumn('status', function ($data) {
                    $btn = getStatus($data->status, $data->feedback_message);
                    return $btn;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="View HealthFeed" onclick="viewHealthFeed(' . $row->id . ');"><i class="far fa-eye"></i></a>';

                    if($row->user_id == Auth::id()) {
                        $btn .= '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit HealthFeed" onclick="editHealthFeed(' . $row->id . ');"><i class="far fa-edit"></i></a>';
                        $btn .= '<a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete HealthFeed" onclick="deleteHealthFeed(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    }
                    
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
        $data = ['title' => 'Add HealthFeed'];
        $data['healthfeed_category'] = HealthFeedCategory::pluck('title', 'id')->toArray();
        $html = view('admin.healthfeed.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load healthfeed data.', 'html' => $html];

        return Response::json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'category_ids' => 'required',
            'title' => 'required',
            'content' => 'required',
            'cover_photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                if ($request->hasFile('cover_photo')) {
                    $avatar = $request->file('cover_photo');
                    if ($avatar->getClientOriginalExtension() == 'jfif') {
                        $filename = time() . $this->random . '.jpg';
                        Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/healthfeed/' . $filename));
                        $input['cover_photo'] = $filename;
                    } else {
                        $filename = time() . $this->random . '.' . $avatar->getClientOriginalExtension();
                        Image::make($avatar)->save(storage_path('app/healthfeed/' . $filename));
                        $input['cover_photo'] = $filename;
                    }
                }
                $input['user_id'] = Auth::id();
                $input['status'] = 1;
                $healthfeed = HealthFeed::create($input);


                $result = ['status' => $this->success, 'message' => 'Health Feed Insert Successful..'];
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
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
        $data['title'] = 'Edit healthfeed';
        $data['healthfeed'] = HealthFeed::find($id);
        $data['healthfeed_category'] = HealthFeedCategory::pluck('title', 'id')->toArray();
        $html = view('admin.healthfeed.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load healthfeed data.', 'html' => $html];
        return Response::json($result);
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
        $rules = [
            'category_ids' => 'required',
            'title' => 'required',
            'content' => 'required',
            'cover_photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                $healthfeed = HealthFeed::find($id);

                if ($request->hasFile('cover_photo')) {
                    $avatar = $request->file('cover_photo');
                    if ($avatar->getClientOriginalExtension() == 'jfif') {
                        $filename = time() . $this->random . '.jpg';
                        Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/healthfeed/' . $filename));
                        $input['cover_photo'] = $filename;
                    } else {
                        $filename = time() . $this->random . '.' . $avatar->getClientOriginalExtension();
                        Image::make($avatar)->save(storage_path('app/healthfeed/' . $filename));
                        $input['cover_photo'] = $filename;
                    }

                    /*remove the existing profile picture*/
                    $image_path = storage_path('app/healthfeed/' . $healthfeed->cover_photo_name);
                    if ($healthfeed->cover_photo_name != "default.png") {
                        @unlink($image_path);
                    }
                }

                $input['status'] = 1;
                $input['feedback_message'] = null;

                $healthfeed->update($input);

                $result = ['status' => $this->success, 'message' => 'Update Successful.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
            return Response::json($result);
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
        try {
            $healthfeed = HealthFeed::find($id);
            $image_path = storage_path('app/healthfeed/' . $healthfeed->cover_photo_name);
            if ($healthfeed->cover_photo_name != "default.png") {
                @unlink($image_path);
            }

            $healthfeed->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
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
                <a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="View HealthFeed" onclick="viewHealthFeed(' . $row->id . ');"><i class="far fa-eye"></i></a>&nbsp;&nbsp;&nbsp;
                <button type="button" onclick="requestHealthFeed(this);" id="' . $row->id . '" value="1" class="btn btn-mat btn-success mr-2">Accept</button>
                <button type="button" onclick="rejectHealthFeed(' . $row->id . ');" id="' . $row->id . '" value="2" class="btn btn-mat btn-danger">Reject</button></div>
                ';
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
