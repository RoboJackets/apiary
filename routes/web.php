<?php

declare(strict_types=1);

// @phan-file-suppress PhanStaticCallToNonStatic

use App\Http\Controllers\AttendanceExportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutodeskLibraryController;
use App\Http\Controllers\ClickUpController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DuesTransactionController;
use App\Http\Controllers\GitHubController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\RemoteAttendanceController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\SquareCheckoutController;
use App\Http\Controllers\SUMSController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TravelAssignmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the 'web' middleware group. Now create something great!
|
*/

Route::middleware('auth.cas.force')->group(static function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('sums', [SUMSController::class, 'index']);

    Route::view('recruiting', 'recruiting/form');

    Route::get('profile', [UserController::class, 'showProfile']);

    Route::prefix('dues')->group(static function (): void {
        Route::get('/', [DuesTransactionController::class, 'showDuesFlow'])->name('showDuesFlow');
    });

    Route::prefix('teams')->name('teams.')->group(static function (): void {
        Route::get('/', [TeamController::class, 'indexWeb'])->name('index');
    });

    Route::prefix('resume')->name('resume.')->group(static function (): void {
        Route::get('/', [ResumeController::class, 'showUploadPage'])->name('index');
    });
    Route::get('users/{id}/resume', [ResumeController::class, 'show'])->name('resume.show');

    Route::prefix('pay')->group(static function (): void {
        Route::get('/dues', [SquareCheckoutController::class, 'payDues'])->name('pay.dues');
        Route::get('/travel', [SquareCheckoutController::class, 'payTravel'])->name('pay.travel');
        Route::get('/complete', [SquareCheckoutController::class, 'complete'])->name('pay.complete');
    });

    Route::get('github', [GitHubController::class, 'redirectToProvider']);
    Route::get('github/callback', [GitHubController::class, 'handleProviderCallback']);

    Route::get('google', [GoogleController::class, 'redirectToProvider']);
    Route::get('google/callback', [GoogleController::class, 'handleProviderCallback']);

    Route::get('clickup', [ClickUpController::class, 'index']);

    Route::get('autodesk', [AutodeskLibraryController::class, 'index']);

    Route::get('agreement/print', [SignatureController::class, 'print'])->name('agreement.print');
    Route::get('agreement/render', [SignatureController::class, 'render'])->name('agreement.render');
    Route::post('agreement/redirect', [SignatureController::class, 'redirect'])->name('agreement.redirect');

    Route::get('travel', [TravelAssignmentController::class, 'index'])->name('travel.index');

    Route::redirect('admin', '/nova');

    Route::get('login/cas', [AuthController::class, 'forceCasAuth'])
        ->name('login.cas');

    Route::view('oauth2/client', 'oauth2clientcreated')->name('oauth2.client.created');
    Route::view('oauth2/pat', 'personalaccesstokencreated')->name('oauth2.pat.created');
});

Route::get('/events/{event}/rsvp', [RsvpController::class, 'storeUser'])
    ->middleware('auth.cas.check')
    ->name('events.rsvp');

Route::view('attendance/kiosk', 'attendance.kiosk')->name('attendance.kiosk');

Route::get('attendance/remote/{secret}', [RemoteAttendanceController::class, 'index'])
    ->middleware('auth.cas.force')
    ->name('attendance.remote');

Route::get('attendance/remote/{secret}/redirect', [RemoteAttendanceController::class, 'redirect'])
    ->middleware('auth.cas.force')
    ->name('attendance.remote.redirect');

Route::get('attendance/export/{secret}', [AttendanceExportController::class, 'show'])
    ->middleware('auth.cas.force')
    ->name('attendance.export');

Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::view('privacy', 'privacy');

Route::get('agreement/complete', [SignatureController::class, 'complete'])->name('agreement.complete');
