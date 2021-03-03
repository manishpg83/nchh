<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\PracticeManager;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use File;

class GlobalController extends Controller
{

    protected $random;
    protected $password;
    protected $status = 200;
    protected $success = "success";
    protected $error = "error";
    protected $paginate_count;
    protected $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->random = str_random(10);
        $this->paginate_count = 9;
    }

    public function sendOtp(Request $request)
    {
        $input = $request->all();
        $input['title'] = ($request->get('field') == "phone") ? 'phone number' : 'email address';
        try {
            $otp = rand(100000, 999999);
            $smsResponse = ['error' => 0];

            if (!$smsResponse['error']) {
                Session::put('MOBILE_OTP', $otp);

                if (!$request->get('resend_otp')) {
                    $html = view('account.profiles.otp-modal', $input)->render();
                    $result = ["status" => $this->success, "message" => "A verification code has been send to your " . $input['title'] . ". Please check", 'html' => $html, 'otp' => $otp];
                } else {
                    $result = ["status" => $this->success, "message" => "A verification code has been send again to your " . $input['title'] . ". Please check",  'otp' => $otp];
                }
            } else {
                $result = ["status" => $this->error, "message" => $smsResponse['message']];
            }
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => 'Something went to wrong, please try again.'];
        }
        return Response::json($result);
    }

    /**
     * Verify the OTP.
     * On send inquiry Form
     */
    public function verifyOtp(Request $request)
    {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $otp = Session::get('MOBILE_OTP');
            if ($request->get('otp') == $otp) {

                if ($request->get('field') == "phone") {
                    $data = ['phone' => $request->get('value'), 'phone_verified_at' => Carbon::now()->format('Y-m-d H:i:s')];
                    $message = "Your phone number is change successfully.";
                } else {
                    $data = ['email' => $request->get('value'), 'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s')];
                    $message = "Your email address is change successfully.";
                }
                User::find(Auth::id())->update($data);
                DB::commit();
                $result = ['status' => $this->success, 'message' => $message];
            } else {
                DB::rollBack();
                $result = ['status' => $this->error, 'message' => 'Code does not match, Please enter correct code.'];
            }
        } catch (Exception $e) {
            DB::rollBack();
            $result = ['status' => $this->error, 'message' => 'Something went to wrong, please try again.'];
        }
        return Response::json($result);
    }


    public function getUser()
    {
        $user = Auth::user();
        $identity_image = storage_path('app/document/' . $user->detail->identity_proof_name);
        $medical_image = storage_path('app/document/' . $user->detail->medical_registration_proof_name);
        $user->detail->identity_proof_size = File::size($identity_image);
        $user->detail->medical_proof_size = File::size($medical_image);
        return Response::json($user);
    }

    public function isDoctorRegister(Request $request)
    {
        if ($request->get('phone')) {
            $existing_users = User::whereHas('role', function ($q) {
                $q->where('keyword', 'doctor');
            })->Where('phone', $request->get('phone'))->count();
        } else {
            $existing_users = User::whereHas('role', function ($q) {
                $q->where('keyword', 'doctor');
            })->where('email', $request->get('email'))->count();
        }

        return ($existing_users > 0) ? "false" : "true";
    }

    public function getDoctors(Request $request)
    {
        $resultCount = $request->get('paginate_count') ? $request->get('paginate_count') : 10;
        $page = $request->get('page');
        $offset = ($page - 1) * $resultCount;

        $user = User::with('detail')->whereHas('role', function ($role) {
            $role->where('keyword', 'doctor');
        })->whereHas('setting', function ($q) {
            $q->where('do_service_at_other_establishment', 1);
        })->where(function ($query) use ($request) {
            $query->where('name', 'LIKE',  '%' . $request->get("term") . '%');
            $query->orwhere('phone', 'LIKE',  '%' . $request->get("term") . '%');
            $query->orWhereHas('detail', function ($detail) use ($request) {
                $detail->where('registration_number', 'LIKE',  '%' . $request->get("term") . '%');
                $detail->orWhere('liecence_number', 'LIKE',  '%' . $request->get("term") . '%');
            });
        })->where('as_doctor_verified', 2);

        if (checkPermission(['clinic', 'hospital'])) {
            if (!empty(Auth::user()->detail->specialty_ids)) {
                $ids = implode('|', Auth::user()->detail->specialty_ids);
                $user = $user->whereHas('detail', function ($detail) use ($ids) {
                    $detail->whereRaw("specialty_ids REGEXP '(^|,)(" . $ids . ")(,|$)'");
                });
            }
            if (!empty($request->has('selected_value'))) {
                $user = $user->whereNotIn('id', $request->get('selected_value'));
            }
        }

        $user = $user->orderBy('name')->paginate($resultCount);

        $count = $user->total();
        $endCount = $offset + $resultCount;
        $morePages = $endCount > $count;

        $data = [
            "items" => $user->items(),
            "total_count" => $user->total(),
            "pagination" => [
                "more" => $morePages
            ],
        ];
        return Response::json($data);
    }

    public function getDoctorSchedule(Request $request, $id = null)
    {
        try {
            /* Edit Practice schedule */
            if ($request->get('practice_id')) {
                $practice_manager = PracticeManager::find($request->get('practice_id'));
                $allocated_timing = PracticeManager::where('id', '!=', $request->get('practice_id'))->where(['doctor_id' => $id, 'status' => 1])->pluck('timing')->toArray();
                $array_big = [];
                if (!empty($practice_manager->timing)) {
                    $timing = json_decode($practice_manager->timing);
                    if (json_last_error() === 0) {
                        $array_big = array_merge($array_big, $timing);
                    }
                }
                if (!empty($allocated_timing)) {
                    foreach ($allocated_timing as $key => $timing) {
                        if (!empty($timing) && !empty($array_big)) {
                            $days = json_decode($timing);
                            foreach ($days as $d_key => $d) {
                                if (!empty($d->periods)) {
                                    foreach ($d->periods as $p) {
                                        $p->is_exist = 1;
                                    }
                                    $array_big[$d_key]->periods = array_merge($array_big[$d_key]->periods, $d->periods);
                                }
                            }
                        } elseif (!empty($practice_manager->timing) && trim($practice_manager->timing) !== '') {
                            $timing = json_decode($practice_manager->timing);
                            $array_big = array_merge($array_big, $timing);
                        }
                    }
                }
            } else {
                $allocated_timing = PracticeManager::where(['doctor_id' => $id, 'status' => 1])->pluck('timing')->toArray();
                $array_big = [];
                foreach ($allocated_timing as $timing) {
                    if (!empty($timing)) {
                        $temp = is_schedule_exist($timing);
                        $array_big = array_merge($array_big, $temp);
                    }
                }
            }
            $schdule = (count($array_big) > 0) ? json_encode($array_big) : [];
            $result = ['status' => 200, 'message' => "slots loade.", 'result' => $schdule];
        } catch (Exception $e) {
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function checkExistEmail(Request $request)
    {
        if ($request->get('recipient_email')) {
            if (User::where('email', $request->get('recipient_email'))->exists()) {
                return 'false';
            } else {
                return 'true';
            }
        } else {
            return 'true';
        }
    }

    public function checkExistPhone(Request $request)
    {
        if ($request->get('recipient_phone')) {
            if (User::where('phone', $request->get('recipient_phone'))->exists()) {
                return 'false';
            } else {
                return 'true';
            }
        } else {
            return 'true';
        }
    }
}
