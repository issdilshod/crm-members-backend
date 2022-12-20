<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;

class CompanyIncorporation extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['state_office_website', 'entity_uuid', 'incorporation_date', 'annual_report_date', 'registered_agent_exists', 'registered_agent_company_name', 'notes', 'parent'];

    protected $attributes = ['status' => 1];

}
