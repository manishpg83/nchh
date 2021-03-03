<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $route_name = '')
    {
        if(!$route_name){
        }
        $route_name = trim($request->route()->getName()); 
        if(isAuthorize($route_name)){
            return $next($request);
        }

        return abort(404);
        // return response()->view('errors.404');
    }
}
