<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RouteRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*if Auth user are manager,pharmacy and accountant then call and can't show front*/
        if (Auth::user()) {
            if (Auth::user()->role->keyword === 'manager' || Auth::user()->role->keyword === 'accountant' || Auth::user()->role->keyword === 'pharmacy') {
                return redirect()->route('account.show-profile-form');
            }
            if(empty(Auth::user()->locality) || empty(Auth::user()->email)){
                return redirect()->route('account.show-profile-form')->with('warning', "Please fill up your profile details. Such as Email, Address..");
            }
        }

        return $next($request);
    }
}
