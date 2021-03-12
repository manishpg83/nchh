<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DataTables;
use App\User;
use App\Drug;
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
        // $this->middleware('checkPermission');
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
                ->addColumn('strength', function ($data) {
                    return ($data->unit == 'other') ? $data->strength . $data->other_unit : $data->strength . $data->unit;
                })->addColumn('instructions', function ($data) {
                    return '<span class="ws-break-spaces">' . $data->instructions . '</span>';
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Drug" onclick="editDrug(' . $row->id . ');"><i class="far fa-edit"></i></a>
                <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Drug" onclick="deleteDrug(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['strength', 'action', 'instructions'])
                ->make(true);
        }
        return view('admin.drug.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = ['title' => 'Add Drug'];
        $data['type'] = config('view.Drug_Type');
        $data['unit'] = config('view.Dosage_Unit');
        $html = view('admin.drug.create', $data)->render();
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
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                $input['added_by'] = Auth::id();
                Drug::create($input);

                $result = ['status' => $this->success, 'message' => 'Drug Insert Successful..'];
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
        $data['type'] = config('view.Drug_Type');
        $data['unit'] = config('view.Dosage_Unit');
        $html = view('admin.drug.update', $data)->render();
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
