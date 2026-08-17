<?php

namespace App\Http\Requests\Properties;

use App\Enums\EndorsementEvent;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StorePropertyEndorsementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            && ($this->user()?->can('update', $property) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $measure = $this->input('measure');

        if ($measure === null || $measure === '') {
            return;
        }

        $this->merge([
            'measure' => str_replace(',', '.', trim((string) $measure)),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event' => ['required', new Enum(EndorsementEvent::class)],
            'description' => ['required', 'string', 'max:2000'],
            'measure' => [
                Rule::requiredIf(fn () => $this->requiresMeasure()),
                Rule::prohibitedIf(fn () => ! $this->requiresMeasure()),
                'nullable',
                'numeric',
                'decimal:0,2',
                'gt:0',
            ],
            'occurred_on' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Property|null $property */
            $property = $this->route('property');
            $event = EndorsementEvent::tryFrom((string) $this->input('event'));

            if (! $property instanceof Property || ! $event instanceof EndorsementEvent) {
                return;
            }

            if ($event === EndorsementEvent::Cancellation && $property->status === PropertyStatus::Inactive) {
                $validator->errors()->add('event', __('validation.endorsement_cancellation_on_inactive'));
            }

            if ($event === EndorsementEvent::Reactivation && $property->status === PropertyStatus::Active) {
                $validator->errors()->add('event', __('validation.endorsement_reactivation_on_active'));
            }

            if ($event->requiresMeasure() && $property->type === PropertyType::Land) {
                $validator->errors()->add('event', __('validation.endorsement_area_change_on_land'));
            }

            if ($event === EndorsementEvent::DecreaseInBuiltArea) {
                $measure = (float) $this->input('measure');
                $buildingArea = (float) $property->building_area;

                if ($buildingArea - $measure < 0) {
                    $validator->errors()->add('measure', __('validation.endorsement_decrease_exceeds_area'));
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'event' => __('properties.endorsements.fields.event'),
            'measure' => __('properties.endorsements.fields.measure'),
            'description' => __('properties.endorsements.fields.description'),
        ];
    }

    private function requiresMeasure(): bool
    {
        $event = EndorsementEvent::tryFrom((string) $this->input('event'));

        return $event?->requiresMeasure() ?? false;
    }
}
