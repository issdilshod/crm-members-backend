<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class UserAccessToken extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'token', 'expires_at'];

    public function user(){
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
