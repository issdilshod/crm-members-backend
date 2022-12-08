<?php

namespace App\Models\VirtualOffice;

use App\Models\Helper\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Support\Facades\Config;

class VirtualOffice extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'vo_provider_name', 'vo_provider_username', 'vo_provider_password', 'vo_website', 'vo_contact_person_name', 'vo_contact_person_phone_number', 'vo_contact_person_email', 'vo_contact_person_email', 'online_account', 'online_account_username', 'online_account_password', 'card_on_file', 'card_last_four_digit', 'card_holder_name', 'monthly_payment_amount', 'contract', 'contract_terms', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function addresses(){
        return $this->hasMany(Address::class, 'entity_uuid', 'uuid')->orderBy('address_parent')
                    ->where('status', '!=', Config::get('common.status.deleted'));
    }
}
