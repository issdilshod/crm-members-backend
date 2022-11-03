<?php

namespace App\Models\VirtualOffice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class VirtualOffice extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'vo_provider_name', 'vo_provider_domain', 'vo_provider_username', 'vo_provider_password', 'street_address', 'address_line2', 'city', 'state', 'postal', 'country', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];
}
