<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Message extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['chat_uuid', 'user_uuid', 'message', 'status'];

    protected $attributes = ['status' => 1];

}
