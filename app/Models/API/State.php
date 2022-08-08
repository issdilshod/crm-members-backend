<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class State extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['short_name', 'full_name', 'status'];
}
