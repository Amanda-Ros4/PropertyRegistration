<?php

namespace App\Models;

use App\Enums\EndorsementEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyEndorsement extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyEndorsementFactory> */
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
