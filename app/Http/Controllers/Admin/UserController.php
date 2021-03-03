<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator; 
use App\Specialty;
use App\Notification;
use App\UserDetail;
use Carbon\Carbon;
use App\UserRole;
use Timezone;
use App\Country;
use App\Service;
use Yajra\DataTables\DataTables;
use App\State;
use App\User;
use App\City; 
use Auth;
use DB;
use App\Setting;

class UserController extends BaseController
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
        $this->_setPageTitle('User Manager');
        $roles = UserRole::whereIn('keyword',['patient','doctor','clinic','hospital','pharmacy','diagnostics'])->pluck('name','id')->toArray();
        $data = ['title' => 'User Manager', 'user' => Auth::user(), 'roles' => $roles];
        if ($request->ajax()) {
            $users = new User();
            if($request->get('role') && $request->get('role') !== "all"){
                $users = $users->whereHas('role',function($q) use($request){
                    $q->where('id',$request->get('role'));
                });
            }
            $users = $users->orderBy('id','DESC')->get()->except(Auth::id());
            return Datatables::of($users)
            ->addColumn('name', function ($data) {
                if ($data->name != null) {
                    $btn = '<img src=" ' . $data->profile_picture . '" class="rounded rounded-circle" style="width: 35px;min-height: 35px;"/> ' . $data->name . '';
                } else {
                    $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                }
                return $btn;
            })->addColumn('role', function ($data) {
                //$btn = getRoles($data->role_id);
                return $data->role->name;
            })->addColumn('phone', function ($data) {
                return '+' . $data->dialcode . '' . $data->phone . '';
            })->addColumn('email', function ($data) {
                return isset($data->email) ? $data->email : '<span class="badge badge-pill badge-info">Not Mentioned</span>';
            })->addColumn('action', function ($row) {
                $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="View User" onclick="viewUser(' . $row->id . ');"><i class="far fa-eye"></i></a>';
                /* <a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit User" onclick="editUser(' . $row->id . ');"><i class="far fa-edit"></i></a>
                <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete User" onclick="deleteUser(' . $row->id . ');"><i class="far fa-trash-alt"></i></a> */
                return $btn;
            })
            ->rawColumns(['name', 'role', 'email', 'phone', 'email', 'action'])
            ->make(true);
        }
        
        Notification::where('receiver_id', Auth::id())->where('type', 'user_register')->update(['is_read' => '1']);
        
        return view('admin.user.index')->with($data);
    }
    
    public function show($id)
    {
        $data['user'] = User::find($id);
        $html = view('admin.user.view', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load user data.', 'html' => $html];
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
        $user = User::find($id);
        
        $selected = $user->timezone;
        $placeholder = 'Select a timezone';
        $formAttributes = array('class' => 'form-control select2', 'name' => 'timezone');
        
        $data['doctors'] = User::whereHas('role', function ($q) {
            $q->whereIn('keyword', ['doctor']);
        })->pluck('name', 'id')->toArray();
        $data['specialty'] = Specialty::pluck('title', 'id')->toArray();
        $data['services'] = service::pluck('name', 'id')->toArray();
        $data['country'] = Country::pluck('name', 'id')->toArray();
        $data['timezonelist'] =  Timezone::selectForm($selected, $placeholder, $formAttributes);
        $data['d_category'] = Specialty::pluck('title', 'id')->toArray();
        $data['user'] = $user;
        $html= view('admin.user.update', $data)->render(); 
        $result = ['status' => $this->success, 'message' => 'load user data.', 'html' => $html]; 
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
        $user = User::find($id);
        $rules = [
            'name' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ['status' => $this->error, 'message' => $validator->errors()];
        } else {
            try {
                $input = $request->all();
                if ($request->file('profile_picture')) {
                    $image_path = storage_path('app/user/' . $user->image_name);
                    if ($user->image_name !== 'default.png') {
                        if (File::exists($image_path)) {
                            File::delete($image_path);
                        }
                    }
                    $file = $request->file('profile_picture');
                    $name = date('dmYHis') . '.' . $file->getClientOriginalExtension();
                    $file->move(storage_path('app/user/'), $name);
                    $input['profile_picture'] = $name;
                }
                
                if($request->get('specialty_ids')){
                    $user_specialties = arrayToString($request->get('specialty_ids'));
                    $input['specialty_ids'] = $user_specialties; 
                }
                
                if($request->get('doctor_id')){
                    $doctor_id = arrayToString($request->get('doctor_id')); 
                    $input['doctor_id'] = $doctor_id;
                }
                
                if ($request->get('services')) {
                    $services = arrayToString($request->get('services'));
                    $input['services'] = $services;
                }
                
                $user->update($input);
                UserDetail::updateOrCreate(['user_id' => $user->id], $input);
                $result = ['status' => $this->success, 'message' => "User data updated Successfully."];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        
        return Response::json($result);
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
            $user = User::find($id);
            $image_path = storage_path('app/user/' . $user->image_name);
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
            $user->delete();
            
            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
    
}