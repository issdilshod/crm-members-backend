<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Chat extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'name', 'status'];

    protected $attributes = ['status' => 1];
}
