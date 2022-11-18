<?php

namespace App\Models\Chat;

use App\Models\Account\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['chat_uuid', 'user_uuid', 'message', 'status'];

    protected $attributes = ['status' => 1];

    public function user():BelongsTo {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function chat():BelongsTo {
        return $this->belongsTo(Chat::class, 'chat_uuid', 'uuid');
    }

}
