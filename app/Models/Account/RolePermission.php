<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class RolePermission extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['permission_uuid', 'role_uuid', 'status'];

    protected $attributes = ['status' => 1];
}
