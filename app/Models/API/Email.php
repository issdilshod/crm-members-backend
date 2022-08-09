<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Email extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'hosting_uuid', 'email', 'password', 'phone', 'status'];
}
