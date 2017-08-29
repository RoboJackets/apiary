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
                return view('users/useredit');
            })->name('userEdit');
        });
    });
});

Route::get('logout', function () {
    cas()->logout(config("app.url"));
});
