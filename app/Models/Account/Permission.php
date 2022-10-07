<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Permission extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['permission_name', 'alias', 'status'];

    protected $attributes = ['status' => 1];
}
