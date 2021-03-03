<?php

namespace App\Http\Controllers\Account;

use App\SharePrescription;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Image;
use DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PrescriptionController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        //$this->middleware('checkPermission', ['only' => ['index']]);
        $this->random = Str::random(12);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Prescription Request');
        $data = [
            'title' => 'Prescription Request',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $prescriptions = SharePrescription::where('pharmacy_id', [Auth::id()])->orderBy('prescription_id', 'DESC')->get();
            return DataTables::of($prescriptions)
                ->addColumn('prescription_id', function ($data) {
                    return '<strong>#'.$data->prescription_id.'</strong>';
                })->addColumn('patient', function ($data) {
                    if ($data->appointment->patient_name != null) {
                        $btn = '<img src=" ' . $data->patient->profile_picture . '" class="rounded rounded-circle float-left mr-2" style="width: 35px;min-height: 35px;"/> <span><h6 class="mb-0">' . $data->appointment->patient_name . '</h6><p class="mb-0 l-0 f-12">';
                        $btn .= isset($data->appointment->patient_phone) ? '<i class="fas fa-phone"></i> ' . $data->appointment->patient_phone : '';
                        $btn .= '</p></span>';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('doctor', function ($data) {
                    if ($data->doctor->name != null) {
                        $btn = '<img src=" ' . $data->doctor->profile_picture . '" class="rounded rounded-circle float-left mr-2" style="width: 35px;min-height: 35px;"/> <span><h6 class="mb-0">' . $data->doctor->name . '</h6><p class="mb-0 l-0 f-12">';
                        $btn .= isset($data->doctor->phone) ? '<i class="fas fa-phone"></i> +' . $data->doctor->dialcode . $data->doctor->phone : '';
                        $btn .= '</p></span>';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('practice', function ($data) {
                    if ($data->appointment->practice->name != null) {
                        $btn = '<img src=" ' . $data->appointment->practice->logo . '" class="rounded rounded-circle float-left mr-2" style="width: 35px;min-height: 35px;"/> <span><h6 class="mb-0">' . $data->appointment->practice->name . '</h6><p class="mb-0 l-0 f-12">';
                        $btn .= isset($data->appointment->practice->phone) ? '<i class="fas fa-phone"></i> +' . $data->appointment->practice->phone : '';
                        $btn .= '</p></span>';
                    } else {
                        $btn = '<span class="badge badge-pill badge-secondary">Not Mentioned</span>';
                    }
                    return $btn;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('account.prescription.show', [$row->prescription_id]) . '" class="mr-3" id="' . $row->prescription_id . '" data-toggle="tooltip" data-placement="top" title="View Prescription"><i class="far fa-eye"></i></a>';
                    return $btn;
                })
                ->rawColumns(['prescription_id', 'patient', 'doctor', 'practice', 'action'])
                ->make(true);
        }
        return view('account.prescription.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $prescriptions = SharePrescription::where('prescription_id',$id)->first();
        $this->_setPageTitle('Prescription Detail');
        $data = [
            'title' => 'Prescription Detail',
            'user' => Auth::user(),
            'name' => ucwords($prescriptions->appointment->patient_name),
            'prescriptions' => $prescriptions,
        ];
        return view('account.prescription.show')->with($data);;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
