<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('/sign-up', 'AuthController@signUp');
    Route::post('/sign-in', 'AuthController@signIn');
    Route::post('/sign-out', 'AuthController@signOut');
});

Route::group([], function () {
    Route::post('/profile', 'ProfileController@me');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
