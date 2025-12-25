<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuzzApiMockController;
use App\Http\Controllers\ClickUpController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocuSignController;
use App\Http\Controllers\DuesTransactionController;
use App\Http\Controllers\GitHubController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\RemoteAttendanceController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\SquareCheckoutController;
use App\Http\Controllers\SUMSController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TravelAssignmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use OpenIDConnect\Laravel\JwksController;

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

    Route::get('profile', [UserController::class, 'showProfile'])->name('profile');

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

    Route::prefix('sign')->group(static function (): void {
        Route::get('/travel', [DocuSignController::class, 'signTravel'])->name('docusign.travel');
        Route::get('/agreement', [DocuSignController::class, 'signAgreement'])->name('docusign.agreement');
        Route::get('/complete', [DocuSignController::class, 'complete'])->name('docusign.complete');

        Route::get('/auth/global', [DocuSignController::class, 'redirectGlobalToProvider'])
            ->middleware('can:authenticate-with-docusign')
            ->name('docusign.auth.global');

        Route::get('/auth/user', [DocuSignController::class, 'redirectUserToProvider'])
            ->middleware('can:access-nova')
            ->name('docusign.auth.user');

        Route::get(
            '/auth/deeplink/{resource}/{resourceId}',
            [DocuSignController::class, 'redirectUserToProviderDeepLink']
        )
            ->middleware('can:access-nova')
            ->name('docusign.auth.deeplink');

        Route::get('/auth/complete', [DocuSignController::class, 'handleProviderCallback'])
            ->name('docusign.auth.complete');
    });

    Route::get('github', [GitHubController::class, 'redirectToProvider']);
    Route::get('github/callback', [GitHubController::class, 'handleProviderCallback']);

    Route::get('google', [GoogleController::class, 'redirectToProvider']);
    Route::get('google/callback', [GoogleController::class, 'handleProviderCallback']);

    Route::get('clickup', [ClickUpController::class, 'index']);

    Route::get('travel', [TravelAssignmentController::class, 'index'])->name('travel.index');

    Route::redirect('admin', '/nova');
    Route::redirect('nova/login', '/nova');

    Route::get('login/cas', [AuthController::class, 'forceCasAuth'])
        ->name('login.cas');

    Route::get('stop-impersonating', [AuthController::class, 'stopImpersonating'])->name('stopImpersonating');
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

Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::view('privacy', 'privacy');

if (config('features.sandbox-mode') === true) {
    Route::post('apiv3/{resource}/{action}', [BuzzApiMockController::class, 'anything']);
}

Route::get('oauth/authorize', [AuthorizationController::class, 'authorize'])
    ->name('passport.authorizations.authorize')
    ->middleware('auth');

Route::get('/.well-known/openid-configuration', [OAuthController::class, 'showOpenIdConfiguration']);
