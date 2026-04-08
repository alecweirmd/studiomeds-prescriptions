<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false, 'verify' => true]);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', 'App\Http\Controllers\DashboardController@index');
    Route::post('/generate_QR', 'App\Http\Controllers\DashboardController@generate_QR');
    Route::get('users/submitted_cqi/{patient_id}', 'App\Http\Controllers\UsersController@show_cqi');
    Route::get('/pdf/artist_medication', 'App\Http\Controllers\DashboardController@generate_med_doc');
    Route::get('/dashboard/approve_patient/{id}', 'App\Http\Controllers\DashboardController@approvePatient');
    Route::get('/dashboard/training', 'App\Http\Controllers\DashboardController@training');
    Route::post('/dashboard/reject_patient/{id}', 'App\Http\Controllers\DashboardController@rejectPatient');
    Route::get('/dashboard/approve_all_patients/', 'App\Http\Controllers\DashboardController@approveAllPatients');
});

Route::get('/register', 'App\Http\Controllers\UsersController@artist_register');
Route::post('users/store_artist', 'App\Http\Controllers\UsersController@store_artist');
Route::get('users/thank_you', 'App\Http\Controllers\UsersController@thank_you');
Route::get('users/client_form/{uuid?}', 'App\Http\Controllers\UsersController@client_form');
Route::post('users/store_patient', 'App\Http\Controllers\UsersController@store_patient');
Route::post('/payment/callback', 'App\Http\Controllers\UsersController@callback');

Route::get('/ajaxStartUser/', 'App\Http\Controllers\UsersController@ajaxStartUser');