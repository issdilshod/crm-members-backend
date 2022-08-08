<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Hosting extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['host', 'status'];
}
