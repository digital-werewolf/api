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

Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
    // Authenticate
    Route::post('/sign-up', 'AuthController@signUp');
    Route::post('/sign-in', 'AuthController@signIn');
    Route::post('/sign-out', 'AuthController@signOut');

    // OAuth
    Route::group(['middleware' => 'web'], function () {
        Route::get('/{driver}/redirect', 'OAuthController@redirect')->name('oauth.redirect');
        Route::get('/{driver}/callback', 'OAuthController@callback')->name('oauth.callback');
    });

    // Verify email
    Route::post('/send-verification-email', 'EmailVerificationController@sendVerificationEmail');
    Route::get('/verify-email/{id}/{hash}', 'EmailVerificationController@verifyEmail')->name('verification.verify');

    // Reset password
    Route::post('/forgot-password', 'PasswordResettingController@forgotPassword');
    Route::post('/reset-password', 'PasswordResettingController@resetPassword')->name('password.reset');
});

Route::group(['prefix' => 'profile'], function () {
    Route::get('/', 'ProfileController@me');
    Route::put('/username', 'ProfileController@updateUsername');
    Route::put('/email', 'ProfileController@updateEmail');
    Route::put('/password', 'ProfileController@updatePassword');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
