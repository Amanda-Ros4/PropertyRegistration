<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'person_id',
        'type',
        'land_area',
        'building_area',
        'cep',
        'street',
        'number',
        'neighborhood',
        'complement',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'person_id' => 'integer',
            'type' => PropertyType::class,
            'land_area' => 'decimal:2',
            'building_area' => 'decimal:2',
            'status' => PropertyStatus::class,
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('properties.user_id', $userId);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        $term = '%'.$search.'%';
        $digits = preg_replace('/[^0-9]/', '', $search) ?? '';

        return $query->where(function (Builder $q) use ($term, $digits) {
            $q->where('street', 'like', $term)
                ->orWhere('number', 'like', $term)
                ->orWhere('neighborhood', 'like', $term)
                ->orWhere('complement', 'like', $term);

            if ($digits !== '') {
                $q->orWhere('cep', 'like', '%'.$digits.'%')
                    ->orWhere('number', 'like', '%'.$digits.'%')
                    ->orWhere('id', $digits);
            }
        });
    }

    public function scopeFilterById(Builder $query, ?int $id): Builder
    {
        if (! $id) {
            return $query;
        }

        return $query->where('id', $id);
    }

    public function scopeFilterByType(Builder $query, ?string $type): Builder
    {
        if (! $type) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeFilterByStreet(Builder $query, ?string $street): Builder
    {
        if (! $street) {
            return $query;
        }

        return $query->where('street', 'like', '%'.$street.'%');
    }

    public function scopeFilterByNumber(Builder $query, ?string $number): Builder
    {
        if (! $number) {
            return $query;
        }

        return $query->where('number', 'like', '%'.$number.'%');
    }

    public function scopeFilterByNeighborhood(Builder $query, ?string $neighborhood): Builder
    {
        if (! $neighborhood) {
            return $query;
        }

        return $query->where('neighborhood', 'like', '%'.$neighborhood.'%');
    }

    public function scopeFilterByPerson(Builder $query, ?int $personId): Builder
    {
        if (! $personId) {
            return $query;
        }

        return $query->where('person_id', $personId);
    }

    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
