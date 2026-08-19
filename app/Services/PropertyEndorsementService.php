<?php

namespace App\Services;

use App\Enums\EndorsementEvent;
use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\PropertyEndorsement;
use Illuminate\Support\Facades\DB;

class PropertyEndorsementService
{
    public function create(Property $property, array $data): PropertyEndorsement
    {
        return DB::transaction(function () use ($property, $data) {
            $event = EndorsementEvent::from($data['event']);
            $measure = $event->requiresMeasure() ? $data['measure'] : null;

            $endorsement = PropertyEndorsement::create([
                'property_id' => $property->id,
                'event' => $event,
                'measure' => $measure,
                'description' => $data['description'],
                'occurred_on' => now()->toDateString(),
            ]);

            match ($event) {
                EndorsementEvent::IncreaseInBuiltArea => $property->update([
                    'building_area' => round((float) $property->building_area + (float) $measure, 2),
                ]),
                EndorsementEvent::DecreaseInBuiltArea => $property->update([
                    'building_area' => round((float) $property->building_area - (float) $measure, 2),
                ]),
                EndorsementEvent::Cancellation => $property->update([
                    'status' => PropertyStatus::Inactive,
                ]),
                EndorsementEvent::Reactivation => $property->update([
                    'status' => PropertyStatus::Active,
                ]),
                EndorsementEvent::Observation => null,
            };

            return $endorsement;
        });
    }
}
