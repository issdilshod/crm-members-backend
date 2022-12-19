<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class ChatUser extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['chat_uuid', 'user_uuid', 'last_seen', 'status'];

    protected $attributes = ['status' => 1];
}
