<?php

namespace App\Models\Helper;

use App\Models\Account\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Support\Facades\Config;

class Department extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['department_name', 'status'];

    protected $attributes = ['status' => 1];

    public function users(){
        return $this->hasMany(User::class, 'department_uuid', 'uuid')
                                                            ->where('status', Config::get('common.status.actived'));
    }
}
