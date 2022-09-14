<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TelegramUserController extends Controller
{

    public function index(){
        $request = file_get_contents( 'php://input' );
        //$request = json_decode( $request, TRUE );

        Storage::disk('local')->put('uploads/telegram.txt', $request);
    }

}
