<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class File extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'entity_uuid', 'file_name', 'file_path', 'file_parent', 'status'];
}
