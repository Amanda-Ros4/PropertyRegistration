<?php

namespace App\Http\Requests\Properties;

use App\Enums\PropertyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePropertyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('property')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(PropertyStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => __('properties.fields.status'),
        ];
    }
}
