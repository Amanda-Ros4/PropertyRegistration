<?php

namespace App\Http\Requests\Properties;

use App\Enums\PropertyType;
use App\Models\Property;
use App\Support\AddressInput;
use App\Support\Digits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Property::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $cep = Digits::only($this->input('cep'));
        $number = Digits::only($this->input('number'));

        $this->merge([
            'cep' => $cep === '' ? null : $cep,
            'number' => $number === '' ? null : $number,
            'street' => AddressInput::sanitize($this->input('street')),
            'neighborhood' => AddressInput::sanitize($this->input('neighborhood')),
            'complement' => AddressInput::sanitize($this->input('complement')),
            'land_area' => $this->normalizeArea($this->input('land_area')),
            'building_area' => $this->normalizeArea($this->input('building_area')),
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
            'land_area' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'building_area' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'cep' => ['nullable', 'string', 'size:8', 'regex:/^[0-9]{8}$/'],
            'street' => ['required', 'string', 'max:255', "regex:{$addressRegex}"],
            'number' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'neighborhood' => ['required', 'string', 'max:255', "regex:{$addressRegex}"],
            'complement' => ['nullable', 'string', 'max:255', "regex:{$addressRegex}"],
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

    private function normalizeArea(mixed $value): mixed
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
