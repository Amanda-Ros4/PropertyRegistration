<?php

namespace App\Services;

use App\Enums\ActiveStatus;
use App\Enums\EndorsementEvent;
use App\Enums\Gender;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\UserProfile;
use App\Models\Audit;
use App\Models\Person;
use App\Models\Property;
use App\Models\PropertyEndorsement;
use App\Models\User;
use App\Support\AuditableTypes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AuditService
{
    /**
     * @var array<class-string, array<string, string>>
     */
    private const ATTRIBUTE_LABELS = [
        Person::class => [
            'id' => 'common.id',
            'user_id' => 'audit.attributes.user_id',
            'name' => 'people.fields.name',
            'birth_date' => 'people.fields.birth_date',
            'cpf' => 'people.fields.cpf',
            'gender' => 'people.fields.gender',
            'phone' => 'people.fields.phone',
            'email' => 'people.fields.email',
        ],
        Property::class => [
            'id' => 'properties.fields.municipal_registration',
            'user_id' => 'audit.attributes.user_id',
            'person_id' => 'properties.fields.owner',
            'type' => 'properties.fields.type',
            'land_area' => 'properties.fields.land_area',
            'building_area' => 'properties.fields.building_area',
            'cep' => 'properties.fields.cep',
            'street' => 'properties.fields.street',
            'number' => 'properties.fields.number',
            'neighborhood' => 'properties.fields.neighborhood',
            'complement' => 'properties.fields.complement',
            'status' => 'properties.fields.status',
        ],
        PropertyEndorsement::class => [
            'id' => 'common.id',
            'property_id' => 'audit.attributes.property_id',
            'event' => 'properties.endorsements.fields.event',
            'measure' => 'properties.endorsements.fields.measure',
            'description' => 'properties.endorsements.fields.description',
            'occurred_on' => 'properties.endorsements.fields.date',
        ],
        User::class => [
            'id' => 'common.id',
            'name' => 'users.fields.name',
            'email' => 'users.fields.email',
            'cpf' => 'users.fields.cpf',
            'profile' => 'users.fields.profile',
            'active' => 'users.fields.active',
            'email_verified_at' => 'audit.attributes.email_verified_at',
        ],
    ];

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $userId = isset($filters['user_id']) ? (int) $filters['user_id'] : null;
        $event = is_string($filters['event'] ?? null) ? $filters['event'] : null;
        $auditableType = is_string($filters['auditable_type'] ?? null) ? $filters['auditable_type'] : null;
        $date = is_string($filters['date'] ?? null) ? $filters['date'] : null;

        return Audit::query()
            ->with('user:id,name,email')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->when($event, fn ($query) => $query->where('event', $event))
            ->when($auditableType, fn ($query) => $query->where('auditable_type', $auditableType))
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', Carbon::parse($date)->toDateString());
            })
            ->whereIn('auditable_type', AuditableTypes::classes())
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Audit $audit) => $this->transformForList($audit));
    }

    public function transformForList(Audit $audit): array
    {
        return [
            'id' => $audit->id,
            'event' => $audit->event,
            'created_at' => $audit->created_at?->toIso8601String(),
            'auditable_type' => $audit->auditable_type,
            'table_label_key' => $audit->tableLabelKey(),
            'auditable_id' => $audit->auditable_id,
            'user_name' => $audit->user?->name ?? __('audit.system_user'),
            'user_email' => $audit->user?->email,
        ];
    }

    public function transformForShow(Audit $audit): array
    {
        return [
            'id' => $audit->id,
            'event' => $audit->event,
            'created_at' => $audit->created_at?->toIso8601String(),
            'auditable_type' => $audit->auditable_type,
            'table_label_key' => $audit->tableLabelKey(),
            'auditable_id' => $audit->auditable_id,
            'user_name' => $audit->user?->name ?? __('audit.system_user'),
            'user_email' => $audit->user?->email,
            'old_values' => $this->presentValues($audit->old_values, (string) $audit->auditable_type),
            'new_values' => $this->presentValues($audit->new_values, (string) $audit->auditable_type),
            'url' => $audit->url,
            'ip_address' => $audit->ip_address,
        ];
    }

    public function userFilterOptions(): array
    {
        $userIds = Audit::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, value: string}>|null
     */
    private function presentValues(mixed $values, string $auditableType): ?array
    {
        $values = $this->normalizeValues($values);

        if ($values === null) {
            return null;
        }

        $entries = [];

        foreach ($values as $attribute => $value) {
            if (! is_string($attribute)) {
                continue;
            }

            $entries[] = [
                'label' => $this->attributeLabel($auditableType, $attribute),
                'value' => $this->attributeValue($auditableType, $attribute, $value),
            ];
        }

        return $entries === [] ? null : $entries;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeValues(mixed $values): ?array
    {
        if ($values === null || $values === [] || $values === '') {
            return null;
        }

        if (is_string($values)) {
            $decoded = json_decode($values, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return ['_' => $values];
            }

            $values = $decoded;
        }

        if (! is_array($values)) {
            return ['_' => $values];
        }

        return $values;
    }

    private function attributeLabel(string $auditableType, string $attribute): string
    {
        $key = self::ATTRIBUTE_LABELS[$auditableType][$attribute] ?? null;

        if ($key) {
            return __($key);
        }

        return str_replace('_', ' ', $attribute);
    }

    private function attributeValue(string $auditableType, string $attribute, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? __('common.yes') : __('common.no');
        }

        if (is_array($value)) {
            if (array_key_exists('value', $value)) {
                return $this->attributeValue($auditableType, $attribute, $value['value']);
            }

            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $stringValue = is_scalar($value) ? (string) $value : '';

        return match ($attribute) {
            'gender' => $this->translateEnum($stringValue, [
                Gender::Male->value => 'genders.male',
                Gender::Female->value => 'genders.female',
                Gender::Other->value => 'genders.other',
                Gender::PreferNotToSay->value => 'genders.prefer_not_to_say',
            ]),
            'type' => $this->translateEnum($stringValue, [
                PropertyType::Land->value => 'properties.types.land',
                PropertyType::House->value => 'properties.types.house',
                PropertyType::Apartment->value => 'properties.types.apartment',
            ]),
            'status' => $this->translateEnum($stringValue, [
                PropertyStatus::Active->value => 'properties.statuses.active',
                PropertyStatus::Inactive->value => 'properties.statuses.inactive',
            ]),
            'profile' => $this->translateEnum($stringValue, [
                UserProfile::TiAdmin->value => 'users.profiles.ti_admin',
                UserProfile::SystemAdmin->value => 'users.profiles.system_admin',
                UserProfile::Attendant->value => 'users.profiles.attendant',
            ]),
            'active' => $this->translateEnum($stringValue, [
                ActiveStatus::Active->value => 'users.active_status.active',
                ActiveStatus::Inactive->value => 'users.active_status.inactive',
            ]),
            'event' => $this->translateEnum($stringValue, [
                EndorsementEvent::IncreaseInBuiltArea->value => 'properties.endorsements.events.increase_in_built_area',
                EndorsementEvent::DecreaseInBuiltArea->value => 'properties.endorsements.events.decrease_in_built_area',
                EndorsementEvent::Observation->value => 'properties.endorsements.events.observation',
                EndorsementEvent::Cancellation->value => 'properties.endorsements.events.cancellation',
                EndorsementEvent::Reactivation->value => 'properties.endorsements.events.reactivation',
            ]),
            'birth_date', 'occurred_on', 'email_verified_at' => $this->formatDateValue($stringValue),
            'cpf' => $this->formatCpf($stringValue),
            'cep' => $this->formatCep($stringValue),
            'person_id' => $this->formatPersonReference((int) $stringValue),
            'property_id' => '#'.$stringValue,
            'user_id' => $this->formatUserReference((int) $stringValue),
            default => $stringValue,
        };
    }

    /**
     * @param  array<string, string>  $map
     */
    private function translateEnum(string $value, array $map): string
    {
        return isset($map[$value]) ? __($map[$value]) : $value;
    }

    private function formatDateValue(string $value): string
    {
        try {
            $date = Carbon::parse($value);

            if ($date->format('H:i:s') === '00:00:00') {
                return $date->format('d/m/Y');
            }

            return $date->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function formatCpf(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) !== 11) {
            return $value;
        }

        return substr($digits, 0, 3).'.'.substr($digits, 3, 3).'.'.substr($digits, 6, 3).'-'.substr($digits, 9, 2);
    }

    private function formatCep(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) !== 8) {
            return $value;
        }

        return substr($digits, 0, 5).'-'.substr($digits, 5, 3);
    }

    private function formatPersonReference(int $id): string
    {
        $name = Person::query()->withTrashed()->whereKey($id)->value('name');

        return $name ? "#{$id} — {$name}" : "#{$id}";
    }

    private function formatUserReference(int $id): string
    {
        $name = User::query()->whereKey($id)->value('name');

        return $name ? "#{$id} — {$name}" : "#{$id}";
    }
}
