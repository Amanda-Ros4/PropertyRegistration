<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessAndAuditTest extends TestCase
{
    use RefreshDatabase;

    private function createPropertyFor(User $owner): Property
    {
        $person = Person::query()->create([
            'user_id' => $owner->id,
            'name' => 'Maria Silva',
            'birth_date' => now()->subYears(30)->toDateString(),
            'cpf' => '39053344705',
            'gender' => Gender::Female,
            'phone' => '11987654321',
            'email' => 'maria.'.$owner->id.'@example.com',
        ]);

        return Property::query()->create([
            'user_id' => $owner->id,
            'person_id' => $person->id,
            'type' => PropertyType::House,
            'land_area' => 100,
            'building_area' => 80,
            'cep' => '01310100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'neighborhood' => 'Bela Vista',
            'status' => PropertyStatus::Active,
        ]);
    }

    public function test_ti_admin_can_view_another_users_property(): void
    {
        $owner = User::factory()->attendant()->create();
        $tiAdmin = User::factory()->tiAdmin()->create();
        $property = $this->createPropertyFor($owner);

        $this->actingAs($tiAdmin)
            ->get(route('properties.edit', $property))
            ->assertOk();

        $this->actingAs($tiAdmin)
            ->get(route('people.edit', $property->person_id))
            ->assertOk();
    }

    public function test_ti_admin_sees_another_users_property_in_the_index(): void
    {
        $owner = User::factory()->attendant()->create();
        $tiAdmin = User::factory()->tiAdmin()->create();
        $property = $this->createPropertyFor($owner);

        $this->actingAs($tiAdmin)
            ->get(route('properties.index'))
            ->assertOk()
            ->assertSee('Avenida Paulista', false);

        $this->actingAs($tiAdmin)
            ->get(route('people.index'))
            ->assertOk()
            ->assertSee('Maria Silva', false);

        $this->assertTrue(
            Property::query()->visibleTo($tiAdmin)->whereKey($property->id)->exists()
        );
    }

    public function test_attendant_cannot_view_another_users_property(): void
    {
        $owner = User::factory()->attendant()->create();
        $other = User::factory()->attendant()->create();
        $property = $this->createPropertyFor($owner);

        $this->actingAs($other)
            ->get(route('properties.edit', $property))
            ->assertNotFound();
    }

    public function test_attendant_cannot_access_audit(): void
    {
        $attendant = User::factory()->attendant()->create();

        $this->actingAs($attendant)
            ->get(route('audit.index'))
            ->assertForbidden();
    }

    public function test_ti_admin_can_access_audit(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();

        $this->actingAs($tiAdmin)
            ->get(route('audit.index'))
            ->assertOk();
    }

    public function test_system_admin_can_access_audit(): void
    {
        $admin = User::factory()->systemAdmin()->create();

        $this->actingAs($admin)
            ->get(route('audit.index'))
            ->assertOk();
    }

    public function test_creating_a_person_writes_an_audit_log(): void
    {
        $user = User::factory()->attendant()->create();

        $this->actingAs($user)
            ->post(route('people.store'), [
                'name' => 'Maria Silva',
                'birth_date' => now()->subYears(25)->toDateString(),
                'cpf' => '39053344705',
                'gender' => Gender::Female->value,
                'phone' => '(11) 98765-4321',
                'email' => 'maria@example.com',
            ])
            ->assertRedirect(route('people.index'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'created',
            'auditable_type' => Person::class,
        ]);
    }
}
