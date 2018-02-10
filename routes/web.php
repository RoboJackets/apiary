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


Route::group(['middleware' => 'auth.cas.force'], function () {
    Route::get('/', 'DashboardController@index')->name('home');

    Route::get('faset', function () {
        return view('faset/faset');
    });

    Route::get('profile', function () {
        return view('users/userprofile', ['id' => auth()->user()->id]);
    });

    Route::prefix('dues')->group(function () {
        Route::get('/', function () {
            return view('dues/payDues');
        })->name('payDues');
        
        Route::get('/pay', 'PaymentController@storeUser')->name('dues.payOne');
        Route::post('/pay', 'PaymentController@storeUser')->name('dues.pay');
    });

    Route::prefix('payments')->group(function () {
        Route::get('/complete', 'PaymentController@handleSquareResponse')
            ->name('payments.complete');
    });

    Route::get('login', function () {
        return redirect('https://login.gatech.edu/cas/logout?service=' . config('app.url'));
    })->name('logout');

    Route::prefix('admin')->group(function () {
        Route::prefix('faset')->group(function () {
            Route::get('/', function () {
                return view('faset/fasetadmin');
            })->name('fasetAdmin');

            Route::get('{id}', function ($id) {
                return view('faset/fasetedit', ['id' => $id]);
            })->name('fasetEdit');
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
    });
});

Route::get('/events/{event}/rsvp', 'RsvpController@oneClickCreate')->middleware('auth.cas.check');

Route::get('login', function () {
    return redirect()->intended();
})->name('login')->middleware('auth.cas.force');

Route::get('logout', function () {
    Session::flush();
    cas()->logout(config("app.url"));
})->name('logout');
