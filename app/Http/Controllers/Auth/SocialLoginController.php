<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use App\OtherDetail;

class SocialLoginController extends Controller
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userInfo = $request->get('data');
        if($request->get('type') == 'google'){
            
            $input = [
                'name' => $userInfo['Bd'],
                'email' => $userInfo['Au'],
                'social_id' => $userInfo['JU'],
                'register_type' => $request->get('type')
             ];
        }

        if($request->get('type') == 'facebook'){
        
            $input = [
                'name' => $userInfo['name'],
                'email' => $userInfo['email'],
                'social_id' => $userInfo['id'],
                'register_type' => $request->get('type')
             ];
        }
            $rules = [
                'name' => 'required',
                'email' => 'required',
                'social_id' => 'required',
                'register_type' => 'required',
            ];

            $validator = Validator::make($input,$rules);
            if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator->errors(), "result" => []];
            } else {
                try {
                    DB::beginTransaction();
                        $user = User::updateOrCreate($input);
                        OtherDetail::updateOrCreate(['user_id' => $user->id],$input);
                        Auth::login($user);
                    DB::commit();
                    $result = ['status' => $this->success, 'message' => "You are register successfully.", 'redirect' => '/home'];
                } catch (Exception $e) {
                    DB::rollBack();
                    $result = ['status' => $this->error, 'message' => $this->exception_message];
                }
            }
            return Response::json($result);
    }
}
