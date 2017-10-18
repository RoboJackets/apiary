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


Route::group(['middleware' => 'cas.auth'], function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('faset', function () {
        return view('faset/faset');
    });

    Route::get('profile', function () {
        return view('users/userprofile', ['id' => auth()->user()->id]);
    });

    Route::get('dues', function () {
        return view('dues/payDues');
    })->name('payDues');

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
                return view('events/eventadmin');
            })->name('eventsAdmin');

            Route::get('{id}', function ($id) {
                return view('events/eventedit', ['id' => $id]);
            })->name('eventEdit');
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
    });
  
    // Use cookie auth to get first token
    Route::get('api/v1/getToken', 'Auth\APITokenController@getToken');
});

Route::get('/events/{event}/rsvp', 'RsvpController@oneClickCreate')->middleware('cas.check');

Route::get('logout', function () {
    Session::flush();
    cas()->logout(config("app.url"));
})->name('logout');
