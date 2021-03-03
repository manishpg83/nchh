<?php

namespace App\Http\Controllers\Account;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Image;

use App\Notification;
use App\Setting;
use App\UserDetail;
use App\User;


class SettingController extends BaseController
{
    protected $random;
    public $status = 200;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->random = Str::random(12);
        $this->middleware('checkPermission')->except(['changePassword','setPassword','storeConsultant']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->_setPageTitle('Setting');
        $data = [
            'title' => 'Setting',
            'user' => Auth::user(),
        ];

        return view('account.setting.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function general()
    {
        $this->_setPageTitle('General Setting');
        $data = [
            'title' => 'General Setting',
            'user' => Auth::user(),
        ];

        return view('account.setting.general-setting')->with($data);
    }

    //change password
    public function changePassword(Request $request)
    {
        $rules = [
            'old_password' => "required",
            'password' => "required",
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator->errors()->first()];
        } else {
            try {
                $user = Auth::user();
                if (Hash::check($request->get("old_password"), $user->password)) {
                    $user->password = Hash::make($request->get('password'));
                    $user->save();
                    $result = ["status" => $this->success, "message" => "Password update successfully."];
                } else {
                    $this->status = 401;
                    $result = ["status" => $this->error, "message" => "Current password is invalid."];
                }
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => $this->exception_message];
            }
        }
        return Response::json($result, $this->status);
    }

    //set password
    public function setPassword(Request $request)
    {
        $rules = [
            'password' => "required",
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->status = 401;
            $result = ["status" => $this->error, "message" => $validator->errors()->first()];
        } else {
            try {
                $user = Auth::user();
                $user->password = Hash::make($request->get('password'));
                $user->save();
                $result = ["status" => $this->success, "message" => "Password set successfully."];
            } catch (Exception $e) {
                $this->status = 401;
                $result = ["status" => $this->error, "message" => $this->exception_message];
            }
        }
        return Response::json($result, $this->status);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function consultant()
    {
        $this->_setPageTitle('Consultant Setting');
        $data = [
            'title' => 'Consultant Setting',
            'user' => Auth::user(),
        ];

        return view('account.setting.consultant')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeConsultant(Request $request)
    {

        $input = $request->except(['_token', '_method']);
        $input['user_id'] = Auth::id();
        $rules = [
             'consultant_duration' => 'required|numeric',
            'availability' => 'required|in:0,1',
        ];
        
        if(checkPermission(['doctor'])){
            $rules += [
                'consultant_as' => 'required|in:ONLINE,INPERSON,BOTH',
                'do_service_at_other_establishment' => 'required|in:0,1'
            ];  
        }

        if ($request->get('availability') == 0) {
            $rules += ['unavailability_start_date' => 'required|before:unavailability_end_date', 'unavailability_end_date' => 'required'];
        }

        $request->validate($rules);

        // p($input);
        Setting::updateOrCreate(['user_id' => Auth::id()], $input);

        return back()->with('success', 'Setting Changed.');
    }
}
