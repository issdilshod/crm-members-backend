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
use App\Http\Controllers\InviteUserController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TelegramUserController;

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
Route::post('api/logout', [UserController::class, 'logout'])->middleware('auth.custom');
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
Route::resource('api/director', DirectorController::class)->middleware('auth.custom');

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

/*
|--------------------------------------------------------------------------
| Invite user via Routes
|--------------------------------------------------------------------------
|
*/
Route::post('api/invite-via-email', [InviteUserController::class, 'via_email'])->middleware('auth.custom');
Route::post('api/invite-via-telegram', [InviteUserController::class, 'via_telegram'])->middleware('auth.custom');
Route::post('api/invite-check-token', [InviteUserController::class, 'check_token']);
Route::post('api/invite-register', [UserController::class, 'invite_register']);
Route::get('api/pending-users', [UserController::class, 'pending_users']);

Route::post('api/telegram-hook', [TelegramUserController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Invite user via Routes
|--------------------------------------------------------------------------
|
*/
Route::resource('api/note', NoteController::class)->middleware('auth.custom');
Route::get('api/note_by_user', [TaskController::class, 'show_by_user'])->middleware('auth.custom');