<?php

namespace App\Models\Company;

use App\Models\Director\Director;
use App\Models\Helper\Address;
use App\Models\Helper\BankAccount;
use App\Models\Helper\Email;
use App\Models\Helper\File;
use App\Models\Helper\RegisterAgent;
use App\Models\Helper\RejectReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;

class Company extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'is_active', 'legal_name', 'sic_code_uuid', 'director_uuid', 'incorporation_date', 'incorporation_state_uuid', 'incorporation_state_name', 'doing_business_in_state_uuid', 'doing_business_in_state_name', 'ein', 'business_number', 'business_number_type', 'voip_provider', 'voip_login', 'voip_password', 'business_mobile_number', 'business_mobile_number_type', 'business_mobile_provider', 'business_mobile_website', 'business_mobile_login', 'business_mobile_password', 'card_on_file', 'card_last_four_digit', 'card_holder_name', 'website', 'db_report_number', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function bank_account(){
        return $this->hasOne(BankAccount::class, 'entity_uuid', 'uuid');
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

    public function address(): HasOne{
        return $this->hasOne(Address::class, 'entity_uuid', 'uuid')
                    ->where('address_parent', 'address');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'director_uuid', 'uuid');
    }

    public function incorporations(): HasMany{
        return $this->hasMany(CompanyIncorporation::class, 'entity_uuid', 'uuid');
    }

    public function register_agents(): HasMany{
        return $this->hasMany(RegisterAgent::class, 'entity_uuid', 'uuid');
    }

    public function business_mobiles(): HasMany
    {
        return $this->hasMany(CompanyBusinessMobile::class, 'entity_uuid', 'uuid')
                        ->where('status', '!=', Config::get('common.status.deleted'));
    }

    public function reject_reason(): HasOne
    {
        return $this->hasOne(RejectReason::class, 'entity_uuid', 'uuid')
                    ->latest();
    }
}
