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

        $raw = trim($search);
        $term = '%'.$raw.'%';
        $cpfDigits = preg_replace('/[^0-9]/', '', $raw);
        $normalized = mb_strtolower($raw);

        $genderMatches = [];
        foreach (Gender::cases() as $gender) {
            $value = $gender->value;
            $label = mb_strtolower((string) __('genders.'.$value));
            if (
                $normalized === $value
                || $normalized === $label
                || (
                    mb_strlen($normalized) >= 3
                    && (str_contains($label, $normalized) || str_contains($normalized, $label))
                )
            ) {
                $genderMatches[] = $value;
            }
        }

        $birthDates = $this->parseSearchBirthDates($raw);

        return $query->where(function (Builder $q) use ($term, $cpfDigits, $genderMatches, $birthDates) {
            $q->where('name', 'like', $term)
                ->orWhere('email', 'like', $term);

            if ($cpfDigits !== '') {
                $q->orWhere('cpf', 'like', '%'.$cpfDigits.'%');
            }

            foreach ($birthDates as $date) {
                $q->orWhereDate('birth_date', $date);
            }

            if ($genderMatches !== []) {
                $q->orWhereIn('gender', array_values(array_unique($genderMatches)));
            }
        });
    }

    /**
     * Interpreta possíveis datas digitadas na busca (YYYY-MM-DD, MM/DD/YYYY, DD/MM/YYYY, MMDDYYYY).
     *
     * @return list<string>
     */
    private function parseSearchBirthDates(string $raw): array
    {
        $dates = [];

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            $dates[] = $raw;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $raw, $m)) {
            $dates[] = sprintf('%s-%s-%s', $m[3], $m[1], $m[2]); // MM/DD/YYYY
            $dates[] = sprintf('%s-%s-%s', $m[3], $m[2], $m[1]); // DD/MM/YYYY
        }

        $digits = preg_replace('/[^0-9]/', '', $raw);
        if (strlen($digits) === 8) {
            $dates[] = sprintf('%s-%s-%s', substr($digits, 4, 4), substr($digits, 0, 2), substr($digits, 2, 2)); // MMDDYYYY
            $dates[] = sprintf('%s-%s-%s', substr($digits, 4, 4), substr($digits, 2, 2), substr($digits, 0, 2)); // DDMMYYYY
        }

        return array_values(array_unique(array_filter($dates, function (string $date) {
            [$y, $m, $d] = array_map('intval', explode('-', $date));

            return checkdate($m, $d, $y);
        })));
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query;
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
            return substr($cpf, 0, 3).'.'.
                substr($cpf, 3, 3).'.'.
                substr($cpf, 6, 3).'-'.
                substr($cpf, 9, 2);
        }

        return $this->cpf;
    }
}
