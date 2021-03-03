<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\PracticeManager;
use App\User;
use App\StaffManager;

trait UserTraits
{
    public function totalDoctor()
    {
        /*  $doctor = PracticeManager::where('added_by', $this->attributes['id'])->where('status', 1)->count();
        return $doctor; */
        return User::whereHas('practice', function ($practice) {
            $practice->where(['added_by' => $this->attributes['id'], 'status' => 1]);
        })->get()->except($this->attributes['id'])->count();
    }

    public function myDoctor()
    {
        return User::whereHas('practice', function ($practice) {
            $practice->where(['added_by' => $this->attributes['id'], 'status' => 1]);
        })->with(['detail' => function ($detail) {
            $detail->select(['id', 'user_id', 'specialty_ids', 'degree', 'experience']);
        }])->get(['id', 'name', 'profile_picture'])->except($this->attributes['id'])->map(function ($object) {
            $object->speciality = $object->detail->specialty_name;
            $object->consultation_fee   = $object->doctorPracticeFee($this->attributes['id'], $object->id);
            $object->average_rating = isset($object->average_rating) ? $object->average_rating : 0;
            $object->total_review = isset($object->total_rating) ? $object->total_rating : 0;
            return $object;
        });
    }

    public function doctorPracticeFee($id, $doctorId)
    {
        $doctor = PracticeManager::where('added_by', $id)->where('doctor_id', $doctorId)->where('status', 1)->first();
        return $doctor->fees;
    }

    /*use for rating table user_id users details*/
    public function userDetail($id)
    {
        return User::select('name', 'profile_picture')->where('id', $id)->first();
    }

    public function manager()
    {
        $staff_ids =  $this->hasMany('App\StaffManager', 'added_by')->pluck('user_id');
        $user = User::whereIn('id', $staff_ids)->whereHas('role', function ($q) {
            $q->where('keyword', 'manager');
        })->get();
        return $user;
    }

    public function practiceAvailable($id)
    {
        if (PracticeManager::where('doctor_id', $id)->exists()) {
            return true;
        } else if (PracticeManager::where('added_by', $id)->exists()) {
            return true;
        } else {
            return false;
        }
    }


    function doctorProfileVerificationAlert($value)
    {
        switch ($value) {
            case 0:
                return 'Lets create your dedicated doctor profile.';
                break;
            case 1:
                return 'Your Profile Is Under Verification.';
                break;
            case 2:
                return 'Your profile has been approved.';
                break;
            case 3:
                return 'Your profile has been rejected.';
                break;
            default:
                return 'Lets create your dedicated doctor profile.';
                break;
        }
    }

    function agentProfileVerificationAlert($value)
    {
        switch ($value) {
            case 0:
                return 'Lets create your dedicated agent profile.';
                break;
            case 1:
                return 'Your Profile Is Under Verification.';
                break;
            case 2:
                return 'Your profile has been approved.';
                break;
            case 3:
                return 'Your profile has been rejected.';
                break;
            default:
                return 'Lets create your dedicated agent profile.';
                break;
        }
    }

    function bankDetailVerificationAlert($value)
    {
        switch ($value) {
            case 0:
                return 'You need to submit your bank details for receive payment';
                break;
            case 1:
                return 'Your request Is Under Verification.';
                break;
            case 2:
                return 'Your request has been approved.';
                break;
            case 3:
                return 'Your bank details rejected by Administrator';
                break;
            default:
                return 'You need to submit your bank details for receive payment';
                break;
        }
    }
}
