<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Mail\ApprovalMail;
use App\Specialty;
use App\Notification;
use App\UserDetail;
use App\Country;
use App\PracticeManager;
use App\User;
use App\Service;
use App\UserGallery;
use Exception;
use Timezone;
use Image;

class IndexController extends BaseController
{
    protected $random;
    public $status = 200;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission')->except(['editProfile', 'destroyGalleryFile', 'changeField', 'userDetail']);
        $this->random = Str::random(12);
    }

    public function showProfileForm(Request $request)
    {
        $this->_setPageTitle('Accounts');
        $user = Auth::user();
        
        if ($request->ajax()) {
            // $result = ["status" => $this->success, "message" => "load"];
            if (!empty($user->gallery)) {
                foreach ($user->gallery as $key => $file) {
                    $file->file_name = $file->image_name;
                    $file->file_size = File::size(storage_path('app/user/gallery_photos/' . $file->image_name));
                }
            }
            return Response::json($user->gallery, $this->status);
        }
        $selected = $user->timezone ? $user->timezone : 'Asia/Kolkata';
        $placeholder = 'Select a Timezone';
        $formAttributes = array('class' => 'form-control select2', 'name' => 'timezone');
        $data = [
            'title' => 'Accounts',
            'user' => $user,
            'country' => Country::pluck('name', 'id')->toArray(),
            'specialty' => Specialty::pluck('title', 'id')->toArray(),
            'services' => Service::pluck('name', 'id')->toArray(),
            'doctors' => User::pluck('name', 'id')->toArray(),
            'timezonelist' => Timezone::selectForm($selected, $placeholder, $formAttributes),
        ];
        //dd($data);
        return view("account/profiles/edit-profile")->with($data);
    }

    public function editProfile(Request $request, $id)
    {
        $input = $request->all(); 
        $rules = [
            'name' => 'required',
        ];

        if (checkPermission(['clinic', 'hospital'])) {
            $rules += [
                'specialty_ids.*' => 'required',
                'services.*' => 'required',
            ];
        }

        if (empty($request->get('as_dedicated_profile'))) {
            $rules += [
                'address' => 'required',
                'locality' => 'required',
                'city' => 'required',
                'state' => 'required',
                'country' => 'required',
                'pincode' => 'required',
                'timezone' => 'required',
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ['status' => $this->error, 'message' => $validator->errors()->first()];
        } else {
            try {
            DB::beginTransaction();
            $user = User::find($id);
            $detail = $request->get('detail');
            if ($request->hasFile('profile_picture')) {
                $avatar = $request->file('profile_picture');
                if ($avatar->getClientOriginalExtension() == 'jfif') {
                    $filename = time() . $this->random . '.jpg';
                    Image::make($avatar)->fit(500, 500, function ($constraint) {
                        $constraint->upsize();
                    })->save(storage_path('app/user/' . $filename));
                    $input['profile_picture'] = $filename;
                } else {
                    $filename = time() . $this->random . '.' . $avatar->getClientOriginalExtension();
                    Image::make($avatar)->fit(500, 500, function ($constraint) {
                        $constraint->upsize();
                    })->save(storage_path('app/user/' . $filename));
                    $input['profile_picture'] = $filename;
                }

                /*remove the existing profile picture*/
                $image_path = storage_path('app/user/' . $user->image_name);
                if ($user->image_name != "default.png") {
                    @unlink($image_path);
                }
            }

            if ($request->get('specialty_ids')) {
                $user_specialties = arrayToString($request->get('specialty_ids')); 
                $detail['specialty_ids'] = $user_specialties; 
            }

            if ($request->get('services')) {
                $services = arrayToString($request->get('services'));
            }

            $subRecord = [];
            if ($request->hasFile('file')) {
                foreach ($request->file('file') as $key => $file) {
                    $filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
                    Image::make($file)->save(storage_path('app/user/gallery_photos/' . $filename));
                    $subRecord[] = [
                        'user_id' => $user->id,
                        'image' => $filename
                    ];
                }
                UserGallery::insert($subRecord);
            }

            $user->update($input);
            if ($request->get('detail')) {
                $detail['services'] = isset($services) ? $services : '';
                UserDetail::where(['user_id' => $user->id])->update($detail);
            }

            //Start change practice address
            if(checkPermission(['clinic','hospital','diagnostics'])){
                PracticeManager::where('added_by', Auth::id())->update([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'logo' => $user->profile_picture,
                    'address' => $user->address,
                    'locality' => $user->locality,
                    'city' => $user->city,
                    'country' => $user->country,
                    'pincode' => $user->pincode,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                ]);
            }
            //End change practice address

            // UserDetail::updateOrCreate(['user_id' => $user->id], $input);
            $result = ['status' => $this->success, 'message' => "Your profile update successfully.", "result" => $user];
            DB::commit();
            } catch (Exception $e) {
                    $this->status = 401;
                    DB::rollBack();
                    $result = ['status' => $this->error, 'message' => $this->exception_message];
                }
        }

        return Response::json($result, $this->status);
    }

    public function destroyGalleryFile($id)
    {
        try {
            DB::beginTransaction();
            $userGallery = UserGallery::find($id);
            if ($userGallery) {

                $file_path = storage_path('app/user/gallery_photos/' . $userGallery->image_name);
                @unlink($file_path);

                $userGallery->delete();
            }
            DB::commit();
            $result = ["status" => $this->success, "message" => "Gallery File Deleted."];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    /* Used function in edit profile (not used) */
    public function changeField(Request $request)
    {
        try {
            $data = [];
            switch ($request->get('field')) {
                case 'registration_number':
                    $data = ['registration_number' => $request->get('value'), 'registration_number_verified' => null];
                    $message = "Your registration number change request send successfully.";
                    break;

                case 'liecence_number':
                    $data = ['liecence_number' => $request->get('value'), 'liecence_number_verified' => null];
                    $message = "Your liecence number change request send successfully.";
                    break;

                default:
                    # code...
                    break;
            }

            User::find(Auth::id())->detail()->update($data);
            $result = ['status' => $this->success, 'message' => $message];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }

        return Response::json($result);
    }

    public function myDoctors()
    {
        $this->_setPageTitle('My Doctors');
        $data = ['user' => Auth::user()];
        return view('account.profiles.my-doctor')->with($data);
    }

    public function userDetail($id)
    {
        $user = User::find($id);
        $this->_setPageTitle('View Details (' . $user->name . ')');
        $data = ['user' => $user];
        return view('account.user.show')->with($data);
    }
}
