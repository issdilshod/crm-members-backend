<?php

namespace App\Models\Task;

use App\Models\Account\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskToUser extends Model
{
    use HasFactory;

    protected $fillable = ['task_uuid', 'user_uuid', 'department_uuid', 'group', 'status'];

    protected $attributes = ['status' => 1];

    public function task(){
        return $this->belongsTo(Task::class, 'task_uuid', 'uuid');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
