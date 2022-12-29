<?php

namespace App\Models\Company;

use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBusinessMobile extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'business_mobile_number', 'business_mobile_provider', 'business_mobile_website', 'business_mobile_login', 'business_mobile_password', 'card_on_file', 'card_last_four_digit', 'card_holder_name', 'parent', 'status'];

    protected $attributes = ['status' => 1];
}
