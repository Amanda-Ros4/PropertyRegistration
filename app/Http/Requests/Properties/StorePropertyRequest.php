<?php

namespace App\Http\Requests\Properties;

use App\Models\Property;
use App\Support\PropertyDocuments;

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

    public function rules(): array
    {
        return array_merge(parent::rules(), PropertyDocuments::uploadRules(0));
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), PropertyDocuments::messages());
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), PropertyDocuments::attributes());
    }
}
