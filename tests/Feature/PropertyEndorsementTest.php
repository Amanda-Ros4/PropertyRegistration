<?php

namespace Tests\Feature;

use App\Enums\EndorsementEvent;
use App\Enums\Gender;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Person;
use App\Models\Property;
use App\Models\PropertyEndorsement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyEndorsementTest extends TestCase
{
    use RefreshDatabase;

    private function createProperty(array $overrides = []): Property
    {
        $user = User::factory()->create();

        $person = Person::query()->create([
            'user_id' => $user->id,
            'name' => 'Maria Silva',
            'birth_date' => now()->subYears(30)->toDateString(),
            'cpf' => '39053344705',
            'gender' => Gender::Female,
            'phone' => '11987654321',
            'email' => 'maria@example.com',
        ]);

        return Property::query()->create(array_merge([
            'user_id' => $user->id,
            'person_id' => $person->id,
            'type' => PropertyType::House,
            'land_area' => 100,
            'building_area' => 80,
            'cep' => '01310100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'neighborhood' => 'Bela Vista',
            'complement' => null,
            'status' => PropertyStatus::Active,
        ], $overrides));
    }

    public function test_increase_endorsement_updates_building_area(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::IncreaseInBuiltArea->value,
                'measure' => '10.50',
                'description' => 'Ampliação da edificação.',
            ])
            ->assertRedirect();

        $property->refresh();

        $this->assertSame('90.50', number_format((float) $property->building_area, 2, '.', ''));
        $this->assertDatabaseHas('property_endorsements', [
            'property_id' => $property->id,
            'event' => EndorsementEvent::IncreaseInBuiltArea->value,
            'measure' => 10.50,
        ]);
        $this->assertDatabaseHas('audits', [
            'event' => 'created',
            'auditable_type' => PropertyEndorsement::class,
        ]);
    }

    public function test_property_status_cannot_be_changed_without_endorsement(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->patch('/properties/'.$property->id.'/status', [
                'status' => PropertyStatus::Inactive->value,
            ])
            ->assertNotFound();

        $this->assertSame(PropertyStatus::Active, $property->fresh()->status);
    }

    public function test_decrease_endorsement_updates_building_area(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::DecreaseInBuiltArea->value,
                'measure' => '20',
                'description' => 'Redução parcial.',
            ])
            ->assertRedirect();

        $this->assertSame('60.00', number_format((float) $property->fresh()->building_area, 2, '.', ''));
    }

    public function test_observation_endorsement_does_not_change_property(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::Observation->value,
                'description' => 'Apenas observação.',
            ])
            ->assertRedirect();

        $property->refresh();

        $this->assertSame(PropertyStatus::Active, $property->status);
        $this->assertSame('80.00', number_format((float) $property->building_area, 2, '.', ''));
    }

    public function test_cancellation_endorsement_inactivates_property(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::Cancellation->value,
                'description' => 'Cancelamento do imóvel.',
            ])
            ->assertRedirect();

        $this->assertSame(PropertyStatus::Inactive, $property->fresh()->status);
    }

    public function test_cancellation_on_inactive_property_is_rejected(): void
    {
        $property = $this->createProperty(['status' => PropertyStatus::Inactive]);

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->from(route('properties.edit', $property))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::Cancellation->value,
                'description' => 'Tentativa inválida.',
            ])
            ->assertSessionHasErrors('event');
    }

    public function test_reactivation_endorsement_activates_property(): void
    {
        $property = $this->createProperty(['status' => PropertyStatus::Inactive]);

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::Reactivation->value,
                'description' => 'Reativação do imóvel.',
            ])
            ->assertRedirect();

        $this->assertSame(PropertyStatus::Active, $property->fresh()->status);
    }

    public function test_reactivation_on_active_property_is_rejected(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->from(route('properties.edit', $property))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::Reactivation->value,
                'description' => 'Tentativa inválida.',
            ])
            ->assertSessionHasErrors('event');
    }

    public function test_area_change_on_land_property_is_rejected(): void
    {
        $property = $this->createProperty([
            'type' => PropertyType::Land,
            'land_area' => 200,
            'building_area' => 0,
        ]);

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->from(route('properties.edit', $property))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::IncreaseInBuiltArea->value,
                'measure' => '10',
                'description' => 'Tentativa inválida.',
            ])
            ->assertSessionHasErrors('event');
    }

    public function test_decrease_greater_than_building_area_is_rejected(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->from(route('properties.edit', $property))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::DecreaseInBuiltArea->value,
                'measure' => '100',
                'description' => 'Tentativa inválida.',
            ])
            ->assertSessionHasErrors('measure');
    }

    public function test_endorsement_sets_occurred_on_automatically(): void
    {
        $property = $this->createProperty();

        $this->actingAs(User::query()->findOrFail($property->user_id))
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::Observation->value,
                'description' => 'Registro com data automática.',
            ])
            ->assertRedirect();

        $endorsement = $property->endorsements()->first();

        $this->assertNotNull($endorsement);
        $this->assertSame(now()->toDateString(), $endorsement->occurred_on->toDateString());
    }

    public function test_other_user_cannot_create_endorsement(): void
    {
        $property = $this->createProperty();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->post(route('properties.endorsements.store', $property), [
                'event' => EndorsementEvent::Observation->value,
                'description' => 'Sem permissão.',
            ])
            ->assertNotFound();
    }
}
