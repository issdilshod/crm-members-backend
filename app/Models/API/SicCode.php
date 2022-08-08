<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SicCode extends Model
{
    use HasFactory;
    use \App\Traits\TraitUuid;

    protected $fillable = ['code', 'office', 'industry_title', 'status'];
}
