<?php

namespace App\Models\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class AccountSecurity extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'question', 'answer', 'status'];

    protected $attributes = ['status' => 1];

}
