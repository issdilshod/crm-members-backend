<?php

namespace App\Http\Controllers;

use App\Services\TelegramUserService;

class TelegramUserController extends Controller
{

    public function index(TelegramUserService $telegramUserService){

        $f = fopen('uploads/telegram.txt', 'w');
        fwrite($f, '123');
        fclose($f);

        $updates = $telegramUserService->getUpdates();

        

        $entity = $telegramUserService->getEntity($updates['message']);

        $telegramUserService->createTelegramUser($entity);

        $msg_response = $telegramUserService->getResponse($entity);

        $telegramUserService->sendResponse($entity, $msg_response);
    }

}
