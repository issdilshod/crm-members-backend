<?php

namespace App\Models\Company;

use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCreditAccount extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'is_active', 'name', 'website', 'phones', 'username', 'password', 'status'];

    protected $attributes = ['status' => 1];
}
