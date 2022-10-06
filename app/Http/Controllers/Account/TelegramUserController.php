<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\Account\TelegramUserService;

class TelegramUserController extends Controller
{

    private $telegramUserService;

    public function __construct()
    {
        $this->telegramUserService = new TelegramUserService();
    }

    public function index()
    {
        $this->telegramUserService->init();
    }

}
