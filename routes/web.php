<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SicCodeController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\TaskController;

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
Route::resource('api/sic_code', SicCodeController::class)->middleware('auth.custom');
Route::resource('api/state', StateController::class)->middleware('auth.custom');
Route::resource('api/hosting', HostingController::class)->middleware('auth.custom');

/*
|--------------------------------------------------------------------------
| Account Group Routes
|--------------------------------------------------------------------------
|
*/
Route::resource('api/department', DepartmentController::class)->middleware('auth.custom');
Route::resource('api/role', RoleController::class)->middleware('auth.custom');
// Login
Route::resource('api/user', UserController::class)->middleware('auth.custom');
Route::get('api/is_auth', [UserController::class, 'is_auth'])->middleware('auth.custom');
Route::post('api/login', [UserController::class, 'login']);
// Activity
Route::resource('api/activity', ActivityController::class);
Route::get('api/activity/user/{uuid}', [ActivityController::class, 'by_user'])->middleware('auth.custom');
Route::get('api/activity/entity/{uuid}', [ActivityController::class, 'by_entity'])->middleware('auth.custom');

/*
|--------------------------------------------------------------------------
| Director Group Routes
|--------------------------------------------------------------------------
|
*/
Route::resource('api/director', DirectorController::class)->middleware('auth.custom')->middleware('cors');

/*
|--------------------------------------------------------------------------
| Company Group Routes
|--------------------------------------------------------------------------
|
*/
Route::resource('api/company', CompanyController::class)->middleware('auth.custom');

/*
|--------------------------------------------------------------------------
| Task Group Routes
|--------------------------------------------------------------------------
|
*/
Route::resource('api/task', TaskController::class)->middleware('auth.custom');
