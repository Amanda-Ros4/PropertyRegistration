<?php

namespace App\Models;

use App\Support\AuditableTypes;
use OwenIt\Auditing\Models\Audit as BaseAudit;

class Audit extends BaseAudit
{
    public function tableLabelKey(): string
    {
        return AuditableTypes::labelKey((string) $this->auditable_type);
    }
}
