<?php declare(strict_types = 1);

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

Route::get('nova/logout', static function () {
    return redirect('logout');
})->name('nova.logout');

Route::group(['middleware' => 'auth.cas.force'], static function (): void {
    Route::get('/', 'DashboardController@index')->name('home');

    Route::get('recruiting', static function () {
        return view('recruiting/form');
    });

    Route::get('profile', static function () {
        return view('users/userprofile', ['id' => auth()->user()->id]);
    });

    Route::prefix('dues')->group(static function (): void {
        Route::get('/', static function () {
            return auth()->user()->is_active ? response()->view('dues.alreadypaid', [], 400) : view('dues/payDues');
        })->name('payDues');

        Route::get('/pay', 'PaymentController@storeUser')->name('dues.payOne');
        Route::post('/pay', 'PaymentController@storeUser')->name('dues.pay');
    });

    Route::group(['prefix' => 'teams', 'as' => 'teams.'], static function (): void {
        Route::get('/', 'TeamController@indexWeb')->name('index');
    });

    Route::prefix('payments')->group(static function (): void {
        Route::get('/complete', 'PaymentController@handleSquareResponse')->name('payments.complete');
    });

    Route::prefix('admin')->group(static function (): void {
        Route::prefix('recruiting')->group(static function (): void {
            Route::get('/', static function () {
                return view('recruiting/recruitingadmin');
            })->name('recruitingAdmin');

            Route::get('{id}', static function ($id) {
                return view('recruiting/recruitingedit', ['id' => $id]);
            })->name('recruitingEdit');
        });

        Route::prefix('users')->group(static function (): void {
            Route::get('/', static function () {
                return view('users/useradmin');
            })->name('usersAdmin');

            Route::get('{id}', static function ($id) {
                return view('users/useredit', ['id' => $id]);
            })->name('userEdit');
        });

        Route::prefix('events')->group(static function (): void {
            Route::get('/', static function () {
                return view('events.indexAdmin');
            })->name('events.indexAdmin');

            Route::get('new', static function () {
                return view('events.create');
            })->name('events.create');

            Route::get('{id}', static function ($id) {
                return view('events.edit', ['id' => $id]);
            })->name('events.edit');
        });

        Route::prefix('dues')->group(static function (): void {
            Route::get('/', static function () {
                return view('dues/duesadmin');
            })->name('duesAdmin');

            Route::get('/pending', static function () {
                return view('dues/pendingduesadmin');
            })->name('pendingDuesAdmin');

            Route::get('{id}', static function ($id) {
                return view('dues/duestransaction', [
                    'id' => $id,
                    'perms' => auth()->user()->getAllPermissions()->pluck('name')->all(),
                ]);
            })->name('duesTransaction');
        });

        Route::prefix('swag')->group(static function (): void {
            Route::get('/', static function () {
                return view('swag.swagIndex');
            })->name('swag.index');
            Route::get('pending', static function () {
                return view('swag.swagPending');
            })->name('swag.pending');
            Route::get('{id}', static function ($id) {
                return view('swag.swagTransaction', ['id' => $id]);
            })->name('swag.transaction');
        });

        Route::prefix('teams')->name('admin.teams.')->group(static function (): void {
            Route::get('/', static function () {
                return view('teams.indexAdmin');
            })->name('index');

            Route::get('create', static function () {
                return view('teams.create');
            })->name('create');

            Route::get('{id}', static function ($id) {
                return view('teams.edit', ['id' => $id]);
            })->name('edit');
        });

        Route::prefix('attendance')->group(static function (): void {
            Route::get('/', static function () {
                return view('attendance.admin');
            })->name('attendance.admin');
        });

        Route::prefix('notification')->name('admin.notification.')->group(static function (): void {
            // Templates
            Route::prefix('templates')->name('templates.')->group(static function (): void {
                Route::get('/', static function () {
                    return view('notification.templates.index');
                })->name('index');

                Route::get('create', static function () {
                    return view('notification.templates.create');
                })->name('create');

                Route::get('{id}', static function ($id) {
                    return view('notification.templates.edit', ['id' => $id]);
                })->name('edit');
            });
        });
    });
});

Route::get('/events/{event}/rsvp', 'RsvpController@storeUser')->middleware('auth.cas.check');

Route::get('attendance/kiosk', static function () {
    return view('attendance.kiosk');
})->name('attendance.kiosk');

Route::get('login', static function () {
    return redirect()->intended();
})->name('login')->middleware('auth.cas.force');

Route::get('logout', static function (): void {
    Session::flush();
    cas()->logout(config('app.url'));
})->name('logout');

Route::get('privacy', static function () {
    return view('privacy');
});
