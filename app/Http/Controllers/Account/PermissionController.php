<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\PermissionResource;
use App\Models\Account\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::where('status', Config::get('common.status.actived'))->get();
        return PermissionResource::collection($permissions);
    }
}
