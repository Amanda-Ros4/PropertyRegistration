<?php

namespace App\Models;

use App\Enums\EndorsementEvent;
use Database\Factories\PropertyEndorsementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class PropertyEndorsement extends Model implements Auditable
{
    use AuditableTrait;

    /** @use HasFactory<PropertyEndorsementFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'event',
        'measure',
        'description',
        'occurred_on',
    ];

    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'event' => EndorsementEvent::class,
            'measure' => 'decimal:2',
            'occurred_on' => 'date',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
