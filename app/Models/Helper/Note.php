<?php

namespace App\Models\Helper;

use App\Models\Account\User;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'text', 'status'];

    protected $attributes = ['status' => 1];

    public function user(){
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
