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
use App\Module;
use App\UserRole;
use App\Permission;
use App\RouteManager;

class PermissionController extends BaseController
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

    public function index(Request $request)
    {
        $this->_setPageTitle('Permission');
        $data = [
            'title' => 'Permission Manager',
            'user' => Auth::user(),
        ];
        $role = UserRole::whereIn('keyword', ['doctor', 'clinic', 'hospital', 'patient','manager','accountant','agent','pharmacy','diagnostics'])->orderBy('keyword', 'ASC')->get();
        $data['role'] = $role;
        $data['module'] = Module::where('status', 'Y')->orderBy('id', 'ASC')->get();
        $data['checkedNodes'] = Permission::where(['role_id' => $role[0]->id, 'status' => 1])->pluck('route_id')->toArray();
        $data['html'] = view('admin.permission.routes')->with($data)->render();
        return view('admin.permission.index')->with($data);
    }

    public function loadRoutes(Request $request)
    {
        $data['module'] = Module::where('status', 'Y')->orderBy('id', 'ASC')->get();
        $data['checkedNodes'] = Permission::where(['role_id' => $request->get('role_id'), 'status' => 1])->pluck('route_id')->toArray();
        try {
            $html = view('admin.permission.routes')->with($data)->render();
            $result = ['status' => $this->success, 'message' => 'Routes permission Loaded.', 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function setPermission(Request $request)
    {
        try {
            $input = $request->all();
            if ($request->get('role_id')) {

                if (!empty($request->get('permission_status'))) {
                    $routelist = RouteManager::orderby('id', 'ASC')->get(['id', 'route_name']);
                    if (!empty($routelist)) {
                        foreach ($routelist as $key => $r) {
                            $permissionObj = Permission::firstOrNew(['role_id' => $request->get('role_id'), 'route_id' => $r->id]);
                            $permissionObj->status = 0;
                            $permissionObj->save();
                        }
                    }
                    Permission::where('role_id', $request->get('role_id'))->whereIn('route_id', $input['permission_status'])->update(['status' => 1]);

                    $result = ['status' => $this->success, 'message' => 'Permission changed.'];
                } else {
                    Permission::where('role_id', $request->get('role_id'))->update(['status' => 0]);
                    $result = ['status' => $this->success, 'message' => 'All route permission disabled.'];
                }
            } else {
                $result = ['status' => $this->error, 'message' => 'Please select routes.'];
            }
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
