<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\MedicalRecord;
use App\SharedMedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends BaseController
{
    public function showSharedMedicalRecord($id)    {

        $this->_setPageTitle('Shared Medical Records');

        $sharedMedicalRecordIds = SharedMedicalRecord::where('doctor_id', Auth::user()->id)->pluck('medical_record_id')->toArray();
        $data['medicalRocord'] = MedicalRecord::where('added_by', $id)->whereIn('id', $sharedMedicalRecordIds)->get();

        return view('front.medical_records.show_shared_medical_records')->with($data);
    }
}
