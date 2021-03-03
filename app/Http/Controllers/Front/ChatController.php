<?php

namespace App\Http\Controllers\Front;

use App\Appointment;
use App\User;
use App\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use View;

class ChatController extends BaseController
{
    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";

    public function index()
    {
        $this->_setPageTitle('Chat');

        if (checkPermission(['doctor'])) {
            $userids = Appointment::whereIn('status', ['attempt', 'completed'])->where('doctor_id', Auth::id())->pluck('patient_id')->toArray();
        }

        if (checkPermission(['patient'])) {
            $userids = Appointment::whereIn('status', ['attempt', 'completed'])->where('patient_id', Auth::id())->pluck('doctor_id')->toArray();
        }

        $data['users'] = User::whereIn('id', $userids)->where('id', '!=', Auth::id())->orderBy('name', 'ASC')->get();
        $data['authUser'] = Auth::user();
        return view('front.chat.index')->with($data);
    }

    public function openChat($id)
    {
        $receptorUser = User::find($id);
        if ($receptorUser == null) {
            return abort(403);
        } else {

            $data['receptorUser'] = $receptorUser;
            $data['chat'] = $this->hasChatWith($receptorUser->id);
            return view('front.chat.index')->with($data);
        }
    }

    public function openPrivateChat($id)
    {

        $receptorUser = User::find($id);

        $currentTime = Carbon::now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');

        $appointment = Appointment::Where(function ($query) use ($id) {
            $query->where('patient_id', Auth::id())->where('doctor_id', $id)
                ->orWhere('patient_id', $id)->where('doctor_id', Auth::id());
        })->where('date', today())
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->WhereNotIn('status', ['pending', 'cancelled'])
            ->first();

        $data = ['is_appointment_available' => 1, 'totalSecond' => 20, 'totalMinute' => 20];

        if ($receptorUser == null || $receptorUser->id == Auth::id()) {
            return abort(403);
        } else {

            $data['receptorUser'] = $receptorUser;
            $data['chat'] = $this->hasChatWith($receptorUser->id);
            $data['call_disconnect'] = View::make('front.chat.call_disconnect')->render();

            return view('front.chat.private')->with($data);
        }
    }

    public function openChatPrivateWindow($id)
    {
        $receptorUser = User::find($id);
        if ($receptorUser == null) {

            $this->status = 401;
            $result = ["status" => $this->error, "message" => "User doesn't exist."];
        } else {
            $data['receptorUser'] = $receptorUser;
            $data['chat'] = $this->hasChatWith($receptorUser->id);
            $data['html'] = view('front.chat.window')->with($data)->render();
            $result = ["status" => $this->success, "message" => "Record added.", 'result' => $data];
        }

        return Response::json($result, $this->status);
    }

    public function openChatHistoryWindow($id)
    {
        $receptorUser = User::find($id);
        if ($receptorUser == null) {

            $this->status = 401;
            $result = ["status" => $this->error, "message" => "User doesn't exist."];
        } else {
            $data['receptorUser'] = $receptorUser;
            $data['chat'] = $this->hasChatWith($receptorUser->id);
            $data['html'] = view('front.chat.history_window')->with($data)->render();
            $result = ["status" => $this->success, "message" => "Record added.", 'result' => $data];
        }

        return Response::json($result, $this->status);
    }

    public function openVideoWindow($id, $active = false)
    {
        $receptorUser = User::find($id);
        if ($receptorUser == null) {
            return abort(403);
        } else {
            $data['receptorUser'] = $receptorUser;
            $data['call_active'] = $active;
            $data['chat'] = $this->hasChatWith($receptorUser->id);
            return view('front.chat.video_window')->with($data);
        }
    }

    public function openVideoChatBox($id)
    {
        $receptorUser = User::find($id);
        if ($receptorUser == null || $receptorUser->id == Auth::id()) {
            $this->status = 401;
            $result = ["status" => $this->error, "message" => "User doesn't exist."];
        } else {

            $data = ['is_appointment_available' => 1, 'totalSecond' => 20, 'totalMinute' => 20];
            $data['receptorUser'] = $receptorUser;
            $data['chat'] = $this->hasChatWith($receptorUser->id);
            $data['html'] = view('front.chat.video.modal')->with($data)->render();
            $result = ["status" => $this->success, "message" => "Record added.", 'result' => $data];
        }
        return Response::json($result, $this->status);
    }

    public function hasChatWith($recipient_id)
    {
        $chat = Chat::where('sender_id', Auth::id())->where('recipient_id', $recipient_id)
            ->orWhere('sender_id', $recipient_id)->where('recipient_id', Auth::id())
            ->get();
        if (!$chat->isEmpty()) {
            return $chat->first();
        } else {
            return $this->createChat(Auth::id(), $recipient_id);;
        }
    }

    public function createChat($sender_id, $recipient_id)
    {
        $chat = Chat::create([
            'sender_id' => $sender_id,
            'recipient_id' => $recipient_id
        ]);
        return $chat;
    }
}
