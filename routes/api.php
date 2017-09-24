<?php

use Illuminate\Http\Request;

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

Route::middleware('jwt.auth')->get('v1/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1/', 'middleware' => ['jwt.auth', 'cas.auth']], function () {
    Route::post('faset', 'FasetVisitController@visit');
    Route::get('faset', 'FasetVisitController@list')->middleware('can:administer');
    Route::get('faset/dedup', 'FasetVisitController@dedup')->middleware('can:administer');
    Route::get('faset/{id}', 'FasetVisitController@show')->middleware('can:administer');
    Route::put('faset/{id}', 'FasetVisitController@update')->middleware('can:administer');
    Route::get('notification/send', 'NotificationController@sendNotification')->middleware('can:administer');
    Route::post('notification/manual', 'NotificationController@sendNotificationManual')->middleware('can:administer');
    Route::middleware('can:administer')->resource('users', 'UserController', ['except' => ['create', 'edit']]);
    Route::middleware('can:administer')->resource('events', 'EventController', ['except' => ['create', 'edit']]);
    Route::middleware('can:administer')->resource('rsvps', 'RsvpController', ['except' => ['create', 'edit']]);
    Route::middleware('can:administer')->resource('payments', 'PaymentController', ['except' => ['create', 'edit']]);
    Route::middleware('can:administer')->get('dues/packages/active', 'DuesPackageController@indexActive');
    Route::get('dues/packages/available', 'DuesPackageController@indexAvailable');
    Route::middleware('can:administer')->resource('dues/packages', 'DuesPackageController', ['except' => ['create', 'edit']]);
    Route::middleware('can:administer')->resource('dues/transactions', 'DuesTransactionController', ['except' => ['create', 'edit']]);
});
