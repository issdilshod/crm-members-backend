<?php

use App\Http\Controllers\Account\ActivityController;
use App\Http\Controllers\Account\InviteUserController;
use App\Http\Controllers\Account\TelegramUserController;
use App\Http\Controllers\Account\UserController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Director\DirectorController;
use App\Http\Controllers\Helper\DepartmentController;
use App\Http\Controllers\Helper\HostingController;
use App\Http\Controllers\Helper\NoteController;
use App\Http\Controllers\Helper\RoleController;
use App\Http\Controllers\Helper\SicCodeController;
use App\Http\Controllers\Helper\StateController;
use App\Http\Controllers\Task\TaskController;
use Illuminate\Support\Facades\Route;

Route::resource('api/sic_code', SicCodeController::class)->middleware('auth.custom');
Route::resource('api/state', StateController::class)->middleware('auth.custom');
Route::resource('api/hosting', HostingController::class)->middleware('auth.custom');

Route::resource('api/department', DepartmentController::class)->middleware('auth.custom');
Route::resource('api/role', RoleController::class)->middleware('auth.custom');
// Login
Route::resource('api/user', UserController::class)->middleware('auth.custom');
Route::get('api/is_auth', [UserController::class, 'is_auth'])->middleware('auth.custom');
Route::post('api/login', [UserController::class, 'login']);
Route::post('api/logout', [UserController::class, 'logout'])->middleware('auth.custom');
Route::get('api/get_me', [UserController::class, 'get_me'])->middleware('auth.custom');
// Activity
Route::get('api/activity', [ActivityController::class, 'index'])->middleware('auth.custom');
Route::get('api/activity/user/{uuid}', [ActivityController::class, 'by_user'])->middleware('auth.custom');
Route::get('api/activity/entity/{uuid}', [ActivityController::class, 'by_entity'])->middleware('auth.custom');

Route::resource('api/director', DirectorController::class)->middleware('auth.custom');
Route::get('api/director', [DirectorController::class, 'index'])->middleware('auth.custom');
Route::get('api/director/{uuid}', [DirectorController::class, 'show'])->middleware('auth.custom');
Route::post('api/director', [DirectorController::class, 'store'])->middleware('auth.custom');
Route::put('api/director/{uuid}', [DirectorController::class, 'update'])->middleware('auth.custom');
Route::delete('api/director/{uuid}', [DirectorController::class, 'destroy'])->middleware('auth.custom');
Route::get('api/director-search/{search}', [DirectorController::class, 'search'])->middleware('auth.custom');
Route::post('api/director-pending', [DirectorController::class, 'pending'])->middleware('auth.custom');
Route::put('api/director-pending-update/{uuid}', [DirectorController::class, 'pending_update'])->middleware('auth.custom');
Route::get('api/director-accept/{uuid}', [DirectorController::class, 'accept'])->middleware('auth.custom');
Route::post('api/director-reject/{uuid}', [DirectorController::class, 'reject'])->middleware('auth.custom');

Route::resource('api/company', CompanyController::class)->middleware('auth.custom');
Route::get('api/company-search/{search}', [CompanyController::class, 'search'])->middleware('auth.custom');

Route::resource('api/task', TaskController::class)->middleware('auth.custom');

Route::post('api/invite-via-email', [InviteUserController::class, 'via_email'])->middleware('auth.custom');
Route::post('api/invite-via-telegram', [InviteUserController::class, 'via_telegram'])->middleware('auth.custom');
Route::post('api/invite-check-token', [InviteUserController::class, 'check_token']);
Route::post('api/invite-register', [UserController::class, 'invite_register']);
Route::get('api/pending-users', [UserController::class, 'pending_users']);

Route::post('api/telegram-hook', [TelegramUserController::class, 'index']);

Route::resource('api/note', NoteController::class)->middleware('auth.custom');
Route::get('api/note_by_user', [NoteController::class, 'show_by_user'])->middleware('auth.custom');
