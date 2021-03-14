<?php

namespace App\Http\Controllers\Front;

use App\City;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Specialty;
use App\Wishlist;
use App\User;
use Exception;

class AjaxController extends Controller
{
    public $success = 200;
    public $error = 400;
    public $exception_message = "Something went wrong.";
    public $paginate_count = 6;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('detectLocation', 'autoSearch', 'autoSearchCity', 'search', 'userResult');
    }

    public function detectLocation(Request $request)
    {
        $cityname = Location::get('1C-3E-84-5D-E8-5D')->cityName;

        if (!empty($request->get('location'))) {
            if (strpos($request->get('location'), ',') !== false) {
                $split = explode(', ', $request->get('location'));
                $location['city'] = $split[0];
                $location['state'] = $split[1];
            } else {
                $location['city'] = $request->get('location');
            }
            Session::put('location', $location || []);
            $result = ['status' => $this->success, 'message' => "Location updated.", 'location' => $location];
        } else {
            $location['city'] = $cityname;
            Session::put('location', $location || []);
            $result = ['status' => $this->success, 'message' => "Find Location successfully.", 'location' => $location];
        }

        return Response::json($result);
    }

    public function autoSearch(Request $request)
    {
        $data['search'] = $request->get('search');
        $name = $request->get('search');
        $location = $request->get('location');
        try {

            $data['doctors'] = User::with('detail')->whereHas('role', function ($q) {
                                        $q->where('keyword', 'doctor');
                                    })
                                    ->where('id', '!=', Auth::id() ? Auth::id() : 0)
                                    ->where('as_doctor_verified', 2)
                                    ->Where('name', 'LIKE', '%' . $request->get('search') . '%')
                                    ->Where('city', 'LIKE', '%' . $location . '%')
                                    ->inRandomOrder()
                                    ->limit(10)
                                    ->get();

            $data['clinics'] = User::with('detail')->whereHas('role', function ($q) {
                                        $q->where('keyword', 'clinic');
                                    })
                                    ->where('id', '!=', Auth::id() ? Auth::id() : 0)
                                    ->where('name', 'LIKE', '%' . $request->get('search') . '%')
                                    ->Where('city', 'LIKE', '%' . $location . '%')
                                    ->inRandomOrder()
                                    ->limit(10)
                                    ->get();

            $data['hospitals'] = User::with('detail')->whereHas('role', function ($q) {
                                        $q->where('keyword', 'hospital');
                                    })
                                    ->where('id', '!=', Auth::id() ? Auth::id() : 0)
                                    ->where('name', 'LIKE', '%' . $request->get('search') . '%')
                                    ->Where('city', 'LIKE', '%' . $location . '%')
                                    ->inRandomOrder()
                                    ->limit(10)
                                    ->get();

            $data['diagnostics'] = User::with('detail')->whereHas('role', function ($q) {
                                        $q->where('keyword', 'diagnostics');
                                    })
                                    ->where('id', '!=', Auth::id() ? Auth::id() : 0)
                                    ->Where('city', 'LIKE', '%' . $location . '%')
                                    ->Where(function($query) use ($request, $location, $name){
                                        $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
                                        $query->orWhereHas('services', function ($q) use ($name) {
                                                $q->where('name', 'LIKE', '%' . $name . '%');
                                        });
                                    })
                                    ->inRandomOrder()
                                    ->limit(10)
                                    ->get();

            $data['specialities'] = Specialty::where('title', 'LIKE', '%' . $request->get('search') . '%')->get();

            $html = view('front.ajax._autoSearch', $data)->render();
            $result = ['status' => $this->success, 'message' => "search successfully.", 'html' => $html];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }

        return Response::json($result);
    }

    public function autoSearchCity(Request $request)
    {
        try {
            $city = '';
            if ($request->get('city')) {
                $city = City::where('name', 'LIKE', '%' . $request->get('city') . '%')->get()->map(function ($city) {
                    $city->with_state = $city->name . ', ' . $city->state->name;
                    return $city;
                });
            }

            $html = '';
            if (!empty($city)) {
                $is_exist = 1;
                foreach ($city as $c) {
                    $html .= '<li class="list-item">';
                    $html .= '<a href="javascript:;" class="menu-list" data-value="' . $c->with_state . '" onclick="selectCity(\'' . $c->with_state . '\')">';
                    $html .= '<span class="text-muted role-tag">' . $c->with_state . '</span>';
                    $html .= '</a>';
                    $html .= '</li>';
                }
            } else {
                $is_exist = 0;
                $html .= '<li class="list-item">';
                $html .= '<span class="text-muted role-tag">No City Found</span>';
                $html .= '</li>';
            }

            $result = ['status' => $this->success, 'message' => "search successfully.", 'html' => $html, 'is_exist' => $is_exist];
        } catch (Exception $e) {
            $result = ['status' => $this->error, 'message' => $this->exception_message];
        }
        return Response::json($result);
    }

    public function search(Request $request, User $user)
    {

        $autoString = '';
        $data = $request->all();
        $data['specialist'] = Specialty::select('title', 'id')->inRandomOrder()->limit(5)->get();

        $users = $user->newQuery();

        /* Auth user can't search as self */
        if (Auth::id()) {
            $users = $users->where('id', '!=', Auth::id());
        }

        /* Admin can't show in search list */
        $users = $users->whereHas('role', function ($role) {
            $role->whereNotIn('keyword', ['admin']);
        });

        /* Filter with speciality */
        if (!empty($request->get('speciality'))) {
            $users = $users->whereHas('detail', function ($detail) use ($request) {
                $detail->whereRaw("find_in_set(" . $request->get('speciality') . ",specialty_ids)");
            });

            $autoString .= ': <strong>' . $request->get('search') . '</strong>';
        }

        if (!empty($request->get('keyword'))) {

            Session::put('search_keyword', $request->get('keyword'));

            $users->where(function ($query) use ($request) {
                $query->where("name", 'LIKE', "%" . $request->get('keyword') . "%");
            });
        }

        if (!empty($request->get('type'))) {
            $users->where(function ($query) use ($request) {
                $query->whereHas('role', function ($role) use ($request) {
                    $role->where('keyword', $request->get('type'));
                });
                if ($request->get('type') == 'doctor') {
                    $query->where('as_doctor_verified', 2);
                }
            });

            if ($request->get('keyword')) {
                $autoString .= ': <strong>' . $request->get('type') . ' search with ' . $request->get('keyword') . '</strong>';
            } else {
                $autoString .= ': <strong>' . $request->get('type') . '</strong>';
            }
        }

        if (!empty($request->get('gender'))) {
            $users->where(function ($query) use ($request) {
                $query->whereIn('gender', $request->get('gender'));
                $query->orwhereHas('staff.user', function ($object) use ($request) {
                    $object->where('gender', $request->get('gender'));
                    $object->whereHas('role', function ($role) {
                        $role->whereIn('keyword', ['doctor']);
                    });
                });
            });
        }

        if (!empty($request->get('consult_fee'))) {
            $fees = explode('#', $request->get('consult_fee'));

            $users->where(function ($query) use ($fees) {

                $query->whereHas('practice', function ($object) use ($fees) {
                    $object->where('fees', '>=', (int)$fees[0]);
                    $object->where('fees', '<=', (int)$fees[1]);
                });
                $query->orwhereHas('staff.practice', function ($object) use ($fees) {

                    $object->where('fees', '>=', (int)$fees[0]);
                    $object->where('fees', '<=', (int)$fees[1]);
                    $object->whereHas('doctor.role', function ($role) {
                        $role->whereIn('keyword', ['doctor']);
                    });
                });
            });
        }

        if (!empty($request->get('consult_as'))) {

            if (in_array("BOTH", $request->get('consult_as'))) {
                $as = ["ONLINE", "INPERSON", "BOTH"];
            } else {
                $as = $request->get('consult_as');
                $as[] = 'BOTH';
            }

            $users->where(function ($query) use ($request, $as) {
                $query->whereHas('setting', function ($setting) use ($as) {
                    $setting->whereIn('consultant_as', $as);
                });
                $query->orwhereHas('staff.user.setting', function ($object) use ($as) {
                    $object->whereIn('consultant_as', $as);
                });
            });
        }

        if ($request->get('search_by') == 'paging') {
            $data['render_product'] = 1;
        }

        $users->with(['practice']);
        $users->orderBy('id', 'DESC');

        $users = $users->paginate($this->paginate_count);
        if ($users->count() > 0) {
            $string = $users->firstItem() . "-" . $users->lastItem() . " of " . $users->total() . " results for " . $autoString;
            Session::put('search_string', $string);
            $data['users'] = $users;
            $data['last_page'] = $users->lastPage();
        } else {
            $string = $users->total() . " results for " . $autoString;
            Session::put('search_string', $string);
            $data['last_page'] = 0;
        }

        if ($request->ajax()) {
            $renderHtml = view('front.ajax._load_result')->with($data)->render();
            return Response::json(['status' => 'success', 'search_string' => $string, 'html' => $renderHtml, 'last_page' => $data['last_page']]);
        }
        return view('front.ajax._global_search')->with($data);
    }

    public function userResult($id, $type, $search)
    {
        if ($type == 'user') {
            $data['user'] = User::find($id);
            $data['specialist'] = Specialty::select('title', 'id')->inRandomOrder()->limit(5)->get();
            $data['search'] = $search;
            return view("front.user.show")->with($data);
        } else {
            if ($type == 'specialty') {
                $data['users'] = User::with('detail')
                    ->whereHas('detail', function ($q) use ($id) {
                        $q->whereRaw("find_in_set($id,specialty_ids)");
                    })->get();
            } elseif ($type == 'doctor') {
                $data['users'] = User::with('detail')->whereHas('role', function ($q) {
                    $q->where('keyword', 'doctor');
                })->Where('name', 'LIKE', '%' . $search . '%')->where('as_doctor_verified', 2)
                    ->get();
            } elseif ($type == 'hospital') {
                $data['users'] = User::with('detail')->whereHas('role', function ($q) {
                    $q->where('keyword', 'hospital');
                })->Where('name', 'LIKE', '%' . $search . '%')
                    ->get();
            } else {
                $data['users'] = User::with('detail')->whereHas('role', function ($q) {
                    $q->where('keyword', 'clinic');
                })->Where('name', 'LIKE', '%' . $search . '%')
                    ->get();
            }
            $data['search'] = $search;
            $data['specialist'] = Specialty::select('title', 'id')->inRandomOrder()->limit(5)->get();
            return view("front.user.filter_data")->with($data);
        }
    }

    public function manageWishlist(Request $request)
    {
        $rules = [
            'doctor_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $result = ["status" => $this->error, "message" => $validator];
        } else {
            try {
                $input = $request->all();
                $input['user_id'] = Auth::id();

                $isUserExists = Wishlist::where('user_id', '=', Auth::id())->where('doctor_id', '=', $request->get('doctor_id'))->exists();
                if (!$isUserExists) {
                    Wishlist::create($input);
                    $html = '<i class="fas fa-star" data-toggle="tooltip" title="Remove from favorite"></i>';
                    $result = ['status' => $this->success, 'message' => 'Doctor add in favorite', 'html' => $html];
                } else {
                    Wishlist::where('user_id', '=', Auth::id())->where('doctor_id', '=', $request->get('doctor_id'))->delete();
                    $html = '<i class="far fa-star" data-toggle="tooltip" title="Add to favorite"></i>';
                    $result = ['status' => $this->success, 'message' => 'Doctor remove from favorite', 'id' => $request->get('doctor_id'), 'html' => $html];
                }
            } catch (Exception $e) {
                $result = ["status" => $this->error, "message" => "Something went wrong. Please try again."];
            }
        }
        return Response::json($result);
    }
}
