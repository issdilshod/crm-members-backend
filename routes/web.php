<?php

use App\Http\Controllers\Account\ActivityController;
use App\Http\Controllers\Account\InviteUserController;
use App\Http\Controllers\Account\PermissionController;
use App\Http\Controllers\Account\TelegramUserController;
use App\Http\Controllers\Account\UserController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Director\DirectorController;
use App\Http\Controllers\Helper\DepartmentController;
use App\Http\Controllers\Helper\HostingController;
use App\Http\Controllers\Helper\NoteController;
use App\Http\Controllers\Helper\PendingController;
use App\Http\Controllers\Helper\RoleController;
use App\Http\Controllers\Helper\SicCodeController;
use App\Http\Controllers\Helper\StateController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\VirtualOffice\VirtualOfficeController;
use App\Http\Controllers\WebsitesFuture\WebsitesFutureController;
use Illuminate\Support\Facades\Route;

// helpers
Route::resource('api/sic_code', SicCodeController::class)->middleware('auth.custom');
Route::resource('api/state', StateController::class)->middleware('auth.custom');
Route::resource('api/hosting', HostingController::class)->middleware('auth.custom');
Route::resource('api/department', DepartmentController::class)->middleware('auth.custom');
Route::resource('api/role', RoleController::class)->middleware('auth.custom');
Route::get('api/pending', [PendingController::class, 'by_user'])->middleware('auth.custom');
Route::get('api/pending/search/{search}', [PendingController::class, 'search'])->middleware('auth.custom');

// account
Route::resource('api/user', UserController::class)->middleware('auth.custom');
Route::get('api/user', [UserController::class, 'index'])->middleware('auth.custom');
Route::post('api/user', [UserController::class, 'store'])->middleware('auth.custom');
Route::get('api/user/{uuid}', [UserController::class, 'show'])->middleware('auth.custom');
Route::put('api/user/{uuid}', [UserController::class, 'update'])->middleware('auth.custom');
Route::delete('api/user/{uuid}', [UserController::class, 'destroy'])->middleware('auth.custom');
Route::get('api/pending-users', [UserController::class, 'pending_users'])->middleware('auth.custom');
Route::get('api/user-online', [UserController::class, 'online'])->middleware('auth.custom');
Route::get('api/user-offline', [UserController::class, 'offline'])->middleware('auth.custom');
//login
Route::post('api/login', [UserController::class, 'login']);
Route::post('api/logout', [UserController::class, 'logout'])->middleware('auth.custom');
Route::get('api/get_me', [UserController::class, 'get_me'])->middleware('auth.custom');
Route::get('api/is_auth', [UserController::class, 'is_auth'])->middleware('auth.custom');
//invite
Route::post('api/invite-via-email', [InviteUserController::class, 'via_email'])->middleware('auth.custom');
Route::post('api/invite-via-telegram', [InviteUserController::class, 'via_telegram'])->middleware('auth.custom');
//register
Route::post('api/invite-check-token', [InviteUserController::class, 'check_token']);
Route::post('api/register', [UserController::class, 'register']);
Route::put('api/user/accept/{uuid}', [UserController::class, 'accept'])->middleware('auth.custom');
Route::put('api/user/reject/{uuid}', [UserController::class, 'reject'])->middleware('auth.custom');

// permissions
Route::get('api/permission', [PermissionController::class, 'index'])->middleware('auth.custom');
Route::get('api/permission-department/{uuid}', [PermissionController::class, 'by_department'])->middleware('auth.custom');
Route::get('api/permission-user/{uuid}', [PermissionController::class, 'by_user'])->middleware('auth.custom');
Route::post('api/permission-department', [PermissionController::class, 'department'])->middleware('auth.custom');
Route::post('api/permission-user', [PermissionController::class, 'user'])->middleware('auth.custom');

// activities
Route::get('api/activity', [ActivityController::class, 'index'])->middleware('auth.custom');
Route::get('api/activity/user/{uuid}', [ActivityController::class, 'by_user'])->middleware('auth.custom');
Route::get('api/activity/entity/{uuid}', [ActivityController::class, 'by_entity'])->middleware('auth.custom');

// directors
Route::resource('api/director', DirectorController::class)->middleware('auth.custom');
Route::get('api/director', [DirectorController::class, 'index'])->middleware('auth.custom');
Route::get('api/director/{uuid}', [DirectorController::class, 'show'])->middleware('auth.custom');
Route::post('api/director', [DirectorController::class, 'store'])->middleware('auth.custom');
Route::put('api/director/{uuid}', [DirectorController::class, 'update'])->middleware('auth.custom');
Route::delete('api/director/{uuid}', [DirectorController::class, 'destroy'])->middleware('auth.custom');
Route::get('api/director-search/{search}', [DirectorController::class, 'search'])->middleware('auth.custom');
Route::post('api/director-pending', [DirectorController::class, 'pending'])->middleware('auth.custom');
Route::put('api/director-pending-update/{uuid}', [DirectorController::class, 'pending_update'])->middleware('auth.custom');
Route::put('api/director-accept/{uuid}', [DirectorController::class, 'accept'])->middleware('auth.custom');
Route::put('api/director-reject/{uuid}', [DirectorController::class, 'reject'])->middleware('auth.custom');
Route::get('api/director-user', [DirectorController::class, 'by_user'])->middleware('auth.custom');
Route::get('api/director-permission', [DirectorController::class, 'permission'])->middleware('auth.custom');
Route::get('api/director-list/{search?}', [DirectorController::class, 'director_list'])->middleware('auth.custom');

