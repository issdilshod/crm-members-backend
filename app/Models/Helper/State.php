<?php

namespace App\Models\Helper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class State extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['short_name', 'full_name', 'status'];

    protected $attributes = ['status' => 1];
}
