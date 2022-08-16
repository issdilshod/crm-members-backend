<?php

use App\Http\Controllers\SicCodeController;
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
Route::get('api/sic_code', 'SicCodeController@index');
Route::post('api/sic_code', 'SicCodeController@store');

Route::get('api/state', 'StateController@index');
Route::post('api/state', 'StateController@store');

Route::get('api/hosting', 'HostingController@index');
Route::post('api/hosting', 'HostingController@store');

/*
|--------------------------------------------------------------------------
| Account Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/department', 'DepartmentController@index');
Route::post('api/department', 'DepartmentController@store');

Route::get('api/user', 'UserController@index');
Route::post('api/user', 'UserController@store');
Route::get('api/user/{uuid}', 'UserController@show');
Route::put('api/user/{uuid}', 'UserController@update');
Route::delete('api/user/{uuid}', 'UserController@delete');

Route::get('api/role', 'RoleController@index');
Route::post('api/role', 'RoleController@store');

Route::get('api/activity', 'ActivityController@index');
//Route::post('api/activity', 'ActivityController@store');
Route::get('api/activity/{uuid}', 'ActivityController@show');
Route::put('api/activity/{uuid}', 'ActivityController@update');
Route::delete('api/activity/{uuid}', 'ActivityController@delete');

/*
|--------------------------------------------------------------------------
| Director Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/director', 'DirectorController@index');
Route::post('api/director', 'DirectorController@store');
Route::get('api/director/{uuid}', 'DirectorController@show');
Route::put('api/director/{uuid}', 'DirectorController@update');
Route::delete('api/director/{uuid}', 'DirectorController@delete');

/*
|--------------------------------------------------------------------------
| Company Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/company', 'CompanyController@index');
Route::post('api/company', 'CompanyController@store');
Route::get('api/company/{uuid}', 'CompanyController@show');
Route::put('api/company/{uuid}', 'CompanyController@update');
Route::delete('api/company/{uuid}', 'CompanyController@delete');

/*
|--------------------------------------------------------------------------
| Task Group
|--------------------------------------------------------------------------
|
*/
Route::get('api/task', 'TaskController@index');
Route::post('api/task', 'TaskController@store');
Route::get('api/task/{uuid}', 'TaskController@show');
Route::put('api/task/{uuid}', 'TaskController@update');
Route::delete('api/task/{uuid}', 'TaskController@delete');
