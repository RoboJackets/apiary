<?php

declare(strict_types=1);

use App\User;
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

Route::middleware('auth:api')->get('v1/user', static function (Request $request): User {
    return $request->user();
});

Route::prefix('v1/')->name('api.v1.')->middleware('auth.token', 'auth.cas.force')->group(
    static function (): void {
        // Recruiting (Formerly known as FASET)
        Route::prefix('recruiting')->name('recruiting.')->group(static function (): void {
            Route::post('/', 'RecruitingVisitController@store')->name('store');
            Route::get('/', 'RecruitingVisitController@index')->name('index');
            Route::resource(
                'campaigns/{campaign}/recipients',
                'RecruitingCampaignRecipientController'
            )->except('create', 'edit');
            Route::get('campaigns/{id}/queue', 'RecruitingCampaignController@queue')->name('campaigns.queue');
            Route::resource('campaigns', 'RecruitingCampaignController')->except('create', 'edit');
            Route::get('dedup', 'RecruitingVisitController@dedup')->name('dedup');
            Route::get('{id}', 'RecruitingVisitController@show')->name('show');
            Route::put('{id}', 'RecruitingVisitController@update')->name('update');
        });

        // Notifications
        Route::prefix('notification')->name('notification.')->group(static function (): void {
            Route::get('send', 'NotificationController@sendNotification')->name('send');
            Route::post('manual', 'NotificationController@sendNotificationManual')->name('manual');
            Route::resource('templates', 'NotificationTemplateController')->except('create', 'edit');
        });

        // Misc Resources
        Route::post('attendance/search', 'AttendanceController@search')->name('attendance.search');
        Route::get('attendance/statistics', 'AttendanceController@statistics')->name('attendance.statistics');
        Route::resource('attendance', 'AttendanceController')->except('create', 'edit');
        Route::get('users/search', 'UserController@search');
        Route::resource('users', 'UserController')->except('create', 'edit');
        Route::get('users/{id}/resume', 'ResumeController@show');
        Route::post('users/{id}/resume', 'ResumeController@store');
        Route::delete('users/{id}/resume', 'ResumeController@delete');
        Route::get('resumebooks/{tag}', 'ResumeBookController@show')->name('resumebook.show');
        Route::get('attendancereports/{hash}', 'AttendanceReportController@show')->name('attendancereport.show');
        Route::resource('events', 'EventController')->except('create', 'edit');
        Route::resource('rsvps', 'RsvpController')->except('create', 'edit');
        Route::resource('payments', 'PaymentController')->except('create', 'edit');

        // Dues Packages
        Route::get('dues/packages/active', 'DuesPackageController@indexActive');
        Route::get('dues/packages/available', 'DuesPackageController@indexAvailable');
        Route::resource('dues/packages', 'DuesPackageController')->except('create', 'edit');

        // Dues Transactions
        Route::get('dues/transactions/paid', 'DuesTransactionController@indexPaid');
        Route::get('dues/transactions/pending', 'DuesTransactionController@indexPending');
        Route::get('dues/transactions/pendingSwag', 'DuesTransactionController@indexPendingSwag');
        Route::resource('dues/transactions', 'DuesTransactionController')->except('create', 'edit');

        // Roles + Permissions
        Route::post('roles/{id}/assign', 'RoleController@assign');
        Route::resource('roles', 'RoleController', ['edit']);
        Route::resource('permissions', 'PermissionController', ['edit']);

        // Teams
        Route::get('teams/{id}/members', 'TeamController@showMembers')->name('teams.show.members');
        Route::post('teams/{id}/members', 'TeamController@updateMembers')->name('teams.update.members');
        Route::resource('teams', 'TeamController')->except('create', 'edit');
    }
);
