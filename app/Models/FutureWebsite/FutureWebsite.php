<?php

namespace App\Models\FutureWebsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class FutureWebsite extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'sic_code_uuid', 'link', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];
}