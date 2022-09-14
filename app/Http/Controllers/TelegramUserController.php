<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramUserController extends Controller
{

    public function index(){
        $request = file_get_contents( 'php://input' );
        //$request = json_decode( $request, TRUE );

        $f = fopen("uploads/test.txt", "w");
        fwrite($f, $request);
        fclose($f);
    }

}
