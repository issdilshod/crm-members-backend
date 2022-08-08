<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Role extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['role_name', 'status'];
}
