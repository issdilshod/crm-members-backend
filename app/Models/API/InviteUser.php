<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class InviteUser extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['via', 'unique_identify', 'entry_token', 'status'];

    protected $attributes = ['status' => 1];
}
