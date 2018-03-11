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

Route::middleware('auth:api')->get('v1/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1/', 'as' => 'api.v1.', 'middleware' => ['auth.token', 'auth.cas.force']], function () {
    //FASET
    Route::post('faset', 'FasetVisitController@store');
    Route::get('faset', 'FasetVisitController@index');
    Route::get('faset/dedup', 'FasetVisitController@dedup');
    Route::get('faset/{id}', 'FasetVisitController@show');
    Route::put('faset/{id}', 'FasetVisitController@update');

    //Notifications
    Route::get('notification/send', 'NotificationController@sendNotification');
    Route::post('notification/manual', 'NotificationController@sendNotificationManual');

    //Misc Resources
    Route::resource('attendance', 'AttendanceController', ['except' => ['create', 'edit']]);
    Route::get('users/search', 'UserController@search');
    Route::resource('users', 'UserController', ['except' => ['create', 'edit']]);
    Route::resource('events', 'EventController', ['except' => ['create', 'edit']]);
    Route::resource('rsvps', 'RsvpController', ['except' => ['create', 'edit']]);
    Route::resource('payments', 'PaymentController', ['except' => ['create', 'edit']]);

    //Dues Packages
    Route::get('dues/packages/active', 'DuesPackageController@indexActive');
    Route::get('dues/packages/available', 'DuesPackageController@indexAvailable');
    Route::resource('dues/packages', 'DuesPackageController', ['except' => ['create', 'edit']]);

    //Dues Transactions
    Route::get('dues/transactions/paid', 'DuesTransactionController@indexPaid');
    Route::get('dues/transactions/pending', 'DuesTransactionController@indexPending');
    Route::get('dues/transactions/pendingSwag', 'DuesTransactionController@indexPendingSwag');
    Route::resource('dues/transactions', 'DuesTransactionController', ['except' => ['create', 'edit']]);

    //Roles + Permissions
    Route::post('roles/{id}/assign', 'RoleController@assign');
    Route::resource('roles', 'RoleController', ['except' => 'create', 'edit']);
    Route::resource('permissions', 'PermissionController', ['except' => 'create', 'edit']);

    //Teams
    Route::get('teams/{id}/members', 'TeamController@showMembers')->name('teams.show.members');
    Route::post('teams/{id}/members', 'TeamController@updateMembers')->name('teams.update.members');
    Route::resource('teams', 'TeamController', ['except' => ['create', 'edit']]);
});
