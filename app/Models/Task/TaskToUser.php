<?php

namespace App\Models\Task;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class TaskToUser extends Model
{
    use HasFactory, TraitUuid;
    
    protected $fillable = ['task_uuid', 'user_uuid', 'is_group', 'status'];

    protected $attributes = ['status' => 1, 'is_group' => false];
}
