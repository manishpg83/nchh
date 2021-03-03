<?php

namespace App\Http\Controllers\Account;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\HealthFeedCategory;
use App\Notification;
use DataTables;
use App\User;
use App\HealthFeed;
use App\Jobs\HealthFeedVerificationJob;
use Exception;
use Image;
use Auth;
use DB;


class HealthFeedController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission');
        $this->random = Str::random(12);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Health Feed');
        $data = [
            'title' => 'Health Feed',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $healthfeed = HealthFeed::where('user_id', [Auth::id()])->orderBy('id', 'DESC')->get();
            return Datatables::of($healthfeed)
                ->addColumn('title', function ($data) {
                    return $data->short_title;
                })->addColumn('category', function ($data) {
                    return $data->category->title;
                })->addColumn('image', function ($data) {
                    return '<img src=" ' . $data->cover_photo . '" class="rounded" style="width:100px"/>';
                })->addColumn('status', function ($data) {
                    $btn = getStatus($data->status, $data->feedback_message);
                    return $btn;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="View HealthFeed" onclick="viewHealthFeed(' . $row->id . ');"><i class="far fa-eye"></i></a>
                <a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit HealthFeed" onclick="editHealthFeed(' . $row->id . ');"><i class="far fa-edit"></i></a>
                <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete HealthFeed" onclick="deleteHealthFeed(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['title', 'category', 'image', 'status', 'action'])
                ->make(true);
        }
        return view('account.healthfeed.index')->with($data);
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
        $html = view('account.healthfeed.create', $data)->render();
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
                $healthfeed = HealthFeed::create($input);

                //get super admin id
                $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                    $q->where('keyword', 'admin');
                })->first();

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => isset($is_admin->id) ? $is_admin->id : '',
                    'title' => 'Request For Health Feed Approval',
                    'type' => 'health_feed',
                    'message' => Auth::user()->name . ' has posted a health feed '.$healthfeed->title.' relevant to '.$healthfeed->category->title.'. please review and approve it.'
                ];

                Notification::create($data);

                if ($is_admin->email) {
                    $mailInfo = ([
                        'recipient_email' => $is_admin->email,
                        'recipient_name' => 'NC Health HUB',
                        'title' => 'Request For Health Feed Approval',
                        'subject' => 'Request For The Approval Of The Health Feed',
                        'content' => 'I’m writing to request approval for my new health feed.I have post health feed '.$healthfeed->title.' relevant to '.$healthfeed->category->title.'.'
                    ]);
                    dispatch(new HealthFeedVerificationJob($mailInfo)); //add mail to queue
                }

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
        $data['title'] = 'View HealthFeed';
        $data['healthfeed'] = HealthFeed::find($id);
        $html = view('account.healthfeed.show', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load healthfeed data.', 'html' => $html];
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
        $html = view('account.healthfeed.update', $data)->render();
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

                $input['status'] = 0;
                $input['feedback_message'] = null;

                $healthfeed->update($input);

                //get super admin id
                $is_admin =  User::select('id', 'email')->whereHas('role', function ($q) {
                    $q->where('keyword', 'admin');
                })->first();

                $data = [
                    'sender_id' => Auth::id(),
                    'receiver_id' => isset($is_admin->id) ? $is_admin->id : '',
                    'title' => 'Request For Health Feed Approval',
                    'type' => 'health_feed',
                    'message' => Auth::user()->name . ' has been uploaded updated Healthfeed. please check details and give response.'
                ];

                Notification::create($data);

                if ($is_admin->email) {
                    $mailInfo = ([
                        'recipient_email' => $is_admin->email,
                        'recipient_name' => 'NC Health HUB',
                        'title' => 'Request For Health Feed Approval',
                        'subject' => 'Request For The Approval Of The Health Feed',
                        'content' => 'I’m writing to request approval for my updated health feed.I have post health feed '.$healthfeed->title.' relevant to '.$healthfeed->category->title.'.'
                    ]);
                    dispatch(new HealthFeedVerificationJob($mailInfo)); //add mail to queue
                }

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
}
