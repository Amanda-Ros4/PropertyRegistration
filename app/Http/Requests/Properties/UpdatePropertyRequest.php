<?php

namespace App\Http\Requests\Properties;

class UpdatePropertyRequest extends PropertyFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('property')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->preparePropertyPayload();
    }
}
