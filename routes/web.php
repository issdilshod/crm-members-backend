<?php

use App\Http\Controllers\Account\ActivityController;
use App\Http\Controllers\Account\InviteUserController;
use App\Http\Controllers\Account\PermissionController;
use App\Http\Controllers\Account\TelegramUserController;
use App\Http\Controllers\Account\UserController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\FutureCompanyController;
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

Route::middleware('auth.custom')->group(function() {
    // helpers
    Route::resource('api/sic_code', SicCodeController::class);
    Route::resource('api/state', StateController::class);
    Route::resource('api/hosting', HostingController::class);
    Route::resource('api/department', DepartmentController::class);
    Route::resource('api/role', RoleController::class);
    Route::get('api/pending', [PendingController::class, 'by_user']);
    Route::get('api/pending/search/{search}', [PendingController::class, 'search']);

    // account
    Route::resource('api/user', UserController::class);
    Route::get('api/user', [UserController::class, 'index']);
    Route::post('api/user', [UserController::class, 'store']);
    Route::get('api/user/{uuid}', [UserController::class, 'show']);
    Route::put('api/user/{uuid}', [UserController::class, 'update']);
    Route::delete('api/user/{uuid}', [UserController::class, 'destroy']);
    Route::get('api/pending-users', [UserController::class, 'pending_users']);
    Route::get('api/user-online', [UserController::class, 'online']);
    Route::get('api/user-offline', [UserController::class, 'offline']);
    Route::post('api/logout', [UserController::class, 'logout']);
    Route::get('api/get_me', [UserController::class, 'get_me']);
    Route::get('api/is_auth', [UserController::class, 'is_auth']);

    //invite
    Route::post('api/invite-via-email', [InviteUserController::class, 'via_email']);
    Route::post('api/invite-via-telegram', [InviteUserController::class, 'via_telegram']);

    //register
    Route::put('api/user/accept/{uuid}', [UserController::class, 'accept'])->middleware('auth.custom');
    Route::put('api/user/reject/{uuid}', [UserController::class, 'reject'])->middleware('auth.custom');

    // permissions
    Route::get('api/permission', [PermissionController::class, 'index']);
    Route::get('api/permission-department/{uuid}', [PermissionController::class, 'by_department']);
    Route::get('api/permission-user/{uuid}', [PermissionController::class, 'by_user']);
    Route::post('api/permission-department', [PermissionController::class, 'department']);
    Route::post('api/permission-user', [PermissionController::class, 'user']);

    // activities
    Route::get('api/activity', [ActivityController::class, 'index']);
    Route::get('api/activity/user/{uuid}', [ActivityController::class, 'by_user']);
    Route::get('api/activity/entity/{uuid}', [ActivityController::class, 'by_entity']);

    // directors
    Route::resource('api/director', DirectorController::class);
    Route::get('api/director', [DirectorController::class, 'index']);
    Route::get('api/director/{uuid}', [DirectorController::class, 'show']);
    Route::post('api/director', [DirectorController::class, 'store']);
    Route::put('api/director/{uuid}', [DirectorController::class, 'update']);
    Route::delete('api/director/{uuid}', [DirectorController::class, 'destroy']);
    Route::get('api/director-search/{search}', [DirectorController::class, 'search']);
    Route::post('api/director-pending', [DirectorController::class, 'pending']);
    Route::put('api/director-pending-update/{uuid}', [DirectorController::class, 'pending_update']);
    Route::put('api/director-accept/{uuid}', [DirectorController::class, 'accept']);
    Route::put('api/director-reject/{uuid}', [DirectorController::class, 'reject']);
    Route::get('api/director-user', [DirectorController::class, 'by_user']);
    Route::get('api/director-permission', [DirectorController::class, 'permission']);
    Route::get('api/director-list/{search?}', [DirectorController::class, 'director_list']);

    // companies
    Route::resource('api/company', CompanyController::class);
    Route::get('api/company', [CompanyController::class, 'index']);
    Route::get('api/company/{uuid}', [CompanyController::class, 'show']);
    Route::post('api/company', [CompanyController::class, 'store']);
    Route::put('api/company/{uuid}', [CompanyController::class, 'update']);
    Route::delete('api/company/{uuid}', [CompanyController::class, 'destroy']);
    Route::get('api/company-search/{search}', [CompanyController::class, 'search']);
    Route::post('api/company-pending', [CompanyController::class, 'pending']);
    Route::put('api/company-pending-update/{uuid}', [CompanyController::class, 'pending_update']);
    Route::put('api/company-accept/{uuid}', [CompanyController::class, 'accept']);
    Route::put('api/company-reject/{uuid}', [CompanyController::class, 'reject']);
    Route::get('api/company-user/', [CompanyController::class, 'by_user']);
    Route::get('api/company-permission', [CompanyController::class, 'permission']);

    // websites future
    Route::resource('api/future-websites', WebsitesFutureController::class);
    Route::get('api/future-websites', [WebsitesFutureController::class, 'index']);
    Route::get('api/future-websites/{uuid}', [WebsitesFutureController::class, 'show']);
    Route::post('api/future-websites', [WebsitesFutureController::class, 'store']);
    Route::put('api/future-websites/{uuid}', [WebsitesFutureController::class, 'update']);
    Route::delete('api/future-websites/{uuid}', [WebsitesFutureController::class, 'destroy']);
    Route::get('api/future-websites-search/{search}', [WebsitesFutureController::class, 'search']);
    Route::post('api/future-websites-pending', [WebsitesFutureController::class, 'pending']);
    Route::put('api/future-websites-pending-update/{uuid}', [WebsitesFutureController::class, 'pending_update']);
    Route::put('api/future-websites-accept/{uuid}', [WebsitesFutureController::class, 'accept']);
    Route::put('api/future-websites-reject/{uuid}', [WebsitesFutureController::class, 'reject']);
    Route::get('api/future-websites-permission', [WebsitesFutureController::class, 'permission']);

    // virtual office
    Route::resource('api/virtual-office', VirtualOfficeController::class);
    Route::get('api/virtual-office', [VirtualOfficeController::class, 'index']);
    Route::get('api/virtual-office/{uuid}', [VirtualOfficeController::class, 'show']);
    Route::post('api/virtual-office', [VirtualOfficeController::class, 'store']);
    Route::put('api/virtual-office/{uuid}', [VirtualOfficeController::class, 'update']);
    Route::delete('api/virtual-office/{uuid}', [VirtualOfficeController::class, 'destroy']);
    Route::get('api/virtual-office-search/{search}', [VirtualOfficeController::class, 'search']);
    Route::post('api/virtual-office-pending', [VirtualOfficeController::class, 'pending']);
    Route::put('api/virtual-office-pending-update/{uuid}', [VirtualOfficeController::class, 'pending_update']);
    Route::put('api/virtual-office-accept/{uuid}', [VirtualOfficeController::class, 'accept']);
    Route::put('api/virtual-office-reject/{uuid}', [VirtualOfficeController::class, 'reject']);
    Route::get('api/virtual-office-permission', [VirtualOfficeController::class, 'permission']);

    // virtual office
    Route::resource('api/future-company', FutureCompanyController::class);
    Route::get('api/future-company', [FutureCompanyController::class, 'index']);
    Route::get('api/future-company/{uuid}', [FutureCompanyController::class, 'show']);
    Route::post('api/future-company', [FutureCompanyController::class, 'store']);
    Route::put('api/future-company/{uuid}', [FutureCompanyController::class, 'update']);
    Route::delete('api/future-company/{uuid}', [FutureCompanyController::class, 'destroy']);
    Route::get('api/future-company-search/{search}', [FutureCompanyController::class, 'search']);
    Route::post('api/future-company-pending', [FutureCompanyController::class, 'pending']);
    Route::put('api/future-company-pending-update/{uuid}', [FutureCompanyController::class, 'pending_update']);
    Route::put('api/future-company-accept/{uuid}', [FutureCompanyController::class, 'accept']);
    Route::put('api/future-company-reject/{uuid}', [FutureCompanyController::class, 'reject']);
    Route::get('api/future-company-permission', [FutureCompanyController::class, 'permission']);

    // chats
    Route::resource('api/chat', ChatController::class);
    Route::get('api/chat', [ChatController::class, 'index']);
    Route::get('api/chat/{uuid}', [ChatController::class, 'show']);
    Route::post('api/chat', [ChatController::class, 'store']);
    Route::put('api/chat/{uuid}', [ChatController::class, 'update']);
    Route::delete('api/chat/{uuid}', [ChatController::class, 'destroy']);

    // tasks
    Route::resource('api/task', TaskController::class);

    // notes
    Route::resource('api/note', NoteController::class);
    Route::get('api/note_by_user', [NoteController::class, 'show_by_user']);
});

Route::post('api/login', [UserController::class, 'login']);

Route::post('api/invite-check-token', [InviteUserController::class, 'check_token']);
Route::post('api/register', [UserController::class, 'register']);

// Telegram hook
Route::post('api/telegram-hook', [TelegramUserController::class, 'index']);