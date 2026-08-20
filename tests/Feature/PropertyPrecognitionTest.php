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

class PropertyPrecognitionTest extends TestCase
{
    use RefreshDatabase;

    private function createProperty(): Property
    {
        $user = User::factory()->create();

        $person = Person::query()->create([
            'user_id' => $user->id,
            'name' => 'Maria Silva',
            'birth_date' => now()->subYears(30)->toDateString(),
            'cpf' => '39053344705',
            'gender' => Gender::Female,
        ]);

        return Property::query()->create([
            'user_id' => $user->id,
            'person_id' => $person->id,
            'type' => PropertyType::House,
            'land_area' => 100,
            'building_area' => 80,
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'neighborhood' => 'Bela Vista',
            'status' => PropertyStatus::Active,
        ]);
    }

    public function test_precognition_validates_property_number_without_creating(): void
    {
        $user = User::factory()->create();

        $person = Person::query()->create([
            'user_id' => $user->id,
            'name' => 'Maria Silva',
            'birth_date' => now()->subYears(30)->toDateString(),
            'cpf' => '39053344705',
            'gender' => Gender::Female,
        ]);

        $this->actingAs($user)
            ->withHeaders([
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'number',
            ])
            ->postJson(route('properties.store'), [
                'person_id' => $person->id,
                'type' => PropertyType::House->value,
                'land_area' => '100',
                'building_area' => '80',
                'street' => 'Rua A',
                'number' => 'abc',
                'neighborhood' => 'Centro',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('number');

        $this->assertDatabaseCount('properties', 0);
    }

    public function test_precognition_accepts_valid_property_street(): void
    {
        $user = User::factory()->create();

        $person = Person::query()->create([
            'user_id' => $user->id,
            'name' => 'Maria Silva',
            'birth_date' => now()->subYears(30)->toDateString(),
            'cpf' => '39053344705',
            'gender' => Gender::Female,
        ]);

        $this->actingAs($user)
            ->withHeaders([
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'street',
            ])
            ->postJson(route('properties.store'), [
                'person_id' => $person->id,
                'type' => PropertyType::House->value,
                'land_area' => '100',
                'building_area' => '80',
                'street' => 'Rua das Flores',
                'number' => '10',
                'neighborhood' => 'Centro',
            ])
            ->assertNoContent();
    }

    public function test_precognition_validates_property_update_street(): void
    {
        $property = $this->createProperty();
        $user = User::query()->findOrFail($property->user_id);

        $this->actingAs($user)
            ->withHeaders([
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'street',
            ])
            ->putJson(route('properties.update', $property), [
                'person_id' => $property->person_id,
                'type' => PropertyType::House->value,
                'street' => '',
                'number' => '10',
                'neighborhood' => 'Centro',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('street');
    }

    public function test_precognition_validates_endorsement_description(): void
    {
        $property = $this->createProperty();
        $user = User::query()->findOrFail($property->user_id);

        $this->actingAs($user)
            ->withHeaders([
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'description',
            ])
            ->postJson(route('properties.endorsements.store', $property), [
                'event' => 'O',
                'description' => '',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('description');

        $this->assertDatabaseCount('property_endorsements', 0);
    }

    public function test_precognition_accepts_valid_endorsement_description(): void
    {
        $property = $this->createProperty();
        $user = User::query()->findOrFail($property->user_id);

        $this->actingAs($user)
            ->withHeaders([
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'description',
            ])
            ->postJson(route('properties.endorsements.store', $property), [
                'event' => 'O',
                'description' => 'Observação registrada.',
            ])
            ->assertNoContent();
    }
}
