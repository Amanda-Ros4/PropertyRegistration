<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class PropertyDocument extends Model
{
    protected $fillable = [
        'property_id',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size',
    ];

    protected $hidden = [
        'path',
        'disk',
    ];

    protected $appends = [
        'human_size',
        'is_image',
        'is_pdf',
        'preview_url',
        'download_url',
    ];

    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (PropertyDocument $document): void {
            if ($document->path !== '') {
                Storage::disk($document->disk)->delete($document->path);
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getHumanSizeAttribute(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getPreviewUrlAttribute(): string
    {
        return route('properties.documents.show', [
            'property' => $this->property_id,
            'document' => $this->id,
        ]);
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('properties.documents.download', [
            'property' => $this->property_id,
            'document' => $this->id,
        ]);
    }
}
