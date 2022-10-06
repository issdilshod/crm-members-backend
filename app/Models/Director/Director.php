<?php

namespace App\Models\Director;

use App\Models\Helper\Address;
use App\Models\Helper\Email;
use App\Models\Helper\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Support\Facades\Config;

class Director extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'ssn_cpn', 'company_association', 'phone_type', 'phone_number', 'status'];

    protected $attributes = ['status' => 1];

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