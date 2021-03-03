<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\UserRole;
use App\UserWallet;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends BaseController
{
    protected $random;
    public $status = 200;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        //
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Wallet');
        $user_wallet = UserWallet::select('id', 'user_id')->withTrashed()->groupBy('user_id')->get();
        $data = ['title' => 'Wallet', 'user' => Auth::user(), 'user_wallet' => $user_wallet];
        if ($request->ajax()) {
             if ($request->get('user_id') && $request->get('user_id') !== "all") {
                $userWallet = UserWallet::where('user_id', $request->get('user_id'))->withTrashed()->orderBy('id', 'DESC')->get();
            } else {
                $userWallet = UserWallet::withTrashed()->orderBy('id', 'DESC')->get();
            }
            return DataTables::of($userWallet)
                ->addColumn('name', function ($data) {
                    return $data->user->name;
                })->addColumn('patient_name', function ($data) {
                    return $data->appointment->patient_name;
                })->addColumn('date', function ($data) {
                    return isset($data->created_at) ? date('d M, Y h:i a', strtotime($data->created_at)) : 'No mentioned';
                })->addColumn('price', function ($data) {
                    if (isset($data->status) && $data->status == 'refunded') {
                        $btn = '<span class="text-danger">- ' . $data->price . '</span>';
                    } else {
                        if ($data->appointment->status == 'completed') {
                            $btn = '<span class="text-success">+ ' . $data->price . '</span>';
                        } else {
                            $btn = '<span class="text-warning">+ ' . $data->price . '</span>';
                        }
                    }
                    return $btn;
                })->addColumn('status', function ($data) {
                    $btn = isset($data->status) ? ucfirst($data->status) : '-';
                    return $btn;
                })
                ->rawColumns(['name', 'patient_name', 'date', 'price', 'status'])
                ->make(true);
        }

        return view('admin.wallet.index')->with($data);
    }
}
