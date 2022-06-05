<?php

declare(strict_types=1);

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\DuesPackageController;
use App\Http\Controllers\DuesTransactionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\NovaExportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RsvpController;
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

Route::prefix('v1/')->name('api.v1.')->middleware(['auth:api'])->group(
    static function (): void {
        // Misc Resources
        Route::post('attendance/search', [AttendanceController::class, 'search'])->name('attendance.search');
        Route::get('attendance/statistics', [AttendanceController::class, 'statistics'])->name('attendance.statistics');
        Route::resource('attendance', AttendanceController::class)->except('create', 'edit');
        Route::get('users/search', [UserController::class, 'search']);
        Route::resource('users', UserController::class)->except('create', 'edit');
        Route::post('users/{id}/resume', [ResumeController::class, 'store']);
        Route::get('user', [UserController::class, 'showSelf']);
        Route::get('attendancereports/{hash}', [AttendanceReportController::class, 'show'])
            ->name('attendancereport.show');
        Route::resource('events', EventController::class)->except('create', 'edit');
        Route::resource('rsvps', RsvpController::class)->except('create', 'edit');
        Route::resource('payments', PaymentController::class)->except('create', 'edit');

        // Dues Packages
        Route::get('dues/packages/active', [DuesPackageController::class, 'indexActive']);
        Route::get('dues/packages/available', [DuesPackageController::class, 'indexAvailable']);
        Route::get('dues/packages/purchase', [DuesPackageController::class, 'indexUserCanPurchase']);
        Route::resource('dues/packages', DuesPackageController::class)->except('create', 'edit');

        // Dues Transactions
        Route::get('dues/transactions/paid', [DuesTransactionController::class, 'indexPaid']);
        Route::get('dues/transactions/pending', [DuesTransactionController::class, 'indexPending']);
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

Route::webhooks('/v1/square', 'square');

Route::webhooks('/v1/postmark-outbound', 'postmark-outbound');

Route::get('/v1/info', [InfoController::class, 'show']);

Route::get('/v1/nova/export/{file}', [NovaExportController::class, 'export'])->name('api.v1.nova.export')
    ->middleware(['signed']);
