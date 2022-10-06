<?php

namespace App\Models\Account;

use App\Models\Helper\Department;
use App\Models\Helper\Note;
use App\Models\Helper\TaskToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class User extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['department_uuid', 'role_uuid', 'first_name', 'last_name', 'username', 'password', 'telegram', 'status'];

    protected $attributes = ['status' => 1];

    public function department(){
        return $this->belongsTo(Department::class, 'department_uuid', 'uuid')
                                                            ->where('status', Config::get('common.status.actived'));
    }

    public function activities(){
        return $this->hasMany(Activity::class, 'user_uuid', 'uuid')
                                                                ->where('status', Config::get('common.status.actived'))
                                                                ->limit(10);
    }

    public function tasks(){
        return $this->hasMany(TaskToUser::class, 'user_uuid', 'uuid')
                                                            ->where('status', Config::get('common.status.actived'));
    }

    public function access_tokens(){
        return $this->hasMany(UserAccessToken::class, 'user_uuid', 'uuid')
                                                            ->where('expires_at', '>', Carbon::now()->toDateTimeString());
    }

    public function notes(){
        return $this->hasMany(Note::class, 'user_uuid', 'uuid')
                        ->where('status', Config::get('common.status.actived'));
    }
}
