<?php

namespace App\Http\Controllers\Account;

use App\Country;
use App\PracticeManager;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use Image;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\DataTables;

use function GuzzleHttp\json_decode;

class PracticeController extends BaseController
{
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        // $this->middleware('auth');
        $this->middleware('checkPermission');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Practice Manager');
        $data = [];

        if ($request->ajax()) {
            try {

                $practice = new PracticeManager;

                if (checkPermission(['doctor'])) {
                    $practice = $practice->where(['doctor_id' => Auth::id()])->orderBy('id', 'DESC');
                    /* 'staff_id' => null */
                } else {
                    $practice = $practice->where('doctor_id', Auth::id())->orderBy('id', 'DESC');
                }

                $datatable = DataTables::of($practice->get());

                $datatable = $datatable->addColumn('name', function ($q) {
                    return $q->name;
                });
                $datatable = $datatable->addColumn('email', function ($q) {
                    return $q->email ? $q->email : '<span class="badge badge-light">No mentioned</span>';
                });
                $datatable = $datatable->addColumn('phone', function ($q) {
                    return $q->phone;
                });
                $datatable = $datatable->addColumn('locality', function ($q) {
                    return $q->locality;
                });
                $datatable = $datatable->addColumn('address', function ($q) {
                    return '<span class="ws-break-spaces">' . $q->address . '</span>';
                });

                $datatable = $datatable->addColumn('action', function ($q) {
                    $button = '';
                    if (!$q->staff_id) {
                        $button = '<a href="' . route('account.practice.edit', [$q->id]) . '" class="f-16 m-l-5 m-r-5" data-toggle="tooltip" data-placement="top" data-trigger="hover" title="Edit Practice"><i class="far fa-edit"></i></a>';
                    }

                    // if (checkAuthorization('admin.course.edit')) {
                    //     $button .= '<a href="javascript:;" class="f-16 m-l-5 m-r-5" onclick="loadCourseForm(' . $q->id . ');" data-toggle="tooltip" data-placement="top" data-trigger="hover" title="Edit Course Details"><i class="icofont icofont-ui-edit"></i></a>';
                    // }

                    // $button .= '<a href="javascript:;" class="f-16 m-l-5 m-r-5" onclick="deletePractice(' . $q->id . ')" data-toggle="tooltip" data-original-title="Delete Practice"><i class="icofont icofont-ui-delete"></i></a>';

                    return $button;
                });

                $datatable = $datatable->rawColumns(['name', 'email', 'phone', 'locality', 'address', 'action']);

                $datatable = $datatable->make(true);
            } catch (Exception $e) {
                $datatable = Datatables::of(PracticeManager::select()->take(0)->get());
                $datatable = $datatable->make(true);
            }
            return $datatable;
        }

