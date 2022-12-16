<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class CompanyIncorporation extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['entity_uuid', 'annual_report', 'effective_date', 'registered_agent_exists', 'notes', 'parent'];

    protected $attributes = ['status' => 1];

}
