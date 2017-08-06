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
        return view('faset');
    });

    Route::get('admin/faset', function () {
        return view('fasetadmin');
    })->name('fasetAdmin');
  
    Route::get('api/v1/getToken', 'Auth\APITokenController@getToken');
});

Route::get('logout', function () {
    cas()->logout(config("app.url"));
});
