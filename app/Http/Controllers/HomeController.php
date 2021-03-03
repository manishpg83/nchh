<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Auth;
use App\User;
use App\UserRole;
use App\Country;
use App\State;
use App\City;
use App\Specialty;
use App\HealthFeed;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;
use Stevebauman\Location\Facades\Location;

use DataTables;
use DB;


class HomeController extends Controller
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
    }
    
    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function index()
    {
        $data['specialist'] = Specialty::select('title', 'id')->inRandomOrder()->limit(5)->get();
        $data['healthfeeds'] = HealthFeed::where('status', 1)->orderBy('id', 'desc')->take(2)->get();
        return view("home")->with($data);
    }
}