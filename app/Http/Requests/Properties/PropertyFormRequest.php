<?php

namespace App\Http\Requests\Properties;

use App\Enums\PropertyType;
use App\Support\AddressInput;
use App\Support\Digits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

abstract class PropertyFormRequest extends FormRequest
{
    protected function preparePropertyPayload(): void
    {
        $cep = Digits::only($this->input('cep'));
        $number = Digits::only($this->input('number'));
        $buildingArea = $this->isLand()
            ? 0
            : $this->normalizeArea($this->input('building_area'));

        $this->merge([
            'cep' => $cep === '' ? null : $cep,
            'number' => $number === '' ? null : $number,
            'street' => AddressInput::sanitize($this->input('street')),
            'neighborhood' => AddressInput::sanitize($this->input('neighborhood')),
            'complement' => AddressInput::sanitize($this->input('complement')),
            'land_area' => $this->normalizeArea($this->input('land_area')),
            'building_area' => $buildingArea,
        ]);
    }

    public function rules(): array
    {
        $addressRegex = '/^[\p{L}\p{N}\s°ºª′″\'"\/\\\\\-.,:()·×÷±²³µ∠△▲▼◆○●≈≠≤≥]+$/u';

        return [
            'person_id' => [
                'required',
                'integer',
                Rule::exists('people', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
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
        $rules = [
            Rule::requiredIf(fn () => $this->isLand()),
            'nullable',
            'numeric',
            'decimal:0,2',
        ];

        $rules[] = $this->isLand() ? 'gt:0' : 'min:0';

        return $rules;
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
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ((float) $value !== 0.0) {
                        $fail(__('validation.building_area_must_be_zero'));
                    }
                },
            ];
        }

        return ['nullable', 'numeric', 'min:0', 'decimal:0,2'];
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
        ];
    }

    protected function isLand(): bool
    {
        return $this->input('type') === PropertyType::Land->value;
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
