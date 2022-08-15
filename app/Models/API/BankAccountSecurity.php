<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class BankAccountSecurity extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'question', 'answer', 'status'];

    protected $attributes = ['status' => 1];

    public function bank_account(){
        return $this->belongsTo(BankAccount::class, 'entity_uuid', 'uuid');
    }

}
