<?php

declare(strict_types=1);

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DuesPackageController;
use App\Http\Controllers\DuesTransactionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\MerchandiseController;
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
        // Attendance
        Route::apiResource('attendance', AttendanceController::class);
        Route::post('attendance/search', [AttendanceController::class, 'search'])->name('attendance.search');
        Route::get('attendance/statistics', [AttendanceController::class, 'statistics'])->name('attendance.statistics');

        // Users
        // The search endpoint MUST be registered before the apiResource, otherwise it will not take precedence
        Route::get('users/search', [UserController::class, 'search']);
        Route::get('users/managers', [UserController::class, 'indexManagers'])->middleware('cache:86400');
        Route::apiResource('users', UserController::class)->middleware('cache:86400');
        Route::post('users/{id}/resume', [ResumeController::class, 'store']);
        Route::get('user', [UserController::class, 'showSelf']);
        Route::post('user/override/self', [UserController::class, 'applySelfOverride']);

        // Miscellany
        Route::apiResource('events', EventController::class);
        Route::apiResource('rsvps', RsvpController::class);
        Route::apiResource('payments', PaymentController::class);
        Route::get('payments/user/{user}', [PaymentController::class, 'indexForUser']);

        // Dues Packages
        Route::get('dues/packages/active', [DuesPackageController::class, 'indexActive']);
        Route::get('dues/packages/available', [DuesPackageController::class, 'indexAvailable']);
        Route::get('dues/packages/purchase', [DuesPackageController::class, 'indexUserCanPurchase']);
        Route::apiResource('dues/packages', DuesPackageController::class);

        // Dues Transactions
        Route::get('dues/transactions/paid', [DuesTransactionController::class, 'indexPaid']);
        Route::get('dues/transactions/pending', [DuesTransactionController::class, 'indexPending']);
        Route::apiResource('dues/transactions', DuesTransactionController::class);

        // Roles + Permissions
        Route::post('roles/{id}/assign', [RoleController::class, 'assign']);
        Route::resource('roles', RoleController::class, ['edit']);
        Route::resource('permissions', PermissionController::class, ['edit']);

        // Teams
        Route::get('teams/{id}/members', [TeamController::class, 'showMembers'])->name('teams.show.members');
        Route::post('teams/{id}/members', [TeamController::class, 'updateMembers'])->name('teams.update.members');
        Route::apiResource('teams', TeamController::class);

        // Merchandise
        Route::get('merchandise', [MerchandiseController::class, 'index']);
        Route::get('merchandise/{merchandise}', [MerchandiseController::class, 'show'])
            ->missing([MerchandiseController::class, 'handleMissingModel']);
        Route::get(
            'merchandise/{merchandise}/distribute/{user:gtid}',
            [MerchandiseController::class, 'getDistribution']
        )
            ->withoutScopedBindings()
            ->missing([MerchandiseController::class, 'handleMissingModel']);
        Route::post(
            'merchandise/{merchandise}/distribute/{user:gtid}',
            [MerchandiseController::class, 'distribute']
        )
            ->withoutScopedBindings()
            ->missing([MerchandiseController::class, 'handleMissingModel']);
    }
);

Route::webhooks('/v1/square', 'square');

Route::webhooks('/v1/postmark/outbound', 'postmark-outbound');

Route::webhooks('/v1/postmark/inbound', 'postmark-inbound');

Route::webhooks('/v1/docusign', 'docusign')
    ->middleware(['signed']);

Route::get('/v1/info', [InfoController::class, 'show']);

Route::get('/v1/nova/export/{file}', [NovaExportController::class, 'export'])->name('api.v1.nova.export')
    ->middleware(['signed']);

Route::get('/v1/calendar', \App\Http\Controllers\CalendarController::class);
