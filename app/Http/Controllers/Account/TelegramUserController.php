<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\Account\TelegramUserService;

class TelegramUserController extends Controller
{

    public function index(TelegramUserService $telegramUserService){

        $updates = $telegramUserService->getUpdates();

        $entity = $telegramUserService->getEntity($updates['message']);

        $telegramUserService->createTelegramUser($entity);

        $msg_response = $telegramUserService->getResponse($entity);

        $telegramUserService->sendResponse($entity, $msg_response);
    }

}
