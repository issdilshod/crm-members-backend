<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Support\Facades\Config;

class Company extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'legal_name', 'sic_code_uuid', 'director_uuid', 'incorporation_state_uuid', 'incorporation_state_name', 'doing_business_in_state_uuid', 'doing_business_in_state_name', 'ein', 'business_number', 'business_number_type', 'voip_provider', 'voip_login', 'voip_password', 'business_mobile_number_provider', 'business_mobile_number_login', 'business_mobile_number_password', 'website', 'db_report_number', 'status'];

    protected $attributes = ['status' => 1];

    public function bank_account(){
        return $this->hasMany(BankAccount::class, 'entity_uuid', 'uuid')
                                                            ->where('status', Config::get('common.status.actived'));
    }

    public function files(){
        return $this->hasMany(File::class, 'entity_uuid', 'uuid')
                                                            ->where('status', Config::get('common.status.actived'));
    }

    public function emails(){
        return $this->hasMany(Email::class, 'entity_uuid', 'uuid')
                                                        ->where('status', Config::get('common.status.actived'));
    }

    public function addresses(){
        return $this->hasMany(Address::class, 'entity_uuid', 'uuid')
                                                            ->where('status', Config::get('common.status.actived'));
    }
}
