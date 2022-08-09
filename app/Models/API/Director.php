<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use App\Models\API\File;
use App\Models\API\Email;

class Director extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'ssn_cpn', 'company_association', 'phone_type', 'phone_number', 'status'];

    public function files(){
        return $this->hasMany(File::class);
    }

    public function emails(){
        return $this->hasMany(Email::class);
    }
}
