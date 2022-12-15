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
        return Message::where('chat_uuid', $this->uuid)
                    ->where('status', Config::get('common.status.actived'))
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }
}
