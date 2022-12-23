<?php

namespace App\Models\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class RejectReason extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'description', 'status'];

    protected $attributes = ['status' => 1];
}
