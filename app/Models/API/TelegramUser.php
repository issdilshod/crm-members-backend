<?php

namespace App\Models\API;

use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['telegram_id', 'is_bot', 'first_name', 'last_name', 'username', 'language_code', 'status'];

    protected $attributes = ['status' => 1];
}
