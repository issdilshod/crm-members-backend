<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use App\Models\API\Director;

class Address extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'street_address', 'address_line_2', 'city', 'state', 'postal', 'country', 'address_parent', 'status'];

    protected $attributes = ['status' => 1];

    public function director(){
        return $this->belongsTo(Director::class, 'entity_uuid', 'uuid');
    }
}
