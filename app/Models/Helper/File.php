<?php

namespace App\Models\Helper;

use App\Models\Company\Company;
use App\Models\Director\Director;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class File extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'entity_uuid', 'file_name', 'file_path', 'file_parent', 'status'];

    protected $attributes = ['status' => 1];

    public function director(){
        return $this->belongsTo(Director::class, 'entity_uuid', 'uuid');
    }

    public function company(){
        return $this->belongsTo(Company::class, 'entity_uuid', 'uuid');
    }
}
