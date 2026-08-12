<?php

namespace App\Support;

use Closure;

class PropertyDocuments
{
    public const MAX_PER_PROPERTY = 5;

    public const MAX_KILOBYTES = 3072;

    public const DISK = 'local';

    public const DIRECTORY = 'property-documents';

    /**
     * @var list<string>
     */
    public const MIMES = ['jpg', 'jpeg', 'png', 'pdf'];

    /**
     * @var list<string>
     */
    public const MIMETYPES = ['image/jpeg', 'image/png', 'application/pdf'];

    public static function directoryFor(int $propertyId): string
    {
        return self::DIRECTORY.'/'.$propertyId;
    }

    /**
     * @return array<string, mixed>
     */
    public static function uploadRules(int $existingCount, bool $required = false): array
    {
        return [
            'documents' => [
                $required ? 'required' : 'nullable',
                'array',
                function (string $attribute, mixed $value, Closure $fail) use ($existingCount): void {
                    $incoming = is_array($value) ? count($value) : 0;

                    if ($existingCount + $incoming > self::MAX_PER_PROPERTY) {
                        $fail(__('validation.property_documents_max', ['max' => self::MAX_PER_PROPERTY]));
                    }
                },
            ],
            'documents.*' => [
                'file',
                'mimes:'.implode(',', self::MIMES),
                'mimetypes:'.implode(',', self::MIMETYPES),
                'max:'.self::MAX_KILOBYTES,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'documents.required' => __('validation.property_documents_required'),
            'documents.max' => __('validation.property_documents_max', ['max' => self::MAX_PER_PROPERTY]),
            'documents.*.mimes' => __('validation.property_documents_mimes'),
            'documents.*.mimetypes' => __('validation.property_documents_mimes'),
            'documents.*.max' => __('validation.property_documents_size'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'documents' => __('properties.fields.documents'),
            'documents.*' => __('properties.fields.documents'),
        ];
    }
}
