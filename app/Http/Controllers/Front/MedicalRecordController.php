<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\MedicalRecord;
use App\MedicalRecordFile;
use App\SharedMedicalRecord;
use App\User;
use App\Jobs\SharedMedicalRecordJob;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Image;
use Validator;

class MedicalRecordController extends BaseController
{

    public $status = 200;
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";

    public function __construct()
    {
        $this->middleware('checkPermission');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->_setPageTitle('Medical Records');
        $data['medicalRocord'] = MedicalRecord::where('added_by', Auth::id())->get();
        return view('front.medical_records.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->_setPageTitle('Add Medical Records');
        return view('front.medical_records.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        try {
            DB::beginTransaction();
            $input['added_by'] = Auth::id();
            $medicalRecord = MedicalRecord::create($input);
            if ($medicalRecord) {
                $subRecord = [];
                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $key => $file) {
                        $filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
                        Image::make($file)->save(storage_path('app/medical-record-files/' . $filename));
                        $subRecord[] = [
                            'record_id' => $medicalRecord->id,
                            'filename' => $filename
                        ];
                    }
                    MedicalRecordFile::insert($subRecord);
                }
            }
            DB::commit();
            $result = ["status" => $this->success, "message" => "Record added.", 'redirect' => route('medical_record.index')];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $this->_setPageTitle('Medical Records');
        $medical_record = MedicalRecord::find($id);
        $data['record'] = $medical_record;
        $data['record_files'] = $medical_record->files;

        if ($request->ajax()) {
            // $result = ["status" => $this->success, "message" => "load"];
            if (!empty($medical_record->files)) {
                foreach ($medical_record->files as $key => $file) {
                    $file->file_name = $file->filename_value;
                    $file->file_size = File::size(storage_path('app/medical-record-files/' . $file->filename_value));
                }
            }
            return Response::json($medical_record->files, $this->status);
        }
        // p($data['files']);
        return view('front.medical_records.edit')->with($data);
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
        $input = $request->all();
        try {
            DB::beginTransaction();
            $medicalRecord = MedicalRecord::find($id)->update($input);
            if ($medicalRecord) {
                $subRecord = [];
                // MedicalRecordFile::where('record_id', $id)->delete();
                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $key => $file) {
                        $filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
                        Image::make($file)->save(storage_path('app/medical-record-files/' . $filename));
                        $subRecord[] = [
                            'record_id' => $id,
                            'filename' => $filename
                        ];
                    }
                    MedicalRecordFile::insert($subRecord);
                }
            }
            DB::commit();
            $result = ["status" => $this->success, "message" => "Record Updated."];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
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
            DB::beginTransaction();
            $medicalRecord = MedicalRecord::find($id);
            if ($medicalRecord) {

                if ($medicalRecord->files) {
                    foreach ($medicalRecord->files as $key => $file) {
                        $file_path = storage_path('app/medical-record-files/' . $file->filename_value);
                        @unlink($file_path);
                        $file->delete();
                    }
                }

                $medicalRecord->delete();
            }
            DB::commit();
            $result = ["status" => $this->success, "message" => "Record Deleted."];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function destroyRecordFile($id)
    {
        try {
            DB::beginTransaction();
            $medicalRecordFile = MedicalRecordFile::find($id);
            if ($medicalRecordFile) {

                $file_path = storage_path('app/medical-record-files/' . $medicalRecordFile->filename_value);
                @unlink($file_path);

                $medicalRecordFile->delete();
            }
            DB::commit();
            $result = ["status" => $this->success, "message" => "Record File Deleted."];
        } catch (Exception $e) {
            DB::rollBack();
            $this->status = 401;
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result, $this->status);
    }

    public function shareMedicalRecord()    {
        
        try {
            $data = ['title' => 'Share Medical Record', 'doctors' => Auth::user()->wishlist, 'id' => request()->id  ];
            $html = view('front.medical_records.share_medical_record', $data)->render();
            $result = ["status" => $this->success, "message" => "data loded", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function storeShareMedicalRecord(Request $request)   {
        
        $rules = [
            'medical_record_id'  => 'required',
            'doctor_id'  => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator->errors()->first()];
        } else {
            try {
                $input = $request->all();
                $input['user_id'] = Auth::id();
                $user = Auth::user();
                $data = [];
                $user_account = SharedMedicalRecord::Create($input);

                //get doctor id
                $doctor =  User::select('id', 'email')->where('id', $request->doctor_id)->first();

                if ($doctor->email) {
                    $mailInfo = ([
                        'receiver_email' => $doctor->email,
                        'receiver_name' => 'NC Health HUB',
                        'title' => '',
                        'subject' => 'Shared Medical Record',
                        'content' => 'Shared Medical Record<br>
                         Please, let me know if any concerns about the same.<br>
                         <br>
                         <br>
                         Thanks.',

                    ]);
                    dispatch(new SharedMedicalRecordJob($mailInfo)); //add mail to queue
                }

                $result = ["status" => $this->success, "message" => "Medical Record shared successfully.", 'redirect' => 'medical_record'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
        }
        return Response::json($result);
    }
}
