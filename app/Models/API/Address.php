<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Address extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'street_address', 'address_line_2', 'city', 'state', 'postal', 'country', 'status'];
}
