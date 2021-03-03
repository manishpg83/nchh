<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Notification;
use App\User;
use App\UserApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class GlobalController extends Controller
{
    public function verifiedOTP(Request $request)
    {
        $data = Session::get('OTP');
        if ($data['MOBILE_OTP'] == $request->get('otp')) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function verifyDetailForLogin(Request $request)
    {
        $user = new User();
        if ($request->get('phone')) {
            $user = $user->where('phone', $request->get('phone'));
        } elseif ($request->get('email')) {
            $user = $user->where('email', $request->get('email'))->orWhere('phone', $request->get('email'));
        }
        if ($user->exists()) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function sendChatNotification(Request $request, Notification $notification)
    {
        //send notification to doctor
        $androidToken = UserApp::where('user_id', $request->receiver_id)->where('device_type', 'Android')->orderBy('updated_at', 'DESC')->pluck('token')->first();

        if (!empty($androidToken)) {
            $subject = $request->title;
            $sms_push_text = $request->text;
            $extra = ['id' => $request->sender_id, 'type' => $request->type];
            $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
        }
    }
}
