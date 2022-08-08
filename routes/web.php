<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SicCodeController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;

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

//Route::get('/', function () { return view('welcome'); });

Route::resource('api/sic_code', SicCodeController::class);

Route::resource('api/state', StateController::class);

Route::resource('api/hosting', HostingController::class);

Route::resource('api/department', DepartmentController::class);

Route::resource('api/user', UserController::class);
