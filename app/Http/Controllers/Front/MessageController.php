<?php

namespace App\Http\Controllers\Front;

use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Exception;



class MessageController extends Controller
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // count how many message are unread from the selected user
        $users = DB::select("select users.id, users.name, users.email, users.profile_picture, users.phone, count(is_read) as unread 
        from users LEFT  JOIN  messages ON users.id = messages.from and is_read = 0 and messages.to = " . Auth::id() . "
        where users.id != " . Auth::id() . " 
        group by users.id, users.name, users.email, users.profile_picture, users.phone");

        return view('front.message.index', ['users' => $users]);
    }

    public function show($id)
    {
        $my_id = Auth::id();
        $user_id = $id;
        // Make read all unread message
        Message::where([
            ['from', '=', $user_id],
            ['to', '=', $my_id],
        ])->update(['is_read' => 1]);

        // Get all message from selected user
        $data['messages'] = Message::where([
            ['from', '=', $user_id],
            ['to', '=', $my_id],
        ])->orWhere([
            ['to', '=', $user_id],
            ['from', '=', $my_id],
        ])->get();
        $data['receiver_user'] = User::find($id);
        return view('front.message.show')->with($data);
    }


    public function store(Request $request)
    {
        $rules = [
            'message' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                $input['from'] = Auth::id();
                $input['is_read'] = 0;
                $message = Message::create($input);
                $text['message'] = $request->get('message');
                $text['sender'] = Auth::id();
                $text['receiver'] = $request->get('to');
                $text['date'] = date('d M y, h:i a', strtotime($message->created_at));

                $result = ['status' => $this->success, 'message' => 'message Insert Successful..', 'text' => $text];
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
    }

    public function getMessage($id)
    {
        $my_id = Auth::id();
        $user_id = $id;
        // Make read all unread message
        Message::where([
            ['from', '=', $user_id],
            ['to', '=', $my_id],
        ])->update(['is_read' => 1]);

        // Get all message from selected user
        $data['messages'] = Message::where([
            ['from', '=', $user_id],
            ['to', '=', $my_id],
        ])->orWhere([
            ['to', '=', $user_id],
            ['from', '=', $my_id],
        ])->get();
        $data['receiver_user'] = User::find($id);
        return view('front.message.chat_screen')->with($data);
    }
}
