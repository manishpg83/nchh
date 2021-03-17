<?php

namespace App\Http\Controllers\Admin;

use App\DrugUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class DrugUnitController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";
    
    public function index(Request $request)
    {
        $this->_setPageTitle('Drug Units');
        $data = [
            'title' => 'Drug Units',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $drug_unit = DrugUnit::orderBy('id', 'DESC')->get();
            return DataTables::of($drug_unit)
                ->addColumn('title', function ($data) {
                    return $data->name;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Unit" onclick="editUnit(' . $row->id . ');"><i class="far fa-edit"></i></a>
                            <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Unit" onclick="deleteUnit(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['title', 'action'])
                ->make(true);
        }
        return view('admin.drug_unit.index')->with($data);
    }

    public function create()
    {
        $data = ['title' => 'Add Unit'];
        $html = view('admin.drug_unit.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load drug unit data.', 'html' => $html];

        return Response::json($result);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                DrugUnit::create($input);

                $result = ['status' => $this->success, 'message' => 'Unit Insert Successful..'];
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Unit';
        $data['unit'] = DrugUnit::find($id);
        $html = view('admin.drug_unit.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load unit data.', 'html' => $html];
        return Response::json($result);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                $unit = DrugUnit::find($id);
                $unit->update($input);
                $result = ['status' => $this->success, 'message' => 'Update Successful.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
            return Response::json($result);
        }
    }

    public function destroy($id)
    {
        try {
            $unit = DrugUnit::find($id);
            $unit->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
