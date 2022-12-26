<?php

namespace App\Models\Contact;

use App\Models\Helper\AccountSecurity;
use App\Models\Helper\RejectReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;

class Contact extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'first_name', 'last_name', 'email', 'phone_number', 'company_name', 'company_phone_number', 'company_email', 'company_website', 'online_account', 'account_username', 'account_password', 'security_questions', 'notes', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function account_securities(): HasMany
    {
        return $this->hasMany(AccountSecurity::class, 'entity_uuid', 'uuid')
                    ->where('status', '!=', Config::get('common.status.deleted'));
    }

    public function reject_reason(): HasOne
    {
        return $this->hasOne(RejectReason::class, 'entity_uuid', 'uuid')
                    ->latest();
    }

}
