<?php

namespace App\Http\Controllers\Admin;

use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class LanguageController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";
    
    public function index(Request $request)
    {
        $this->_setPageTitle('Languages');
        $data = [
            'title' => 'Languages',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $language = Language::orderBy('id', 'DESC')->get();
            return DataTables::of($language)
                ->addColumn('title', function ($data) {
                    return $data->name;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Language" onclick="editLanguage(' . $row->id . ');"><i class="far fa-edit"></i></a>
                            <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Language" onclick="deleteLanguage(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['title', 'action'])
                ->make(true);
        }
        return view('admin.language.index')->with($data);
    }

    public function create()
    {
        $data = ['title' => 'Add Language'];
        $html = view('admin.language.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load drug language data.', 'html' => $html];

        return Response::json($result);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'short_name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                Language::create($input);

                $result = ['status' => $this->success, 'message' => 'Language Insert Successful..'];
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
    }

    public function edit($id)
    {
        $data['title'] = 'Edit Language';
        $data['language'] = Language::find($id);
        $html = view('admin.language.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load language data.', 'html' => $html];
        return Response::json($result);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
            'short_name' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                $language = Language::find($id);
                $language->update($input);
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
            $language = Language::find($id);
            $language->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
