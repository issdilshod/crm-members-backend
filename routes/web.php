<?php

use App\Http\Controllers\Account\ActivityController;
use App\Http\Controllers\Account\InviteUserController;
use App\Http\Controllers\Account\PermissionController;
use App\Http\Controllers\Account\TelegramUserController;
use App\Http\Controllers\Account\UserController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\FutureCompanyController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Director\DirectorController;
use App\Http\Controllers\FutureWebsite\FutureWebsiteController;
use App\Http\Controllers\Helper\DepartmentController;
use App\Http\Controllers\Helper\FileController;
use App\Http\Controllers\Helper\HostingController;
use App\Http\Controllers\Helper\NoteController;
use App\Http\Controllers\Helper\PendingController;
use App\Http\Controllers\Helper\RoleController;
use App\Http\Controllers\Helper\SicCodeController;
use App\Http\Controllers\Helper\StateController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\VirtualOffice\VirtualOfficeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.custom')->group(function() {
    // helpers
    Route::resource('api/sic_code', SicCodeController::class);
    Route::resource('api/state', StateController::class);
    Route::resource('api/hosting', HostingController::class);
    Route::resource('api/department', DepartmentController::class);
    Route::resource('api/role', RoleController::class);

    Route::get('api/pending', [PendingController::class, 'index']);
    Route::get('api/pending/search', [PendingController::class, 'search']);
    Route::post('api/pending/accept', [PendingController::class, 'accept']);
    Route::post('api/pending/reject', [PendingController::class, 'reject']);
    Route::get('api/pending/duplicate', [PendingController::class, 'duplicate']);
    Route::get('api/pending/users', [PendingController::class, 'users']);

    Route::post('api/file-upload', [FileController::class, 'upload']);

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
    Route::post('api/director-pending', [DirectorController::class, 'pending']);
    Route::put('api/director-pending-update/{uuid}', [DirectorController::class, 'pending_update']);
    Route::put('api/director-accept/{uuid}', [DirectorController::class, 'accept']);
    Route::put('api/director-reject/{uuid}', [DirectorController::class, 'reject']);
    Route::get('api/director-permission', [DirectorController::class, 'permission']);
    Route::get('api/director-list/{search?}', [DirectorController::class, 'director_list']);
    Route::get('api/director-get/{uuid}', [DirectorController::class, 'director_get']);
    Route::put('api/director-override/{uuid}', [DirectorController::class, 'override']);
    Route::get('api/director-unlink/{uuid}', [DirectorController::class, 'unlink']);

    // companies
    Route::resource('api/company', CompanyController::class);
    Route::get('api/company', [CompanyController::class, 'index']);
    Route::get('api/company/{uuid}', [CompanyController::class, 'show']);
    Route::post('api/company', [CompanyController::class, 'store']);
    Route::put('api/company/{uuid}', [CompanyController::class, 'update']);
    Route::delete('api/company/{uuid}', [CompanyController::class, 'destroy']);
    Route::post('api/company-pending', [CompanyController::class, 'pending']);
    Route::put('api/company-pending-update/{uuid}', [CompanyController::class, 'pending_update']);
    Route::put('api/company-accept/{uuid}', [CompanyController::class, 'accept']);
    Route::put('api/company-reject/{uuid}', [CompanyController::class, 'reject']);
    Route::get('api/company-permission', [CompanyController::class, 'permission']);
    Route::get('api/company-list/{search?}', [CompanyController::class, 'company_list']);
    Route::put('api/company-override/{uuid}', [CompanyController::class, 'override']);
    Route::get('api/company-by-director/{uuid}', [CompanyController::class, 'by_director']);

    // websites future
    Route::resource('api/future-websites', FutureWebsiteController::class);
    Route::get('api/future-websites', [FutureWebsiteController::class, 'index']);
    Route::get('api/future-websites/{uuid}', [FutureWebsiteController::class, 'show']);
    Route::post('api/future-websites', [FutureWebsiteController::class, 'store']);
    Route::put('api/future-websites/{uuid}', [FutureWebsiteController::class, 'update']);
    Route::delete('api/future-websites/{uuid}', [FutureWebsiteController::class, 'destroy']);
    Route::get('api/future-websites-search/{search}', [FutureWebsiteController::class, 'search']);
    Route::post('api/future-websites-pending', [FutureWebsiteController::class, 'pending']);
    Route::put('api/future-websites-pending-update/{uuid}', [FutureWebsiteController::class, 'pending_update']);
    Route::put('api/future-websites-accept/{uuid}', [FutureWebsiteController::class, 'accept']);
    Route::put('api/future-websites-reject/{uuid}', [FutureWebsiteController::class, 'reject']);
    Route::get('api/future-websites-permission', [FutureWebsiteController::class, 'permission']);

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

    // future company
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
    Route::get('api/chat-permission', [ChatController::class, 'permission']);
    Route::resource('api/message', MessageController::class);
    Route::get('api/chat-messages/{chat_uuid}', [MessageController::class, 'by_chat']);
    Route::post('api/message', [MessageController::class, 'store']);
    Route::get('api/message/{uuid}', [MessageController::class, 'show']);
    Route::put('api/message/{uuid}', [MessageController::class, 'update']);
    Route::delete('api/message/{uuid}', [MessageController::class, 'destroy']);
    Route::get('api/chat-users', [ChatController::class, 'users']);

    // tasks
    Route::resource('api/task', TaskController::class);
    Route::get('api/task', [TaskController::class, 'index']);
    Route::get('api/task/{uuid}', [TaskController::class, 'show']);
    Route::post('api/task', [TaskController::class, 'store']);
    Route::put('api/task/{uuid}', [TaskController::class, 'update']);
    Route::delete('api/task/{uuid}', [TaskController::class, 'destroy']);
    Route::get('api/task-permission', [TaskController::class, 'permission']);
    Route::put('api/task-progress/{uuid}', [TaskController::class, 'to_progress']);
    Route::get('api/task-comment/{taskUuid}', [TaskController::class, 'comments']);
    Route::put('api/task-approve/{uuid}', [TaskController::class, 'approve']);
    Route::put('api/task-reject/{uuid}', [TaskController::class, 'reject']);

    // contact
    Route::resource('api/contact', ContactController::class);
    Route::get('api/contact', [ContactController::class, 'index']);
    Route::get('api/contact/{uuid}', [ContactController::class, 'show']);
    Route::post('api/contact', [ContactController::class, 'store']);
    Route::put('api/contact/{uuid}', [ContactController::class, 'update']);
    Route::delete('api/contact/{uuid}', [ContactController::class, 'destroy']);
    Route::get('api/contact-search/{search}', [ContactController::class, 'search']);
    Route::post('api/contact-pending', [ContactController::class, 'pending']);
    Route::put('api/contact-pending-update/{uuid}', [ContactController::class, 'pending_update']);
    Route::put('api/contact-accept/{uuid}', [ContactController::class, 'accept']);
    Route::put('api/contact-reject/{uuid}', [ContactController::class, 'reject']);
    Route::get('api/contact-permission', [ContactController::class, 'permission']);


    // notes
    Route::resource('api/note', NoteController::class);
    Route::get('api/note_by_user', [NoteController::class, 'show_by_user']);
});

Route::post('api/login', [UserController::class, 'login']);

Route::post('api/invite-check-token', [InviteUserController::class, 'check_token']);
Route::post('api/register', [UserController::class, 'register']);

// Telegram hook
Route::post('api/telegram-hook', [TelegramUserController::class, 'index']);

// Websocket hook
Route::post('api/websocket-hook', [UserController::class, 'websocket_hook']);