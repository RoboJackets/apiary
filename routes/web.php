<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('nova/logout', 'RedirectController@logout')->name('nova.logout');

Route::middleware('auth.cas.force')->group(static function (): void {
    Route::get('/', 'DashboardController@index')->name('home');

    Route::get('sums', 'SUMSController@index');

    Route::view('recruiting', 'recruiting/form');

    Route::get('profile', 'UserController@showProfile');

    Route::prefix('dues')->group(static function (): void {
        Route::get('/', 'DuesTransactionController@showDuesFlow')->name('payDues');

        Route::get('/pay', 'PaymentController@storeUser')->name('dues.payOne');
        Route::post('/pay', 'PaymentController@storeUser')->name('dues.pay');
    });

    Route::prefix('teams')->name('teams.')->group(static function (): void {
        Route::get('/', 'TeamController@indexWeb')->name('index');
    });

    Route::prefix('resume')->name('resume.')->group(static function (): void {
        Route::get('/', 'ResumeController@showUploadPage')->name('index');
    });

    Route::prefix('payments')->group(static function (): void {
        Route::get('/complete', 'PaymentController@handleSquareResponse')->name('payments.complete');
    });

    Route::get('github', 'GitHubController@redirectToProvider');
    Route::get('github/callback', 'GitHubController@handleProviderCallback');

    Route::get('google', 'GoogleController@redirectToProvider');
    Route::get('google/callback', 'GoogleController@handleProviderCallback');

    Route::get('clickup', 'ClickUpController@index');
});

Route::get('/events/{event}/rsvp', 'RsvpController@storeUser')->middleware('auth.cas.check')->name('events.rsvp');

Route::view('attendance/kiosk', 'attendance.kiosk')->name('attendance.kiosk');

Route::get('attendance/remote/{secret}', 'RemoteAttendanceController@index')
    ->middleware('auth.cas.force')
    ->name('attendance.remote');

Route::get('attendance/remote/{secret}/redirect', 'RemoteAttendanceController@redirect')
    ->middleware('auth.cas.force')
    ->name('attendance.remote.redirect');

Route::get('attendance/export/{secret}', 'AttendanceExportController@show')
    ->middleware('auth.cas.force')
    ->name('attendance.export');

Route::get('login', 'RedirectController@login')->name('login')->middleware('auth.cas.force');

Route::get('logout', 'AuthController@logout')->name('logout');

Route::view('privacy', 'privacy');
