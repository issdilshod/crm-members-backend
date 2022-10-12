<?php

namespace App\Models\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class FutureWebsite extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'domain', 'category', 'status'];

    protected $attributes = ['status' => 1];
}
