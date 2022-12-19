<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use App\Models\Account\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'partner_uuid', 'name', 'type', 'status'];

    protected $attributes = ['status' => 1];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function messages(): HasMany {
        return $this->hasMany(Message::class, 'chat_uuid', 'uuid')->where('status', Config::get('common.status.actived'));
    }

    public function last_message() {
        $user_uuid = request('user_uuid');
        return Message::select('messages.*')
                        ->orderBy('messages.created_at', 'DESC')
                        ->leftJoin('chat_users', 'chat_users.chat_uuid', '=', 'messages.chat_uuid')
                        ->where('chat_users.user_uuid', $user_uuid)
                        ->whereColumn('messages.created_at', '>', 'chat_users.updated_at')
                        ->where('messages.status', '!=', Config::get('common.status.deleted'))
                        ->where('messages.chat_uuid', $this->uuid)
                        ->groupBy('messages.uuid')
                        ->first();
    }
}
