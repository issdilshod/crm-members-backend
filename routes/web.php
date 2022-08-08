<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SicCodeController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityController;

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

/*
|--------------------------------------------------------------------------
| Helper Group Routes
|--------------------------------------------------------------------------
|
*/
Route::resource('api/sic_code', SicCodeController::class);
Route::resource('api/state', StateController::class);
Route::resource('api/hosting', HostingController::class);

/*
|--------------------------------------------------------------------------
| Account Group Routes
|--------------------------------------------------------------------------
|
*/
Route::resource('api/department', DepartmentController::class);
Route::resource('api/user', UserController::class);
Route::resource('api/role', RoleController::class);
Route::resource('api/activity', ActivityController::class);
Route::get('api/activity/user/{uuid}', 'App\Http\Controllers\ActivityController@by_user');
Route::get('api/activity/entity/{uuid}', 'App\Http\Controllers\ActivityController@by_entity');
