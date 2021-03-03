<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DataTables;
use App\Drug;
use App\HealthFeedCategory;
use Exception;
use Auth;

class HealthFeedCategoryController extends BaseController
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
        $this->_setPageTitle('HealthFeed Category');
        $data = [
            'title' => 'HealthFeed Category',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $healthfeed_category = HealthFeedCategory::orderBy('id', 'DESC')->get();
            return DataTables::of($healthfeed_category)
                ->addColumn('title', function ($data) {
                    return $data->title;
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Category" onclick="editCategory(' . $row->id . ');"><i class="far fa-edit"></i></a>
                <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Category" onclick="deleteCategory(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['title', 'action'])
                ->make(true);
        }
        return view('admin.healthfeed_category.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = ['title' => 'Add Category'];
        $html = view('admin.healthfeed_category.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load healthfeed category data.', 'html' => $html];

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
            'title' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                HealthFeedCategory::create($input);

                $result = ['status' => $this->success, 'message' => 'Category Insert Successful..'];
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
        $data['title'] = 'Edit category';
        $data['category'] = HealthFeedCategory::find($id);
        $html = view('admin.healthfeed_category.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load category data.', 'html' => $html];
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
            'title' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                $category = HealthFeedCategory::find($id);
                $category->update($input);
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
            $category = HealthFeedCategory::find($id);
            $category->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
