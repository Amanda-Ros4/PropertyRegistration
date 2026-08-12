<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonValidationTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_CPF = '39053344705';

    private function user(): User
    {
        return User::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Maria Silva',
            'birth_date' => now()->subYears(20)->toDateString(),
            'cpf' => self::VALID_CPF,
            'gender' => Gender::Male->value,
            'phone' => '(11) 98765-4321',
            'email' => 'maria@example.com',
        ], $overrides);
    }

    public function test_person_can_be_created_with_valid_data(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('people.store'), $this->validPayload())
            ->assertRedirect(route('people.index'));

        $this->assertDatabaseHas('people', [
            'user_id' => $user->id,
            'cpf' => self::VALID_CPF,
            'email' => 'maria@example.com',
            'phone' => '11987654321',
        ]);
    }

    public function test_cpf_must_be_valid(): void
    {
        $this->actingAs($this->user())
            ->from(route('people.create'))
            ->post(route('people.store'), $this->validPayload([
                'cpf' => '11111111111',
            ]))
            ->assertSessionHasErrors('cpf');
    }

    public function test_cpf_must_be_unique_per_user(): void
    {
        $user = $this->user();

        Person::query()->create([
            'user_id' => $user->id,
            'name' => 'João',
            'birth_date' => now()->subYears(30)->toDateString(),
            'cpf' => self::VALID_CPF,
            'gender' => Gender::Male,
        ]);

        $this->actingAs($user)
            ->from(route('people.create'))
            ->post(route('people.store'), $this->validPayload([
                'email' => 'outro@example.com',
            ]))
            ->assertSessionHasErrors('cpf');
    }

    public function test_email_must_be_valid(): void
    {
        $this->actingAs($this->user())
            ->from(route('people.create'))
            ->post(route('people.store'), $this->validPayload([
                'email' => 'email-invalido',
            ]))
            ->assertSessionHasErrors('email');
    }

    public function test_email_must_be_unique_per_user(): void
    {
        $user = $this->user();

        Person::query()->create([
            'user_id' => $user->id,
            'name' => 'João',
            'birth_date' => now()->subYears(30)->toDateString(),
            'cpf' => '52998224725',
            'gender' => Gender::Male,
            'email' => 'maria@example.com',
        ]);

        $this->actingAs($user)
            ->from(route('people.create'))
            ->post(route('people.store'), $this->validPayload())
            ->assertSessionHasErrors('email');
    }

    public function test_phone_must_match_brazilian_mobile_format(): void
    {
        $this->actingAs($this->user())
            ->from(route('people.create'))
            ->post(route('people.store'), $this->validPayload([
                'phone' => '(11) 3456-7890',
            ]))
            ->assertSessionHasErrors('phone');
    }

    public function test_person_must_be_at_least_18_years_old(): void
    {
        $this->actingAs($this->user())
            ->from(route('people.create'))
            ->post(route('people.store'), $this->validPayload([
                'birth_date' => now()->subYears(17)->toDateString(),
            ]))
            ->assertSessionHasErrors('birth_date');
    }

    public function test_precognition_validates_cpf_without_creating_the_person(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->withHeaders([
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'cpf',
            ])
            ->postJson(route('people.store'), [
                'cpf' => '11111111111',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpf');

        $this->assertDatabaseCount('people', 0);
    }

    public function test_precognition_accepts_a_valid_cpf(): void
    {
        $this->actingAs($this->user())
            ->withHeaders([
                'Precognition' => 'true',
                'Precognition-Validate-Only' => 'cpf',
            ])
            ->postJson(route('people.store'), [
                'cpf' => self::VALID_CPF,
            ])
            ->assertNoContent();
    }
}
