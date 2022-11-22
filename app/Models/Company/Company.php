<?php

namespace App\Models\Company;

use App\Models\Account\Activity;
use App\Models\Director\Director;
use App\Models\Helper\Address;
use App\Models\Helper\BankAccount;
use App\Models\Helper\Email;
use App\Models\Helper\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;

class Company extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'legal_name', 'sic_code_uuid', 'director_uuid', 'incorporation_date', 'incorporation_state_uuid', 'incorporation_state_name', 'doing_business_in_state_uuid', 'doing_business_in_state_name', 'ein', 'business_number', 'business_number_type', 'voip_provider', 'voip_login', 'voip_password', 'business_mobile_number', 'business_mobile_number_type', 'business_mobile_number_provider', 'business_mobile_number_login', 'business_mobile_number_password', 'website', 'db_report_number', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function bank_account(){
        return $this->hasMany(BankAccount::class, 'entity_uuid', 'uuid');
    }

    public function files(){
        return $this->hasMany(File::class, 'entity_uuid', 'uuid')
                    ->where('status', '!=', Config::get('common.status.deleted'));
    }

    public function emails(){
        return $this->hasMany(Email::class, 'entity_uuid', 'uuid')
                    ->where('status', '!=', Config::get('common.status.deleted'));
    }

    public function addresses(){
        return $this->hasMany(Address::class, 'entity_uuid', 'uuid')->orderBy('address_parent')
                    ->where('status', '!=', Config::get('common.status.deleted'));
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'director_uuid', 'uuid');
    }

    public function last_accepted(): HasOne
    {
        return $this->hasOne(Activity::class, 'entity_uuid', 'uuid')
                    ->orderBy('created_at', 'DESC')
                    ->where('action_code', Config::get('common.activity.codes.company_accept'));
    }

    public function last_rejected(): HasOne
    {
        return $this->hasOne(Activity::class, 'entity_uuid', 'uuid')
                    ->orderBy('created_at', 'DESC')
                    ->where('action_code', Config::get('common.activity.codes.company_reject'));
    }
}
