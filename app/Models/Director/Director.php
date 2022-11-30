<?php

namespace App\Models\Director;

use App\Models\Account\Activity;
use App\Models\Company\Company;
use App\Models\Helper\Address;
use App\Models\Helper\Email;
use App\Models\Helper\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;

class Director extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'ssn_cpn', 'company_association', 'phone_type', 'phone_number', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function files(){
        return $this->hasMany(File::class, 'entity_uuid', 'uuid')
                        ->where('status', Config::get('common.status.actived'));
    }

    public function emails(){
        return $this->hasMany(Email::class, 'entity_uuid', 'uuid');
    }

    public function addresses(){
        return $this->hasMany(Address::class, 'entity_uuid', 'uuid');
    }

    public function address(): HasOne{
        return $this->hasOne(Address::class, 'entity_uuid', 'uuid')
                    ->where('address_parent', 'credit_home_address');
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'director_uuid', 'uuid')
                    ->where('approved', Config::get('common.status.actived'));
    }

    public function last_accepted(): HasOne
    {
        return $this->hasOne(Activity::class, 'entity_uuid', 'uuid')
                    ->orderBy('created_at', 'DESC')
                    ->where('action_code', Config::get('common.activity.codes.director_accept'));
    }

    public function last_rejected(): HasOne
    {
        return $this->hasOne(Activity::class, 'entity_uuid', 'uuid')
                    ->orderBy('created_at', 'DESC')
                    ->where('action_code', Config::get('common.activity.codes.director_reject'));
    }
}
