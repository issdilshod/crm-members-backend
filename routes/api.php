<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SicCodeController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {  return $request->user(); });

/*
|--------------------------------------------------------------------------
| Helper Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/sic_code', [SicCodeController::class, 'index']);
//Route::post('api/sic_code', 'SicCodeController@store');
Route::get('api/state', [StateController::class, 'index']);
//Route::post('api/state', 'StateController@store');
Route::get('api/hosting', [HostingController::class, 'index']);
Route::post('api/hosting', [HostingController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Account Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/department', [DepartmentController::class, 'index']);
Route::get('api/department/{uuid}', [DepartmentController::class, 'show']);
//Route::post('api/department', 'DepartmentController@store');

Route::get('api/user', [UserController::class, 'index']);
Route::post('api/user', [UserController::class, 'store']);
Route::get('api/user/{uuid}', [UserController::class, 'show']);
Route::put('api/user/{uuid}', [UserController::class, 'update']);
Route::delete('api/user/{uuid}', [UserController::class, 'destroy']);

Route::get('api/role', [RoleController::class, 'index']);
//Route::post('api/role', 'RoleController@store');

Route::get('api/activity', [ActivityController::class, 'index']);
//Route::post('api/activity', 'ActivityController@store');
Route::get('api/activity/{uuid}', [ActivityController::class, 'show']);
Route::put('api/activity/{uuid}', [ActivityController::class, 'update']);
Route::delete('api/activity/{uuid}', [ActivityController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Director Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/director', [DirectorController::class, 'index']);
Route::post('api/director', [DirectorController::class, 'store']);
Route::get('api/director/{uuid}', [DirectorController::class, 'show']);
Route::put('api/director/{uuid}', [DirectorController::class, 'update']);
Route::delete('api/director/{uuid}', [DirectorController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Company Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/company', [CompanyController::class, 'index']);
Route::post('api/company', [CompanyController::class, 'store']);
Route::get('api/company/{uuid}', [CompanyController::class, 'show']);
Route::put('api/company/{uuid}', [CompanyController::class, 'update']);
Route::delete('api/company/{uuid}', [CompanyController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Task Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/task', [TaskController::class, 'index']);
Route::post('api/task', [TaskController::class, 'store']);
Route::get('api/task/{uuid}', [TaskController::class, 'show']);
Route::put('api/task/{uuid}', [TaskController::class, 'update']);
Route::delete('api/task/{uuid}', [TaskController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Note Group
|--------------------------------------------------------------------------
|
*/
//Route::get('api/note', [TaskController::class, 'index']);
Route::post('api/note', [TaskController::class, 'store']);
Route::get('api/note/{uuid}', [TaskController::class, 'show']);
Route::put('api/note/{uuid}', [TaskController::class, 'update']);
//Route::delete('api/note/{uuid}', [TaskController::class, 'destroy']);