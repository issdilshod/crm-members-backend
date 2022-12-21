<?php

namespace App\Models\VirtualOffice;

use App\Models\Company\Company;
use App\Models\Director\Director;
use App\Models\Helper\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Support\Facades\Config;

class VirtualOffice extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'vo_signer_uuid', 'vo_provider_name', 'vo_website', 'vo_provider_phone_number', 'vo_contact_person_name', 'vo_contact_person_phone_number', 'vo_contact_person_email', 'vo_contact_person_email', 'online_account', 'online_email', 'online_account_username', 'online_account_password', 'card_on_file', 'autopay', 'card_last_four_digit', 'card_holder_name', 'contract', 'contract_terms', 'contract_terms_notes', 'contract_effective_date', 'monthly_payment_amount', 'agreement_terms', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function addresses(){
        return $this->hasMany(Address::class, 'entity_uuid', 'uuid')->orderBy('address_parent')
                    ->where('status', '!=', Config::get('common.status.deleted'));
    }

    public function director(){
        return $this->belongsTo(Director::class, 'vo_signer_uuid', 'uuid');
    }

    public function company(){
        if ($this->vo_signer_uuid==null){ return null; }
        return Company::where('director_uuid', $this->vo_signer_uuid)
                        ->first(['uuid', 'legal_name']);
    }
}