// companies
Route::resource('api/company', CompanyController::class)->middleware('auth.custom');
Route::get('api/company', [CompanyController::class, 'index'])->middleware('auth.custom');
Route::get('api/company/{uuid}', [CompanyController::class, 'show'])->middleware('auth.custom');
Route::post('api/company', [CompanyController::class, 'store'])->middleware('auth.custom');
Route::put('api/company/{uuid}', [CompanyController::class, 'update'])->middleware('auth.custom');
Route::delete('api/company/{uuid}', [CompanyController::class, 'destroy'])->middleware('auth.custom');
Route::get('api/company-search/{search}', [CompanyController::class, 'search'])->middleware('auth.custom');
Route::post('api/company-pending', [CompanyController::class, 'pending'])->middleware('auth.custom');
Route::put('api/company-pending-update/{uuid}', [CompanyController::class, 'pending_update'])->middleware('auth.custom');
Route::put('api/company-accept/{uuid}', [CompanyController::class, 'accept'])->middleware('auth.custom');
Route::put('api/company-reject/{uuid}', [CompanyController::class, 'reject'])->middleware('auth.custom');
Route::get('api/company-user/', [CompanyController::class, 'by_user'])->middleware('auth.custom');
Route::get('api/company-permission', [CompanyController::class, 'permission'])->middleware('auth.custom');

// websites future
Route::resource('api/future-websites', WebsitesFutureController::class)->middleware('auth.custom');
Route::get('api/future-websites', [WebsitesFutureController::class, 'index'])->middleware('auth.custom');
Route::get('api/future-websites/{uuid}', [WebsitesFutureController::class, 'show'])->middleware('auth.custom');
Route::post('api/future-websites', [WebsitesFutureController::class, 'store'])->middleware('auth.custom');
Route::put('api/future-websites/{uuid}', [WebsitesFutureController::class, 'update'])->middleware('auth.custom');
Route::delete('api/future-websites/{uuid}', [WebsitesFutureController::class, 'destroy'])->middleware('auth.custom');
Route::get('api/future-websites-search/{search}', [WebsitesFutureController::class, 'search'])->middleware('auth.custom');
Route::post('api/future-websites-pending', [WebsitesFutureController::class, 'pending'])->middleware('auth.custom');
Route::put('api/future-websites-pending-update/{uuid}', [WebsitesFutureController::class, 'pending_update'])->middleware('auth.custom');
Route::put('api/future-websites-accept/{uuid}', [WebsitesFutureController::class, 'accept'])->middleware('auth.custom');
Route::put('api/future-websites-reject/{uuid}', [WebsitesFutureController::class, 'reject'])->middleware('auth.custom');
Route::get('api/future-websites-permission', [WebsitesFutureController::class, 'permission'])->middleware('auth.custom');

// virtual office
Route::resource('api/virtual-office', VirtualOfficeController::class)->middleware('auth.custom');
Route::get('api/virtual-office', [VirtualOfficeController::class, 'index'])->middleware('auth.custom');
Route::get('api/virtual-office/{uuid}', [VirtualOfficeController::class, 'show'])->middleware('auth.custom');
Route::post('api/virtual-office', [VirtualOfficeController::class, 'store'])->middleware('auth.custom');
Route::put('api/virtual-office/{uuid}', [VirtualOfficeController::class, 'update'])->middleware('auth.custom');
Route::delete('api/virtual-office/{uuid}', [VirtualOfficeController::class, 'destroy'])->middleware('auth.custom');
Route::get('api/virtual-office-search/{search}', [VirtualOfficeController::class, 'search'])->middleware('auth.custom');
Route::post('api/virtual-office-pending', [VirtualOfficeController::class, 'pending'])->middleware('auth.custom');
Route::put('api/virtual-office-pending-update/{uuid}', [VirtualOfficeController::class, 'pending_update'])->middleware('auth.custom');
Route::put('api/virtual-office-accept/{uuid}', [VirtualOfficeController::class, 'accept'])->middleware('auth.custom');
Route::put('api/virtual-office-reject/{uuid}', [VirtualOfficeController::class, 'reject'])->middleware('auth.custom');
Route::get('api/virtual-office-permission', [VirtualOfficeController::class, 'permission'])->middleware('auth.custom');

// tasks
Route::resource('api/task', TaskController::class)->middleware('auth.custom');

// notes
Route::resource('api/note', NoteController::class)->middleware('auth.custom');
Route::get('api/note_by_user', [NoteController::class, 'show_by_user'])->middleware('auth.custom');

// telegram hook
Route::post('api/telegram-hook', [TelegramUserController::class, 'index']);
