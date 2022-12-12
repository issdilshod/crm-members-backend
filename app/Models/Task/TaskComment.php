<?php

namespace App\Models\Task;

use App\Models\Account\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class TaskComment extends Model
{
    use HasFactory, TraitUuid;
    
    protected $fillable = ['user_uuid', 'task_uuid', 'comment', 'status'];

    protected $attributes = ['status' => 1];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
