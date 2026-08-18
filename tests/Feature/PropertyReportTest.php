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

class PropertyReportTest extends TestCase
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
            'email' => 'maria@example.com',
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

    public function test_user_can_download_synthetic_property_report(): void
    {
        $user = User::factory()->attendant()->create();
        $this->createPropertyFor($user);

        $response = $this->actingAs($user)
            ->get(route('properties.report.synthetic'));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_user_can_download_individual_property_report(): void
    {
        $user = User::factory()->attendant()->create();
        $property = $this->createPropertyFor($user);

        $response = $this->actingAs($user)
            ->get(route('properties.report.individual', $property));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_attendant_cannot_download_another_users_individual_report(): void
    {
        $owner = User::factory()->attendant()->create();
        $other = User::factory()->attendant()->create();
        $property = $this->createPropertyFor($owner);

        $this->actingAs($other)
            ->get(route('properties.report.individual', $property))
            ->assertNotFound();
    }
}
