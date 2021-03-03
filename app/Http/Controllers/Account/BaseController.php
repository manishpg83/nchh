<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class BaseController extends Controller
{
    protected function _setPageTitle($title)
    {
        view()->share([
            'pageTitle' => $title,
        ]);
    }

    public function removePicture($id = null)
    {
        if (!$id) {
            $id = Auth::id();
        }
        $user = User::find($id);
        $image_path = storage_path('app/user/' . $user->image_name);
        if ($user->image_name != "default.png") {
            @unlink($image_path);
        }
        $user->profile_picture = 'default.png';
        $user->save();
        $result = ['status' => 200, 'message' => "Remove profile picture successfully.", "result" => $user];
        return Response::json($result);
    }
}
