<?php

namespace App\Http\Controllers\Account;
    
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DataTables;
use App\User;
use App\DiagnosticsService;
use Exception;
use Image;
use Auth;
use DB;

class DiagnosticsServicesController extends BaseController
{
        protected $random;
        public $success = 'success';
        public $error = 'error';
        public $exception_message = "Something went wrong, please try again.";
    
        public function __construct()
        {
            $this->middleware('checkPermission')->only(['index']);
            $this->random = Str::random(12);
        }
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function index(Request $request)
        {
            $this->_setPageTitle('Diagnostics Services');
            $data = [
                'title' => 'Diagnostics Services',
                'user' => Auth::user(),
            ];
            if ($request->ajax()) {
                $services = DiagnosticsService::where('diagnostics_id', [Auth::id()])->orderBy('id', 'DESC')->get();
                return Datatables::of($services)
                    ->addColumn('name', function ($data) {
                        return $data->name;
                    })->addColumn('price', function ($data) {
                        return ''.$data->price;
                    })->addColumn('information', function ($data) {
                        return isset($data->information)?'<p class="p-0 m-0 d-inline-flax ws-break-spaces">'.$data->information.'</p>': '-';
                    })->addColumn('action', function ($row) {
                        $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Service" onclick="editDiagnosticsService(' . $row->id . ');"><i class="far fa-edit"></i></a>
                    <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Service" onclick="deleteDiagnosticsService(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                        return $btn;
                    })
                    ->rawColumns(['name', 'price','action','information'])
                    ->make(true);
            }
            return view('account.diagnostics_services.index')->with($data);
        }
    
        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function create()
        {
            $data = ['title' => 'Add Diagnostics Service'];
            $html = view('account.diagnostics_services.create', $data)->render();
            $result = ['status' => $this->success, 'message' => 'load service data.', 'html' => $html];
    
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
                'price' => 'required',
            ];
    
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $result = ["status" => $this->error, "message" => $validator];
            } else {
                try {
                    $input = $request->all();
                    $input['diagnostics_id'] = Auth::id();
                    $servece = DiagnosticsService::create($input);
                    
                    $result = ['status' => $this->success, 'message' => 'Service Insert Successful..'];
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
            $data['title'] = 'Edit Diagnostics Service';
            $data['service'] = DiagnosticsService::find($id);
            $html = view('account.diagnostics_services.update', $data)->render();
            $result = ['status' => $this->success, 'message' => 'load service data.', 'html' => $html];
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
                'price' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator);
            } else {
                try {
                    $input = $request->all();
                    $service = DiagnosticsService::find($id);
                    $service->update($input);
                    $result = ['status' => $this->success, 'message' => 'Update Service Successful.'];
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
                $service = DiagnosticsService::find($id);
                $service->delete();
    
                $result = ['status' => $this->success, 'message' => 'Deleted service successfully.'];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
            return Response::json($result);
        }
    }
    