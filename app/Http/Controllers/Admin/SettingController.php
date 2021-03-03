<?php

namespace App\Http\Controllers\Admin;

use App\Commission;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Exception;


class SettingController extends BaseController
{
    protected $random;
    public $status = 200;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
         //
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

        return view('admin.setting.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function commission()
    {
        $this->_setPageTitle('Commission Setting');
        $patient_agent = isset(Auth::user()->commission->patient_agent) ? Auth::user()->commission->patient_agent: 0 ;
        $other_agent = isset(Auth::user()->commission->patient_agent) ? Auth::user()->commission->patient_agent: 0 ;
        $data = [
            'title' => 'Commission Setting',
            'user' => Auth::user(),
            'case_1' => 100 - ($patient_agent + $other_agent),
            'case_2' => 100 - $other_agent,
            'case_3' => 100 - $patient_agent,
        ];

        return view('admin.setting.commission')->with($data);
    } 

    public function storeCommission(Request $request){
        $input = $request->all();
        Commission::updateOrCreate(['user_id' => Auth::id()], $input);

        return back()->with('success', 'Commission Setting Changed.');
    }
}
