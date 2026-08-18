<?php

namespace App\Http\Requests\Properties;

use App\Enums\PropertyType;
use App\Support\AddressInput;
use App\Support\Digits;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

abstract class PropertyFormRequest extends FormRequest
{
    protected function preparePropertyPayload(): void
    {
        $cep = Digits::only($this->input('cep'));
        $number = Digits::only($this->input('number'));

        $this->merge([
            'cep' => $cep === '' ? null : $cep,
            'number' => $number === '' ? null : $number,
            'street' => AddressInput::sanitize($this->input('street')),
            'neighborhood' => AddressInput::sanitize($this->input('neighborhood')),
            'complement' => AddressInput::sanitize($this->input('complement')),
            'land_area' => $this->isApartment()
                ? 0
                : $this->normalizeArea($this->input('land_area')),
            'building_area' => $this->isLand()
                ? 0
                : $this->normalizeArea($this->input('building_area')),
        ]);
    }

    public function rules(): array
    {
        $addressRegex = '/^[\p{L}\p{N}\s°ºª′″\'"\/\\\\\-.,:()·×÷±²³µ∠△▲▼◆○●≈≠≤≥]+$/u';

        return [
            'person_id' => [
                'required',
                'integer',
                $this->personExistsRule(),
            ],
            'type' => ['required', new Enum(PropertyType::class)],
            'land_area' => $this->landAreaRules(),
            'building_area' => $this->buildingAreaRules(),
            'cep' => ['nullable', 'string', 'size:8', 'regex:/^[0-9]{8}$/'],
            'street' => ['required', 'string', 'max:255', "regex:{$addressRegex}"],
            'number' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'neighborhood' => ['required', 'string', 'max:255', "regex:{$addressRegex}"],
            'complement' => ['nullable', 'string', 'max:255', "regex:{$addressRegex}"],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function landAreaRules(): array
    {
        if ($this->isApartment()) {
            return [
                'required',
                'numeric',
                'decimal:0,2',
                $this->mustBeZero(__('validation.land_area_must_be_zero')),
            ];
        }

        $required = $this->isLand() || $this->isHouse();

        return [
            Rule::requiredIf(fn () => $required),
            'nullable',
            'numeric',
            'decimal:0,2',
            $required ? 'gt:0' : 'min:0',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function buildingAreaRules(): array
    {
        if ($this->isLand()) {
            return [
                'required',
                'numeric',
                'decimal:0,2',
                $this->mustBeZero(__('validation.building_area_must_be_zero')),
            ];
        }

        $required = $this->isHouse() || $this->isApartment();

        return [
            Rule::requiredIf(fn () => $required),
            'nullable',
            'numeric',
            'decimal:0,2',
            $required ? 'gt:0' : 'min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'person_id' => __('properties.fields.owner'),
            'type' => __('properties.fields.type'),
            'land_area' => __('properties.fields.land_area'),
            'building_area' => __('properties.fields.building_area'),
            'cep' => __('properties.fields.cep'),
            'street' => __('properties.fields.street'),
            'number' => __('properties.fields.number'),
            'neighborhood' => __('properties.fields.neighborhood'),
            'complement' => __('properties.fields.complement'),
        ];
    }

    public function messages(): array
    {
        return [
            'land_area.required' => __('validation.land_area_required'),
            'land_area.gt' => __('validation.land_area_gt'),
            'building_area.required' => __('validation.building_area_required'),
            'building_area.gt' => __('validation.building_area_gt'),
        ];
    }

    protected function personExistsRule(): \Illuminate\Validation\Rules\Exists
    {
        $rule = Rule::exists('people', 'id')->whereNull('deleted_at');

        if (! $this->user()?->canAccessAllRecords()) {
            $rule->where('user_id', $this->user()?->id);
        }

        return $rule;
    }

    protected function isLand(): bool
    {
        return $this->input('type') === PropertyType::Land->value;
    }

    protected function isHouse(): bool
    {
        return $this->input('type') === PropertyType::House->value;
    }

    protected function isApartment(): bool
    {
        return $this->input('type') === PropertyType::Apartment->value;
    }

    protected function mustBeZero(string $message): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($message): void {
            if ((float) $value !== 0.0) {
                $fail($message);
            }
        };
    }

    protected function normalizeArea(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        return $value;
    }
}
