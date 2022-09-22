<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Support\Facades\Config;

class BankAccount extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'name', 'website', 'username', 'password', 'account_number', 'routing_number', 'status'];

    protected $attributes = ['status' => 1];

    public function bank_account_security(){
        return $this->hasMany(BankAccountSecurity::class, 'entity_uuid', 'uuid')
                        ->orderBy('created_at', 'ASC')
                        ->where('status', Config::get('common.status.actived'));
    }
}
