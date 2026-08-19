<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Person extends Model implements Auditable
{
    use AuditableTrait;
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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canAccessAllRecords()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        $raw = trim($search);
        $term = '%' . $raw . '%';
        $digits = preg_replace('/[^0-9]/', '', $raw) ?? '';
        $normalized = mb_strtolower($raw);

        $genderMatches = [];
        foreach (Gender::cases() as $gender) {
            $value = $gender->value;
            $label = mb_strtolower((string) __('genders.' . $value));
            if ($normalized === $value || $normalized === $label) {
                $genderMatches[] = $value;

                continue;
            }

            // Evita match frouxo com 1 letra (ex.: "a" em "Masculino"/"Feminino").
            if (mb_strlen($normalized) >= 2 && str_contains($label, $normalized)) {
                $genderMatches[] = $value;
            }
        }

        $birthCriteria = $this->parseSearchBirthDateCriteria($raw);

        return $query->where(function (Builder $q) use ($term, $digits, $genderMatches, $birthCriteria) {
            $q->where('name', 'like', $term)
                ->orWhere('email', 'like', $term);

            if ($digits !== '') {
                $q->orWhere('cpf', 'like', '%' . $digits . '%')
                    ->orWhere('phone', 'like', '%' . $digits . '%');
            }

            foreach ($birthCriteria['exact'] as $date) {
                $q->orWhereDate('birth_date', $date);
            }

            foreach ($birthCriteria['years'] as $year) {
                $q->orWhereYear('birth_date', $year);
            }

            foreach ($birthCriteria['year_ranges'] as $range) {
                $q->orWhere(function (Builder $dateQuery) use ($range) {
                    $dateQuery
                        ->whereDate('birth_date', '>=', sprintf('%04d-01-01', $range['start']))
                        ->whereDate('birth_date', '<=', sprintf('%04d-12-31', $range['end']));
                });
            }

            foreach ($birthCriteria['months'] as $month) {
                $q->orWhereMonth('birth_date', $month);
            }

            foreach ($birthCriteria['days'] as $day) {
                $q->orWhereDay('birth_date', $day);
            }

            foreach ($birthCriteria['day_months'] as $dayMonth) {
                $q->orWhere(function (Builder $dateQuery) use ($dayMonth) {
                    $dateQuery
                        ->whereDay('birth_date', $dayMonth['day'])
                        ->whereMonth('birth_date', $dayMonth['month']);
                });
            }

            foreach ($birthCriteria['month_years'] as $monthYear) {
                $q->orWhere(function (Builder $dateQuery) use ($monthYear) {
                    $dateQuery
                        ->whereMonth('birth_date', $monthYear['month'])
                        ->whereYear('birth_date', $monthYear['year']);
                });
            }

            if ($genderMatches !== []) {
                $q->orWhereIn('gender', array_values(array_unique($genderMatches)));
            }
        });
    }

    /**
     * Interpreta datas completas ou parciais na busca (padrão BR: DD/MM/YYYY).
     * Exemplos: 30081996, 30/08/1996, 1996, 08, 30, 3008, 199.
     *
     * @return array{
     *     exact: list<string>,
     *     years: list<int>,
     *     year_ranges: list<array{start:int, end:int}>,
     *     months: list<int>,
     *     days: list<int>,
     *     day_months: list<array{day:int, month:int}>,
     *     month_years: list<array{month:int, year:int}>
     * }
     */
    private function parseSearchBirthDateCriteria(string $raw): array
    {
        $criteria = [
            'exact' => [],
            'years' => [],
            'year_ranges' => [],
            'months' => [],
            'days' => [],
            'day_months' => [],
            'month_years' => [],
        ];

        // Só interpreta como data se o termo for numérico / formato de data.
        if (! preg_match('/^[\d\/\-.]+$/', $raw)) {
            return $criteria;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            $criteria['exact'][] = $raw;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $raw, $m)) {
            // Preferência BR: DD/MM/YYYY; também tenta MM/DD/YYYY.
            $criteria['exact'][] = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
            $criteria['exact'][] = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[1], (int) $m[2]);
        }

        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $raw, $m)) {
            $month = (int) $m[1];
            $year = (int) $m[2];
            if ($month >= 1 && $month <= 12 && $year >= 1900 && $year <= 2100) {
                $criteria['month_years'][] = ['month' => $month, 'year' => $year];
            }
        }

        if (preg_match('/^(\d{4})$/', $raw, $m)) {
            $year = (int) $m[1];
            if ($year >= 1900 && $year <= 2100) {
                $criteria['years'][] = $year;
            }
        }

        $digits = preg_replace('/[^0-9]/', '', $raw) ?? '';
        $length = strlen($digits);

        // 1 dígito: não filtra por data (muito amplo); fica com CPF/telefone/nome.
        if ($length === 2) {
            $value = (int) $digits;
            if ($value >= 1 && $value <= 31) {
                $criteria['days'][] = $value;
            }
            if ($value >= 1 && $value <= 12) {
                $criteria['months'][] = $value;
            }
        }

        // Ano parcial portátil (ex.: 199 → 1990..1999)
        if ($length === 3) {
            $prefix = (int) $digits;
            $start = $prefix * 10;
            $end = $start + 9;
            if ($start >= 1900 && $end <= 2100) {
                $criteria['year_ranges'][] = ['start' => $start, 'end' => $end];
            }
        }

        if ($length === 4) {
            $asYear = (int) $digits;
            if ($asYear >= 1900 && $asYear <= 2100) {
                $criteria['years'][] = $asYear;
            }

            $day = (int) substr($digits, 0, 2);
            $month = (int) substr($digits, 2, 2);
            if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12) {
                $criteria['day_months'][] = ['day' => $day, 'month' => $month]; // DDMM
            }
        }

        if ($length === 6) {
            $day = (int) substr($digits, 0, 2);
            $month = (int) substr($digits, 2, 2);
            $yearShort = (int) substr($digits, 4, 2);

            foreach ([1900 + $yearShort, 2000 + $yearShort] as $year) {
                if ($year >= 1900 && $year <= 2100 && checkdate($month, $day, $year)) {
                    $criteria['exact'][] = sprintf('%04d-%02d-%02d', $year, $month, $day); // DDMMYY
                }
            }

            $monthYearMonth = (int) substr($digits, 0, 2);
            $monthYearYear = (int) substr($digits, 2, 4);
            if ($monthYearMonth >= 1 && $monthYearMonth <= 12 && $monthYearYear >= 1900 && $monthYearYear <= 2100) {
                $criteria['month_years'][] = ['month' => $monthYearMonth, 'year' => $monthYearYear]; // MMYYYY
            }
        }

        if ($length === 8) {
            // Preferência BR: DDMMYYYY (ex.: 30081996 = 30/08/1996)
            $criteria['exact'][] = sprintf(
                '%s-%s-%s',
                substr($digits, 4, 4),
                substr($digits, 2, 2),
                substr($digits, 0, 2)
            );
            // Fallback MM/DD/YYYY
            $criteria['exact'][] = sprintf(
                '%s-%s-%s',
                substr($digits, 4, 4),
                substr($digits, 0, 2),
                substr($digits, 2, 2)
            );
        }

        $criteria['exact'] = array_values(array_unique(array_filter(
            $criteria['exact'],
            function (string $date) {
                [$y, $m, $d] = array_map('intval', explode('-', $date));

                return checkdate($m, $d, $y);
            }
        )));
        $criteria['years'] = array_values(array_unique($criteria['years']));
        $criteria['year_ranges'] = array_values(array_unique($criteria['year_ranges'], SORT_REGULAR));
        $criteria['months'] = array_values(array_unique($criteria['months']));
        $criteria['days'] = array_values(array_unique($criteria['days']));
        $criteria['day_months'] = array_values(array_unique($criteria['day_months'], SORT_REGULAR));
        $criteria['month_years'] = array_values(array_unique($criteria['month_years'], SORT_REGULAR));

        return $criteria;
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
            return substr($cpf, 0, 3) . '.' .
                substr($cpf, 3, 3) . '.' .
                substr($cpf, 6, 3) . '-' .
                substr($cpf, 9, 2);
        }

        return $this->cpf;
    }
}
