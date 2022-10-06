<?php

namespace App\Models\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class Hosting extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['host', 'status'];

    protected $attributes = ['status' => 1];
}
