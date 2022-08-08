<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class User extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['first_name', 'last_name', 'username', 'password', 'telegram', 'status'];
}
