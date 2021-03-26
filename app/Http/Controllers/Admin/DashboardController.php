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

class DashboardController extends BaseController
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
    public function index()
    {
        $this->_setPageTitle('Dashboard');
        $data = [
            'title' => 'Dashboard',
            'user' => Auth::user(),
        ]; 
        
        $data['doctors'] = [
            'title' => 'Total Doctors',
            'count' => User::whereHas('role',function($d){
                $d->where('keyword','doctor');
            })->where('as_doctor_verified', '2')->count(),
            'navigation' => route('admin.getDoctor'),
        ];
        $data['doctor_verification_pending'] = [
            'title' => 'Doctor Profile Pending Request',
            'count' => User::whereHas('role',function($d){
                $d->whereIn('keyword',['patient','doctor']);
            })->where('as_doctor_verified', '1')->count(),
            'navigation' => route('admin.doctor.profile.verification'),
        ];
        $data['clinic'] = [
            'title' => 'Total Clinic',
            'count' => User::whereHas('role',function($d){
                $d->where('keyword','clinic');
            })->count(),
            'navigation' => route('admin.getClinic'),
        ];
        $data['hospital'] = [
            'title' => 'Total Hospital',
            'count' => User::whereHas('role',function($d){
                $d->where('keyword','hospital');
            })->count(),
            'navigation' => route('admin.getHospital'),
        ];
        $data['pharmacy'] = [
            'title' => 'Total Pharmacy',
            'count' => User::whereHas('role',function($d){
                $d->where('keyword','pharmacy');
            })->count(),
            'navigation' => route('admin.getPharmacy'),
        ];
        
        $data['diagnostics'] = [
            'title' => 'Total Diagnostics',
            'count' => User::whereHas('role',function($d){
                $d->where('keyword','diagnostics');
            })->where('as_diagnostics_verified', '2')->count(),
            'navigation' => route('admin.getDiagnostics'),
        ];
        
        $data['diagnostics_verification_pending'] = [
            'title' => 'Diagnostics Profile Pending Request',
            'count' => User::whereHas('role',function($d){
                $d->whereIn('keyword',['diagnostics']);
            })->where('as_diagnostics_verified', '1')->count(),
            'navigation' => route('admin.diagnostics.profile.verification'),
        ];
        
        $data['agent'] = [
            'title' => 'Total Agent',
            'count' => User::where('as_agent_verified', '2')->count(), 
            'navigation' => route('admin.getAgent'),
        ];
        
        $data['agent_verification_pending'] = [
            'title' => 'Agent Profile Pending Request',
            'count' => User::where('as_agent_verified', '1')->count(),
            'navigation' => route('admin.agent.profile.verification'),
        ];

        $data['recent_doctors'] = User::whereHas('role',function($d){
            $d->where('keyword','doctor');
        })->where('as_doctor_verified', '2')->orderBy('created_at','DESC')->limit(50)->get();
        return view('admin.dashboard')->with($data);
    }
    
    public function doctorChart(Request $request)
    {
        if ($request->ajax()){
            $months= [];
            $count = [];
            $data['total_doctor_today'] = User::whereHas('role',function($d){
                $d->where('keyword','doctor');
            })->where('as_doctor_verified', '2')->whereDate('created_at', today())->count();
            $data['total_doctor_week'] = User::whereHas('role',function($d){
                $d->where('keyword','doctor');
            })->where('as_doctor_verified', '2')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $data['total_doctor_month'] = User::whereHas('role',function($d){
                $d->where('keyword','doctor');
            })->where('as_doctor_verified', '2')->whereYear('created_at', '=', now()->year)->whereMonth('created_at', now()->month)->count();
            $data['total_doctor_year'] = User::whereHas('role',function($d){
                $d->where('keyword','doctor');
            })->where('as_doctor_verified', '2')->whereYear('created_at', now()->year)->count();
            
            $doctor = User::whereHas('role', function($d){
                $d->where('keyword','doctor');
            })->where('as_doctor_verified', '2')->whereYear('created_at', '=', now()->year)->orderBy('created_at','ASC')->selectRaw('COUNT(*) as count,MONTHNAME(created_at) month')->groupBy('month')->get();
            foreach($doctor as $key =>$value){
                $months[$key]= $value->month;
                $count[$key] = $value->count;
            }
            $doctor_chart = [
                'months' => $months,
                'value' => $count
            ];
            /* 'months' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            'value' => [640, 387, 530, 302, 430, 270, 488, 530, 302, 430, 270, 488] */
            $result = ['status' => $this->success, 'message' => 'Doctor data.','doctor_chart' => $doctor_chart,'data' => $data];
            
            return Response::json($result);
        }
    }
}