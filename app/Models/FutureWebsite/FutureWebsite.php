<?php

namespace App\Models\FutureWebsite;

use App\Models\Helper\RejectReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FutureWebsite extends Model
{
    use HasFactory, TraitUuid;

    protected $fillable = ['user_uuid', 'sic_code_uuid', 'link', 'status', 'approved'];

    protected $attributes = ['status' => 1, 'approved' => 0];

    public function reject_reason(): HasOne
    {
        return $this->hasOne(RejectReason::class, 'entity_uuid', 'uuid')
                    ->latest();
    }
}
