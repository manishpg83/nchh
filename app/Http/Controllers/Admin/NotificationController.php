<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect; 
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use App\Notification;
use Carbon\Carbon;
use DataTables;
use App\User;
use Auth;
use DB;

class NotificationController extends BaseController
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        
        $this->_setPageTitle('Notification Manager');
        $data = ['title' => 'Notification Manager','user' => Auth::user()];
        if($request->ajax()) {
            $notifications = Notification::where('receiver_id' , Auth::id())
            ->orderBy('id','DESC')->get();
            
            return Datatables::of($notifications)
            ->addColumn('user', function ($data){
                return $data->sender->name;
            })->addColumn('title', function ($data) {
                return $data->title;
            })->addColumn('type', function ($data) {
                return $data->type;
            })->addColumn('action', function($data){
                $btn = '<button type="button"  id="'.$data->id.'" class="btn btn-mat btn-success btn-sm">Preview</button>';
                return $btn;
            })->rawColumns(['user','title','type','action'])
            ->make(true);
        }
        
        return view('admin.notification.index')->with($data);
    }
}