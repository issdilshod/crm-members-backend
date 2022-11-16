<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;

class Chat extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'partner_uuid', 'name', 'status'];

    protected $attributes = ['status' => 1];

    public function messages(): HasMany {
        return $this->hasMany(Message::class, 'chat_uuid', 'uuid')->where('status', Config::get('common.status.actived'));
    }

    public function last_message(): HasOne {
        return $this->hasOne(Message::class, 'chat_uuid', 'uuid')
                    ->where('status', Config::get('common.status.actived'))
                    ->orderBy('created_at', 'DESC')
                    ->latest();
    }
}
