<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Support\Facades\Config;

class Activity extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'entity_uuid', 'device', 'ip', 'description', 'changes', 'action_code', 'status'];

    protected $attributes = ['status' => 1];

    public function user(){
        return $this->belongsTo(User::class, 'user_uuid', 'uuid')
                                                            ->where('status', Config::get('common.status.actived'));
    }
}
