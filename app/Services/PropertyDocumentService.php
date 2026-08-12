<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Support\PropertyDocuments;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PropertyDocumentService
{
    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    public function storeMany(Property $property, array $files): void
    {
        $uploads = array_values(array_filter(
            $files,
            fn (mixed $file): bool => $file instanceof UploadedFile && $file->isValid(),
        ));

        if ($uploads === []) {
            return;
        }

        DB::transaction(function () use ($property, $uploads): void {
            $locked = Property::query()->lockForUpdate()->findOrFail($property->id);
            $existing = $locked->documents()->count();

            if ($existing + count($uploads) > PropertyDocuments::MAX_PER_PROPERTY) {
                throw ValidationException::withMessages([
                    'documents' => [__('validation.property_documents_max', [
                        'max' => PropertyDocuments::MAX_PER_PROPERTY,
                    ])],
                ]);
            }

            foreach ($uploads as $file) {
                $this->storeOne($locked, $file);
            }
        });
    }

    public function storeOne(Property $property, UploadedFile $file): PropertyDocument
    {
        $extension = $this->extensionFor($file);
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = PropertyDocuments::directoryFor((int) $property->id);
        $path = $file->storeAs($directory, $filename, PropertyDocuments::DISK);

        if ($path === false) {
            throw ValidationException::withMessages([
                'documents' => [__('validation.property_documents_store_failed')],
            ]);
        }

        try {
            return $property->documents()->create([
                'original_name' => $this->safeOriginalName($file, $extension),
                'disk' => PropertyDocuments::DISK,
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk(PropertyDocuments::DISK)->delete($path);
            throw $exception;
        }
    }

    public function delete(PropertyDocument $document): void
    {
        $document->delete();
    }

    public function deleteAllForProperty(Property $property): void
    {
        $property->documents()->get()->each->delete();

        Storage::disk(PropertyDocuments::DISK)->deleteDirectory(
            PropertyDocuments::directoryFor((int) $property->id),
        );
    }

    private function extensionFor(UploadedFile $file): string
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            default => strtolower($file->getClientOriginalExtension() ?: 'bin'),
        };
    }

    private function safeOriginalName(UploadedFile $file, string $extension): string
    {
        $name = str_replace(["\0", '/', '\\'], '', $file->getClientOriginalName());
        $name = trim($name);

        if ($name === '') {
            $name = 'document.'.$extension;
        }

        return mb_substr($name, 0, 255);
    }
}
