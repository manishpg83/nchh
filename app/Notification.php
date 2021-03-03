<?php

namespace App;

use App\User;
use App\NotificationKeyword;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Notifications\ResetPassword;

class Notification extends Model
{
	public $success = 200;
	public $error = 400;
	public $exception_message = "Something went worng";

	protected $table = "notifications";

	protected $fillable = ['sender_id', 'receiver_id', 'action_id', 'title', 'type', 'message', 'is_read'];

	public $timestamps = false;

	// public $timestamps = ['created_at', 'deleted_at'];


	public function getcreatedDateAttribute($value)
	{
		$this->timestamps = true;
		return $this->created_at->diffForHumans();
	}

	public function sender()
	{
		return $this->belongsTo('App\User', 'sender_id');
	}

	public function receiver()
	{
		return $this->belongsTo('App\User', 'receiver_id');
	}

	public function sendPushNotification($type, $token, $title, $text, $extraData = [])
	{

		$extra = ['title' => $title, 'text' => $text];
		if (!empty($extraData)) {
			$extra += $extraData;
		}

		if ($type == 'android') {
			$push = new PushNotification('fcm');
			$push->setMessage([
				'notification' => [
					'title' => $title,
					'body' => $text,
					'sound' => 'default',
					'click_action' => isset($extra['type']) ? $extra['type'] : ''
				],
				'data' => $extra
			])->setDevicesToken($token)->send();
			$a = $push->getFeedback();
			 
		}
	}
}
