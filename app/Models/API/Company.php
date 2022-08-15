<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Company extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'legal_name', 'sic_code_uuid', 'director_uuid', 'incorporation_state_uuid', 'incorporation_state_name', 'doing_business_in_state_uuid', 'doing_business_in_state_name', 'ein', 'phone_type', 'phone_number', 'website', 'db_report_number', 'status'];

    protected $attributes = ['status' => 1];

    public function bank_account(){
        return $this->hasMany(BankAccount::class, 'entity_uuid', 'uuid')
                                                            ->where('status', 1);
    }

    public function files(){
        return $this->hasMany(File::class, 'entity_uuid', 'uuid')
                                                            ->where('status', 1);
    }

    public function emails(){
        return $this->hasMany(Email::class, 'entity_uuid', 'uuid')
                                                        ->where('status', 1);
    }

    public function addresses(){
        return $this->hasMany(Address::class, 'entity_uuid', 'uuid')
                                                            ->where('status', 1);
    }
}
