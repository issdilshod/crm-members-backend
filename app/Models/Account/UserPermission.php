<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class UserPermission extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['permission_uuid', 'user_uuid', 'status'];
}