        return view('account.practice_manager.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->_setPageTitle('Create Practice');

        $allocated_timing = PracticeManager::select('timing', 'name')->where(['doctor_id' => Auth::id(), 'status' => 1])->get();
        $array_big = [];
        foreach ($allocated_timing as $timing) {
            if (!empty($timing)) {
                $temp = is_schedule_exist($timing);
                $array_big = array_merge($array_big, $temp);
            }
        }
        $data = [
            'country' => Country::pluck('name', 'id'),
            'allocated_timing' => json_encode($array_big)
        ];
        if (checkPermission(['diagnostics'])) {
            $practice_manager = PracticeManager::select('timing', 'name')->where(['added_by' => Auth::id(), 'status' => 1])->first();
            if (!empty($practice_manager->timing)) {
                $timing = json_decode($practice_manager->timing);
                if (json_last_error() === 0) {
                    $array_big = array_merge($array_big, $timing);
                }
            }
            $data = [
                'country' => Country::pluck('name', 'id'),
                'practice' => $practice_manager,
                'allocated_timing' => json_encode($array_big)
            ];
        }

        return view('account.practice_manager.create')->with($data);
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
        $input['added_by'] = Auth::id();
        $rules = [
            'name' => 'required',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[0-9]{10}+$/',
            'address' => "required",
            'locality' => "required",
            'city' => "required",
            'country' => "required",
            'pincode' => "required",
            'logo' => "mimes:jpeg,jpg,png",
        ];

        if (checkPermission(['doctor'])) {
            $input['doctor_id'] = Auth::id();
            $rules += [
                'fees' => "required|numeric",
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            if ($request->hasFile('logo')) {
                $avatar = $request->file('logo');
                if ($avatar->getClientOriginalExtension() == 'jfif') {
                    $filename = time() . uniqid() . '.jpg';
                    Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/practice/' . $filename));
                    $input['logo'] = $filename;
                } else {
                    $filename = time() . uniqid() . '.' . $avatar->getClientOriginalExtension();
                    /* Image::make($avatar)->save(storage_path('app/practice/' . $filename));
                $input['logo'] = $filename; */

                    Image::make($avatar)->resize(700, 700, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->save(storage_path('app/practice/' . $filename));
                    $input['logo'] = $filename;
                }
            }
            
            if (checkPermission(['doctor'])) {
                PracticeManager::create($input);
            }
            
            if (checkPermission(['diagnostics'])) {
                PracticeManager::updateOrCreate(['added_by' => Auth::id()], $input);
            }

        }
        if (checkPermission(['diagnostics'])) {
            return redirect()->route('account.practice.create')->with('success', 'Practice Created.');
        }else{
            return redirect()->route('account.practice.index')->with('success', 'Practice Created.');
        }
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
    public function edit($id)
    {
        $practice_manager = PracticeManager::find($id);

        //check practice owner
        if (Auth::id() == $practice_manager->added_by) {
            $allocated_timing = PracticeManager::select('timing', 'name')->where('id', '!=', $id)->where(['doctor_id' => Auth::id(), 'status' => 1])->get();
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
                        $days = json_decode($timing->timing);
                        foreach ($days as $d_key => $d) {
                            if (!empty($d->periods)) {
                                foreach ($d->periods as $p) {
                                    $p->is_exist = 1;
                                    $p->title = $timing->name;
                                }
                                // $array_big[$d_key]->periods = $d->periods;
                                $array_big[$d_key]->periods = array_merge($array_big[$d_key]->periods, $d->periods);
                            }
                        }
                        // $temp = is_schedule_exist($timing);
                        // $array_big = array_merge($array_big, $temp);
                    } else {
                        $timing = json_decode($practice_manager->timing);
                        $array_big = array_merge($array_big, $timing);
                    }
                }
            }
            $this->_setPageTitle('Edit Practice');
            $data = [
                'country' => Country::pluck('name', 'id'),
                'practice' => $practice_manager,
                'allocated_timing' => json_encode($array_big)
            ];
            return view('account.practice_manager.edit')->with($data);
        } else {
            return abort(403);
        }
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
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[0-9]{10}+$/',
            'address' => "required",
            'locality' => "required",
            'city' => "required",
			'state' => "required",
            'country' => "required",
            'pincode' => "required",
			'latitude' => "required",
            'longitude' => "required",
            'fees' => "required|numeric",
            'logo' => "mimes:jpeg,jpg,png",
        ]);

        try {
            $input = $request->except(['_token', '_method']);
            $practice = PracticeManager::find($id);
            if ($request->hasFile('logo')) {
                $avatar = $request->file('logo');
                if ($avatar->getClientOriginalExtension() == 'jfif') {
                    $filename = time() . uniqid() . '.jpg';
                    Image::make($avatar)->encode('jpg', 75)->save(storage_path('app/healthfeed/' . $filename));
                    $input['logo'] = $filename;
                } else {
                    $filename = time() . uniqid() . '.' . $avatar->getClientOriginalExtension();
                    /* Image::make($avatar)->save(storage_path('app/practice/' . $filename));
                    $input['logo'] = $filename; */

                    Image::make($avatar)->resize(700, 700, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->save(storage_path('app/practice/' . $filename));
                    $input['logo'] = $filename;
                }
                $image_path = storage_path('app/practice/' . $practice->logo_filename);
                if ($practice->logo_filename != "practice_logo.png") {
                    @unlink($image_path);
                }
            }
            // p($input);
            PracticeManager::where('id', $id)->update($input);

            return redirect()->route('account.practice.index')->with('success', "Practice Updated.");
        } catch (Exception $e) {
            return back()->withError($this->exception_message);
        }
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
            $practice = PracticeManager::find($id)->delete();
            if ($practice) {

                $file_path = storage_path('app/medical-record-files/' . $practice->logo_filename);
                @unlink($file_path);

                $practice->delete();
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
}
