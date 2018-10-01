<?php

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

Route::get('nova/logout', function () {
    return redirect('logout');
})->name('nova.logout');

Route::group(['middleware' => 'auth.cas.force'], function () {
    Route::get('/', 'DashboardController@index')->name('home');

    Route::get('recruiting', function () {
        return view('recruiting/form');
    });

    Route::get('profile', function () {
        return view('users/userprofile', ['id' => auth()->user()->id]);
    });

    Route::prefix('dues')->group(function () {
        Route::get('/', function () {
            if (auth()->user()->is_active) {
                return response()->view('dues.alreadypaid', [], 400);
            } else {
                return view('dues/payDues');
            }
        })->name('payDues');

        Route::get('/pay', 'PaymentController@storeUser')->name('dues.payOne');
        Route::post('/pay', 'PaymentController@storeUser')->name('dues.pay');
    });

    Route::group(['prefix' => 'teams', 'as' => 'teams.'], function () {
        Route::get('/', 'TeamController@indexWeb')->name('index');
    });

    Route::prefix('payments')->group(function () {
        Route::get('/complete', 'PaymentController@handleSquareResponse')
            ->name('payments.complete');
    });

    Route::prefix('admin')->group(function () {
        Route::prefix('recruiting')->group(function () {
            Route::get('/', function () {
                return view('recruiting/recruitingadmin');
            })->name('recruitingAdmin');

            Route::get('{id}', function ($id) {
                return view('recruiting/recruitingedit', ['id' => $id]);
            })->name('recruitingEdit');
        });

        Route::prefix('users')->group(function () {
            Route::get('/', function () {
                return view('users/useradmin');
            })->name('usersAdmin');

            Route::get('{id}', function ($id) {
                return view('users/useredit', ['id' => $id]);
            })->name('userEdit');
        });

        Route::prefix('events')->group(function () {
            Route::get('/', function () {
                return view('events.indexAdmin');
            })->name('events.indexAdmin');

            Route::get('new', function () {
                return view('events.create');
            })->name('events.create');

            Route::get('{id}', function ($id) {
                return view('events.edit', ['id' => $id]);
            })->name('events.edit');
        });

        Route::prefix('dues')->group(function () {
            Route::get('/', function () {
                return view('dues/duesadmin');
            })->name('duesAdmin');

            Route::get('/pending', function () {
                return view('dues/pendingduesadmin');
            })->name('pendingDuesAdmin');

            Route::get('{id}', function ($id) {
                return view('dues/duestransaction', ['id' => $id]);
            })->name('duesTransaction');
        });

        Route::prefix('swag')->group(function () {
            Route::get('/', function () {
                return view('swag.swagIndex');
            })->name('swag.index');
            Route::get('pending', function () {
                return view('swag.swagPending');
            })->name('swag.pending');
            Route::get('{id}', function ($id) {
                return view('swag.swagTransaction', ['id' => $id]);
            })->name('swag.transaction');
        });

        Route::prefix('teams')->name('admin.teams.')->group(function () {
            Route::get('/', function () {
                return view('teams.indexAdmin');
            })->name('index');

            Route::get('create', function () {
                return view('teams.create');
            })->name('create');

            Route::get('{id}', function ($id) {
                return view('teams.edit', ['id' => $id]);
            })->name('edit');
        });

        Route::prefix('attendance')->group(function () {
            Route::get('/', function () {
                return view('attendance.admin');
            })->name('attendance.admin');
        });

        Route::prefix('notification')->name('admin.notification.')->group(function () {

            // Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', function () {
                    return view('notification.templates.index');
                })->name('index');

                Route::get('create', function () {
                    return view('notification.templates.create');
                })->name('create');

                Route::get('{id}', function ($id) {
                    return view('notification.templates.edit', ['id' => $id]);
                })->name('edit');
            });
        });
    });
});

Route::get('/events/{event}/rsvp', 'RsvpController@storeUser')->middleware('auth.cas.check');

Route::get('attendance/kiosk', function () {
    return view('attendance.kiosk');
})->name('attendance.kiosk');

Route::get('login', function () {
    return redirect()->intended();
})->name('login')->middleware('auth.cas.force');

Route::get('logout', function () {
    Session::flush();
    cas()->logout(config('app.url'));
})->name('logout');

Route::get('privacy', function () {
    return view('privacy');
});
