<?php

namespace App\Http\Requests\Properties;

use App\Models\Property;

class StorePropertyRequest extends PropertyFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Property::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->preparePropertyPayload();
    }
}
