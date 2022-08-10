<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class SicCode extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['code', 'office', 'industry_title', 'status'];

    protected $attributes = ['status' => 1];
}
