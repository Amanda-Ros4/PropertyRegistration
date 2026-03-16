<?php

namespace App\Models;

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
        'street',
        'number',
        'neighborhood',
        'complement',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'person_id' => 'integer',
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

        $term = '%' . $search . '%';

        return $query->where(function (Builder $q) use ($term) {
            $q->where('street', 'like', $term)
                ->orWhere('number', 'like', $term)
                ->orWhere('neighborhood', 'like', $term);
        });
    }

    public function scopeFilterByPerson(Builder $query, ?int $personId): Builder
    {
        if (! $personId) {
            return $query;
        }

        return $query->where('person_id', $personId);
    }
}
