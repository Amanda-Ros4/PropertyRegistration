<?php

namespace App\Http\Requests\Properties;

use App\Models\Property;
use App\Support\PropertyDocuments;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            && ($this->user()?->can('update', $property) ?? false);
    }

    public function rules(): array
    {
        $property = $this->route('property');
        $existing = $property instanceof Property
            ? $property->documents()->count()
            : 0;

        return PropertyDocuments::uploadRules($existing, required: true);
    }

    public function messages(): array
    {
        return PropertyDocuments::messages();
    }

    public function attributes(): array
    {
        return PropertyDocuments::attributes();
    }
}
