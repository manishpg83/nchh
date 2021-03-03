<?php

namespace App\Http\Controllers\Front;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Auth;
use App\Specialty;
use App\User;
use App\Article;
use App\ArticleCategory;
use App\Report;
use Exception;

class ReportController extends Controller
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = ['title' => 'Report Manager'];
        if ($request->ajax()) {
            $reports = Report::where('user_id', [Auth::id()])->orderBy('id', 'DESC')->get();
            return DataTables::of($reports)
                ->addColumn('title', function ($data) {
                    return '' . $data->title . '<br><span class="badge badge-pill badge-secondary" title="Report type">' . $data->type . '</span>';
                })->addColumn('image', function ($data) {
                    if (!empty($data->image_name)) {
                        $image = $data->image;
                    } else {
                        $image = asset("images/d_default.png");
                    }
                    return '<img src="' . $image . '" class="rounded" style="width:100px"/>';
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="View Report" onclick="viewReport(' . $row->id . ');"><i class="far fa-eye"></i></a>
                <a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Report" onclick="editReport(' . $row->id . ');"><i class="far fa-edit"></i></a>
                <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Report" onclick="deleteReport(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['title', 'image', 'action'])
                ->make(true);
        }
        return view('front.report.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = ['title' => 'Add Report'];
        $html = view('front.report.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load report data.', 'html' => $html];

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
            'name' => 'required',
            'date' => 'required',
            'type' => 'required',
            //'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                if ($request->file('image')) {
                    $file = $request->file('image');
                    $image_name = date('dmYHis') . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('/images/'), $image_name);
                    $input['image'] = $image_name;
                }
                $input['user_id'] = Auth::id();
                $article = Report::create($input);
                $result = ['status' => $this->success, 'message' => 'Report Insert Successful..'];
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
        $data['title'] = 'View Report';
        $data['report'] = Report::find($id);
        $html = view('front.report.show', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load report data.', 'html' => $html];
        return Response::json($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['title'] = 'Edit Report';
        $data['report'] = Report::find($id);
        $html = view('front.report.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load report data.', 'html' => $html];
        return Response::json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report $report)
    {
        $rules = [
            'title' => 'required',
            'name' => 'required',
            'type' => 'required',
            'date' => 'required',
            //'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                if ($request->file('image')) {

                    $image_path = public_path('/images/' . $report->image_name);
                    if (File::exists($image_path)) {
                        File::delete($image_path);
                    }

                    $file = $request->file('image');
                    //you also need to keep file extension as well
                    $image_name = date('dmYHis') . '.' . $file->getClientOriginalExtension();
                    //using the array instead of object
                    $file->move(public_path('/images/'), $image_name);
                    $input['image'] = $image_name;
                }

                $report->update($input);

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
            $report = Report::find($id);
            $image_path = public_path() . '/images/' . $report->image_name;
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
            $report->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
