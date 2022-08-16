<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Task extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'company_uuid', 'due_date', 'description', 'priority', 'progress', 'status'];

    protected $attributes = ['progress' => 1, 'status' => 1];

    public function users(){
        return $this->hasMany(TaskToUser::class, 'task_uuid', 'uuid')
                                                            ->where('status', 1);
    }

    public function files(){
        return $this->hasMany(File::class, 'entity_uuid', 'uuid')
                                                            ->where('status', 1);
    }
}
