<?php

namespace App\Models\Task;

use App\Models\Account\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class TaskToUser extends Model
{
    use HasFactory, TraitUuid;
    
    protected $fillable = ['task_uuid', 'user_uuid', 'is_group', 'status'];

    protected $attributes = ['status' => 1, 'is_group' => false];

    public function user(){
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
