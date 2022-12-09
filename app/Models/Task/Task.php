<?php

namespace App\Models\Task;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;

class Task extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'task_name', 'due_date', 'description', 'priority', 'progress', 'status'];

    protected $attributes = ['progress' => 1, 'status' => 1];

    public function users(): HasMany
    {
        return $this->hasMany(TaskToUser::class, 'task_uuid')
                    ->where('status', Config::get('common.status.actived'));
    }

}
