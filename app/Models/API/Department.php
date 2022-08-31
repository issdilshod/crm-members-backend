<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Department extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['department_name', 'status'];

    protected $attributes = ['status' => 1];

    public function users(){
        return $this->hasMany(User::class, 'department_uuid', 'uuid')
                                                            ->where('status', 1);
    }
}
