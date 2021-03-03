<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('login', 'AuthController@login');
Route::post('getOtp', 'AuthController@getOtp');

Route::middleware('auth:api')->group(function () {
	Route::get('user', 'AuthController@details');
});