<?php

declare(strict_types=1);

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

Route::get('nova/logout', static function (): RedirectResponse {
    return redirect('logout');
})->name('nova.logout');

Route::middleware('auth.cas.force')->group(static function (): void {
    Route::get('/', 'DashboardController@index')->name('home');

    Route::get('sums', 'SUMSController@index');

    Route::view('recruiting', 'recruiting/form');

    Route::get('profile', static function (): View {
        // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
        return view('users/userprofile', ['id' => auth()->user()->id]);
    });

    Route::prefix('dues')->group(static function (): void {
        Route::get('/', static function () {
            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            return true === auth()->user()->is_active ? response()->view('dues.alreadypaid', [], 400) : view(
                'dues/payDues'
            );
        })->name('payDues');

        Route::get('/pay', 'PaymentController@storeUser')->name('dues.payOne');
        Route::post('/pay', 'PaymentController@storeUser')->name('dues.pay');
    });

    Route::prefix('teams')->name('teams.')->group(static function (): void {
        Route::get('/', 'TeamController@indexWeb')->name('index');
    });

    Route::prefix('resume')->name('resume.')->group(static function (): void {
        Route::get('/', static function (): View {
            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            return view('users/resumeupload', ['id' => auth()->user()->id]);
        })->name('index');
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

Route::get('attendance/export/{secret}', 'AttendanceExportController@show')
    ->middleware('auth.cas.force')
    ->name('attendance.export');

Route::get('login', static function (): RedirectResponse {
    return redirect()->intended();
})->name('login')->middleware('auth.cas.force');

Route::get('logout', static function (): void {
    Session::flush();
    cas()->logout(config('app.url'));
})->name('logout');

Route::view('privacy', 'privacy');
