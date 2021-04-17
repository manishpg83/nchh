<?php

namespace App\Http\Controllers\Account;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DataTables;
use App\User;
use App\Drug;
use App\DrugType;
use App\DrugUnit;
use Exception;
use Image;
use Auth;
use DB;

class DrugController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission');
        $this->random = Str::random(12);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Drug');
        $data = [
            'title' => 'Drug',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $drugs = Drug::where('added_by', [Auth::id()])->orderBy('id', 'DESC')->get();
            return Datatables::of($drugs)
                ->addColumn('type', function ($data) {
                    //return $data->drugType->name;
                    return ($data->drugType->name == 'Other') ? $data->other_type : $data->drugType->name;
                })->addColumn('unit', function ($data) {
                    return ($data->drugUnit->name == 'Other') ? $data->other_unit : $data->drugUnit->name;
                })->addColumn('strength', function ($data) {
                    return $data->strength;
                })->addColumn('instructions', function ($data) {
                    return '<span class="ws-break-spaces">'.$data->instructions.'</span>';
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Drug" onclick="editDrug(' . $row->id . ');"><i class="far fa-edit"></i></a>
                <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Drug" onclick="deleteDrug(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action','instructions'])
                ->make(true);
        }
        return view('account.drug.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = ['title' => 'Add Drug'];
        $data['type'] = DrugType::orderBy('name', 'asc')->pluck('name', 'id');
        $data['unit'] = DrugUnit::orderBy('name', 'asc')->pluck('name', 'id');
        $html = view('account.drug.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load drug data.', 'html' => $html];

        return Response::json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'strength' => 'required|integer',
            'unit' => 'required',
            //'other_unit' => 'required_if:unit,other'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                $input['added_by'] = Auth::id();
                $drug = Drug::create($input);
                $data = [];
                if ($request->get('from') == 'prescription') {
                    $unique_id = mt_rand(11, 99);
                    $data['html'] = view('account.patients.prescription_append')->with(['name' => $drug->drug_name, 'unique_id' => $unique_id, 'appointment_id' => $request->get('appointment_id')])->render();
                    $data['drug_name'] = '<option value="'.$drug->drug_name.'">'.$drug->name.'('.$drug->strength.$drug->unit.')</option>';
                }
                $result = ['status' => $this->success, 'message' => 'Drug Insert Successful..', 'data' => $data];
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
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
        $data['title'] = 'Edit drug';
        $data['drug'] = Drug::find($id);
        $data['type'] = DrugType::orderBy('name', 'asc')->pluck('name', 'id');
        $data['unit'] = DrugUnit::orderBy('name', 'asc')->pluck('name', 'id');
        $html = view('account.drug.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load drug data.', 'html' => $html];
        return Response::json($result);
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
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'strength' => 'required|integer',
            'unit' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                $drug = Drug::find($id);
                $drug->update($input);
                $result = ['status' => $this->success, 'message' => 'Update Successful.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
            return Response::json($result);
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
            $drug = Drug::find($id);
            $drug->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
