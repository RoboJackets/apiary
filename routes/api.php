<?php

declare(strict_types=1);

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\RecruitingCampaignRecipientController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\DuesPackageController;
use App\Http\Controllers\DuesTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecruitingCampaignController;
use App\Http\Controllers\RecruitingVisitController;
use App\Http\Controllers\ResumeBookController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
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

// @phan-suppress-next-line PhanParamTooMany
Route::prefix('v1/')->name('api.v1.')->middleware('auth.token', 'auth.cas.force')->group(
    static function (): void {
        // Recruiting (Formerly known as FASET)
        Route::prefix('recruiting')->name('recruiting.')->group(static function (): void {
            Route::post('/', [RecruitingVisitController::class, 'store'])->name('store');
            Route::get('/', [RecruitingVisitController::class, 'index'])->name('index');
            Route::resource(
                'campaigns/{campaign}/recipients',
                RecruitingCampaignRecipientController::class
            )->except('create', 'edit');
            Route::get('campaigns/{id}/queue', [RecruitingCampaignController::class, 'queue'])->name('campaigns.queue');
            Route::resource('campaigns', RecruitingCampaignController::class)->except('create', 'edit');
            Route::get('dedup', [RecruitingVisitController::class, 'dedup'])->name('dedup');
            Route::get('{id}', [RecruitingVisitController::class, 'show'])->name('show');
            Route::put('{id}', [RecruitingVisitController::class, 'update'])->name('update');
        });

        // Notifications
        Route::prefix('notification')->name('notification.')->group(static function (): void {
            Route::get('send', [NotificationController::class, 'sendNotification'])->name('send');
            Route::post('manual', [NotificationController::class, 'sendNotificationManual'])->name('manual');
            Route::resource('templates', NotificationTemplateController::class)->except('create', 'edit');
        });

        // Misc Resources
        Route::post('attendance/search', [AttendanceController::class, 'search'])->name('attendance.search');
        Route::get('attendance/statistics', [AttendanceController::class, 'statistics'])->name('attendance.statistics');
        Route::resource('attendance', AttendanceController::class)->except('create', 'edit');
        Route::get('users/search', [UserController::class, 'search']);
        Route::resource('users', UserController::class)->except('create', 'edit');
        Route::get('users/{id}/resume', [ResumeController::class, 'show']);
        Route::post('users/{id}/resume', [ResumeController::class, 'store']);
        Route::delete('users/{id}/resume', [ResumeController::class, 'delete']);
        Route::get('resumebooks/{tag}', [ResumeBookController::class, 'show'])->name('resumebook.show');
        Route::get('attendancereports/{hash}', [AttendanceReportController::class, 'show'])->name('attendancereport.show');
        Route::resource('events', EventController::class)->except('create', 'edit');
        Route::resource('rsvps', RsvpController::class)->except('create', 'edit');
        Route::resource('payments', PaymentController::class)->except('create', 'edit');

        // Dues Packages
        Route::get('dues/packages/active', [DuesPackageController::class, 'indexActive']);
        Route::get('dues/packages/available', [DuesPackageController::class, 'indexAvailable']);
        Route::resource('dues/packages', DuesPackageController::class)->except('create', 'edit');

        // Dues Transactions
        Route::get('dues/transactions/paid', [DuesTransactionController::class, 'indexPaid']);
        Route::get('dues/transactions/pending', [DuesTransactionController::class, 'indexPending']);
        Route::get('dues/transactions/pendingSwag', [DuesTransactionController::class, 'indexPendingSwag']);
        Route::resource('dues/transactions', DuesTransactionController::class)->except('create', 'edit');

        // Roles + Permissions
        Route::post('roles/{id}/assign', [RoleController::class, 'assign']);
        Route::resource('roles', RoleController::class, ['edit']);
        Route::resource('permissions', PermissionController::class, ['edit']);

        // Teams
        Route::get('teams/{id}/members', [TeamController::class, 'showMembers'])->name('teams.show.members');
        Route::post('teams/{id}/members', [TeamController::class, 'updateMembers'])->name('teams.update.members');
        Route::resource('teams', TeamController::class)->except('create', 'edit');
    }
);
