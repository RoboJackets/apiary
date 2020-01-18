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

    Route::prefix('admin')->group(static function (): void {
        Route::prefix('recruiting')->group(static function (): void {
            Route::view('/', 'recruiting/recruitingadmin')->name('recruitingAdmin');

            Route::get('{id}', static function ($id): View {
                return view('recruiting/recruitingedit', ['id' => $id]);
            })->name('recruitingEdit');
        });

        Route::prefix('users')->group(static function (): void {
            Route::view('/', 'users/useradmin')->name('usersAdmin');

            Route::get('{id}', static function ($id): View {
                return view('users/useredit', ['id' => $id]);
            })->name('userEdit');
        });

        Route::prefix('events')->group(static function (): void {
            Route::view('/', 'events.indexAdmin')->name('events.indexAdmin');

            Route::view('new', 'events.create')->name('events.create');

            Route::get('{id}', static function ($id): View {
                return view('events.edit', ['id' => $id]);
            })->name('events.edit');
        });

        Route::prefix('dues')->group(static function (): void {
            Route::view('/', 'dues/duesadmin')->name('duesAdmin');

            Route::view('/pending', 'dues/pendingduesadmin')->name('pendingDuesAdmin');

            Route::get('{id}', static function ($id): View {
                return view('dues/duestransaction', [
                    'id' => $id,
                    // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
                    'perms' => auth()->user()->getAllPermissions()->pluck('name')->all(),
                ]);
            })->name('duesTransaction');
        });

        Route::prefix('swag')->group(static function (): void {
            Route::view('/', 'swag.swagIndex')->name('swag.index');
            Route::view('pending', 'swag.swagPending')->name('swag.pending');
            Route::get('{id}', static function ($id): View {
                return view('swag.swagTransaction', ['id' => $id]);
            })->name('swag.transaction');
        });

        Route::prefix('teams')->name('admin.teams.')->group(static function (): void {
            Route::view('/', 'teams.indexAdmin')->name('index');

            Route::view('create', 'teams.create')->name('create');

            Route::get('{id}', static function ($id): View {
                return view('teams.edit', ['id' => $id]);
            })->name('edit');
        });

        Route::prefix('attendance')->group(static function (): void {
            Route::view('/', 'attendance.admin')->name('attendance.admin');
        });

        Route::prefix('notification')->name('admin.notification.')->group(static function (): void {
            // Templates
            Route::prefix('templates')->name('templates.')->group(static function (): void {
                Route::view('/', 'notification.templates.index')->name('index');

                Route::view('create', 'notification.templates.create')->name('create');

                Route::get('{id}', static function ($id): View {
                    return view('notification.templates.edit', ['id' => $id]);
                })->name('edit');
            });
        });
    });

    Route::get('github', 'GitHubController@redirectToProvider');
    Route::get('github/callback', 'GitHubController@handleProviderCallback');

    Route::get('google', 'GoogleController@redirectToProvider');
    Route::get('google/callback', 'GoogleController@handleProviderCallback');

    Route::get('clickup', 'ClickUpController@index');
});

Route::get('/events/{event}/rsvp', 'RsvpController@storeUser')->middleware('auth.cas.check')->name('events.rsvp');

Route::view('attendance/kiosk', 'attendance.kiosk')->name('attendance.kiosk');

Route::get('login', static function (): RedirectResponse {
    return redirect()->intended();
})->name('login')->middleware('auth.cas.force');

Route::get('logout', static function (): void {
    Session::flush();
    cas()->logout(config('app.url'));
})->name('logout');

Route::view('privacy', 'privacy');
