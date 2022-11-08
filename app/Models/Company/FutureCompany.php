<?php

namespace App\Models\Company;

use App\Models\Director\Director;
use App\Models\Helper\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;

class FutureCompany extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'sic_code_uuid', 'incorporation_state_uuid', 'doing_business_in_state_uuid', 'virtual_office_uuid', 'revival_date', 'revival_fee', 'future_website_link', 'recommended_director_uuid', 'revived', 'db_report_number', 'comment', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function files(): HasMany{
        return $this->hasMany(File::class, 'entity_uuid', 'uuid')
                    ->where('status', Config::get('common.status.actived'));
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(Director::class, 'recommended_director_uuid', 'uuid');
    }
}
