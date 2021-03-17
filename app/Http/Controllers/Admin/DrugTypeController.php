<?php

namespace App\Http\Controllers\Admin;

use App\DrugType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class DrugTypeController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";
    
    public function index(Request $request)
    {
        $this->_setPageTitle('Drug Types');
        $data = [
            'title' => 'Drug Types',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $healthfeed_category = DrugType::orderBy('id', 'DESC')->get();
            return DataTables::of($healthfeed_category)
                ->addColumn('title', function ($data) {
                    return $data->name;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Type" onclick="editType(' . $row->id . ');"><i class="far fa-edit"></i></a>
                            <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Type" onclick="deleteType(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['title', 'action'])
                ->make(true);
        }
        return view('admin.drug_type.index')->with($data);
    }

    public function create()
    {
        $data = ['title' => 'Add Type'];
        $html = view('admin.drug_type.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load drug type data.', 'html' => $html];

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
                DrugType::create($input);

                $result = ['status' => $this->success, 'message' => 'Type Insert Successful..'];
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Type';
        $data['type'] = DrugType::find($id);
        $html = view('admin.drug_type.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load type data.', 'html' => $html];
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
                $type = DrugType::find($id);
                $type->update($input);
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
            $type = DrugType::find($id);
            $type->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
