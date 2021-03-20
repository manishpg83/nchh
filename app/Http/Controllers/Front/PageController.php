<?php

namespace App\Http\Controllers\Front;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\Specialty;
use App\HealthFeed;
use App\Jobs\SendInquiryJob;
use App\PracticeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\UserInquiry;

class PageController extends BaseController
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong. Please try again.";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        $data['specialist'] = Specialty::select('id', 'title', 'image')->inRandomOrder()->limit(5)->get();
        $data['healthfeeds'] = HealthFeed::where('status', 1)->orderBy('id', 'desc')->inRandomOrder()->limit(2)->get();
        return view("home")->with($data);
    }

    public function doctorProfile(Request $request, $id, $name)
    {
        $user = User::find($id);
        if (!$user) {
            return abort(404);
        }
        $this->_setPageTitle($user->name . ' | NC Health Hub');
        $data['profile'] = $user;
        $data['user'] = Auth::user();
        return view("front.pages.doctor_profile")->with($data);
    }

    public function clinicProfile(Request $request, $id, $name)
    {

        $user = User::whereHas('practice', function ($practice) {
            $practice->where('staff', '1');
        });

        p($user->get()->toArray());

        $user = User::find($id);
        if (!$user) {
            return view('errors.404');
        }
        $this->_setPageTitle($user->name . ' | NC Health Hub');
        $data['profile'] = $user;
        $data['doctors'] = User::whereHas('practice', function ($practice) {
            $practice->where('staff', '1');
        });
        $data['user'] = Auth::user();

        return view("front.pages.clinic_profile")->with($data);
    }

    public function viewProfile(Request $request, $type, $id, $name)
    {
        $user = User::find($id);
        if (!$user) {
            return abort(404);
        }
        $this->_setPageTitle($user->name . ' | NC Health Hub');
        $data['profile'] = $user;
        $data['practice'] = PracticeManager::with('doctor')->where('added_by', $id)->groupBy('doctor_id')->get();
        $data['user'] = Auth::user();
        $data['specialities'] = $user->detail->specialty_name;
        $data['services'] = $user->detail->services_list_name;

        if ($user->role->keyword == 'diagnostics') {
            return view("front.pages.diagnostics_profile")->with($data);
        } else {
            return view("front.pages.clinic_profile")->with($data);
        }
    }

    public function userInquiry(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $record = [
                    'subject' => $request->get('subject'),
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'content' => $request->get('message'),
                ];

                /* get admin email */
                $record['admin_email'] = User::whereHas('role', function ($a) {
                    $a->where('keyword', 'admin');
                })->pluck('email')->first();

                dispatch(new SendInquiryJob($record));

                $result = ['status' => $this->success, 'message' => 'Mail Send Successful.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
            return Response::json($result);
        }
    }

    public function showThankyou()
    {
        return view('front.pages.thankyou');
    }

    public function terms($type)
    {
        $data['type'] = $type;
        return view('front.pages.terms')->with($data);
    }
}
