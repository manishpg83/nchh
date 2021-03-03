<?php

namespace App\Http\Controllers\Account;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DataTables;
use App\User;
use App\Rating;
use Exception;
use Image;
use Auth;
use DB;

class RatingController extends BaseController
{
    protected $random;
    public $success = 'success';
    public $error = 'error';
    public $exception_message = "Something went wrong, please try again.";

    public function __construct()
    {
        $this->middleware('checkPermission', ['only' => ['index']]);
        $this->random = Str::random(12);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->_setPageTitle('Rating');
        $data = [
            'title' => 'Rating',
            'user' => Auth::user(),
        ];
        if ($request->ajax()) {
            $ratings = Rating::where('user_id', [Auth::id()])->orderBy('id', 'DESC')->get();
            return Datatables::of($ratings)
                ->addColumn('user', function ($data) {
                    return '<img src=" ' . $data->rateableUser->profile_picture . '" class="rounded rounded-circle float-left mr-2" style="width: 40px;min-height: 40px;"/> <span><h6 class="mb-0">' . $data->rateableUser->name . '</h6><p class="mb-0 l-0 f-12">+' . $data->rateableUser->phone . '</p></span>';
                })->addColumn('rate', function ($data) {
                    return '<div class="rating_box" data-rating="' . $data->rating . '"></div>';
                })->addColumn('review', function ($data) {
                    return '<p class="review">' . $data->review .'</p>';
                })->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:;" class="mr-3" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Edit Review" onclick="editReview(' . $row->id . ');"><i class="far fa-edit"></i></a>
                <a href="javascript:;" class="" id="' . $row->id . '" data-toggle="tooltip" data-placement="top" title="Delete Review" onclick="deleteReview(' . $row->id . ');"><i class="far fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->rawColumns(['user', 'action', 'rate', 'review'])
                ->make(true);
        }
        return view('account.rating.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data['rateable_id'] = $request->get('rateable_id');
        $rating = Rating::where(['user_id' => Auth::id(), 'rateable_id' => $request->get('rateable_id')])->first();
        $data['rating'] = $rating;
        $data['title'] = !isset($rating)? "Add Review" : "Edit Review" ;
        $html = view('account.rating.create', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load rating data.', 'html' => $html, 'data' => $data];
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
            'rating' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                DB::beginTransaction();
                $input = $request->all();
                $input['user_id'] = Auth::id();
                Rating::updateOrCreate(['user_id' => Auth::id(), 'rateable_id' => $input['rateable_id']], $input);
                $user = User::find($input['rateable_id']);
                $data['profile'] =  $user;
                $html = view('account.rating.review', $data)->render();
                DB::commit();
                $result = ['status' => $this->success, 'message' => 'Review Add Successful.', 'html' => $html, 'avg_rating' => $user->average_rating, 'total_rating' => $user->total_rating, 'rateable_id' => $user->id];
            } catch (Exception $e) {
                $result = ['status' => $this->error, 'message' => $this->exception_message];
            }
            return Response::json($result);
        } 
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
        $data['title'] = 'Edit review';
        $rating = Rating::find($id);
        $data['rating'] = $rating;
        $html = view('account.rating.update', $data)->render();
        $result = ['status' => $this->success, 'message' => 'load rating data.', 'html' => $html, 'rating' => $rating];
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
            'rating' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        } else {
            try {
                $input = $request->all();
                $rating = Rating::find($id);
                $rating->update($input);
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
            $rating = Rating::find($id);
            $rating->delete();

            $result = ['status' => $this->success, 'message' => 'Deleted successfully.'];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }
}
