<?php

namespace App\Services\Helper;

use App\Models\Helper\RejectReason;

class RejectReasonService{

    public function create($entity)
    {
        $rejectReason = RejectReason::create($entity);
    }

}