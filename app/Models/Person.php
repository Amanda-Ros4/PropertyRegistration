<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'birth_date',
        'cpf',
        'gender',
        'phone',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'birth_date' => 'date',
            'gender' => Gender::class,
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        $term = '%' . $search . '%';

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', $term)
                ->orWhere('cpf', 'like', $term)
                ->orWhere('email', 'like', $term);
        });
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function hasActiveProperties(): bool
    {
        return $this->properties()->whereNull('deleted_at')->exists();
    }

    public function getFormattedCpfAttribute(): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $this->cpf);

        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' .
                substr($cpf, 3, 3) . '.' .
                substr($cpf, 6, 3) . '-' .
                substr($cpf, 9, 2);
        }

        return $this->cpf;
    }
}
