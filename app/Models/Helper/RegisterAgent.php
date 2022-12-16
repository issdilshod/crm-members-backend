<?php

namespace App\Models\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class RegisterAgent extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'name', 'website', 'login', 'password', 'email', 'phone', 'parent', 'status'];

    protected $attributes = ['status' => 1];

}
