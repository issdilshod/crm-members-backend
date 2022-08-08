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
Route::post('api/activity', 'ActivityController@store');
Route::get('api/activity/{uuid}', 'ActivityController@show');
Route::put('api/activity/{uuid}', 'ActivityController@update');
Route::delete('api/activity/{uuid}', 'ActivityController@delete');
