<?php

namespace Tests\Feature;

use App\Enums\ActiveStatus;
use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendant_cannot_access_users_index(): void
    {
        $attendant = User::factory()->attendant()->create();

        $this->actingAs($attendant)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_system_admin_can_access_users_index(): void
    {
        $admin = User::factory()->systemAdmin()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_system_admin_can_create_attendant(): void
    {
        $admin = User::factory()->systemAdmin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Novo Atendente',
                'email' => 'novo.atendente@example.com',
                'cpf' => '52998224725',
                'password' => 'password',
                'password_confirmation' => 'password',
                'profile' => UserProfile::Attendant->value,
                'active' => ActiveStatus::Active->value,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo.atendente@example.com',
            'profile' => UserProfile::Attendant->value,
        ]);
    }

    public function test_system_admin_cannot_create_ti_admin(): void
    {
        $admin = User::factory()->systemAdmin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Novo TI',
                'email' => 'novo.ti@example.com',
                'cpf' => '39053344705',
                'password' => 'password',
                'password_confirmation' => 'password',
                'profile' => UserProfile::TiAdmin->value,
                'active' => ActiveStatus::Active->value,
            ])
            ->assertSessionHasErrors('profile');
    }

    public function test_ti_admin_can_create_system_admin(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();

        $this->actingAs($tiAdmin)
            ->post(route('users.store'), [
                'name' => 'Novo Admin Sistema',
                'email' => 'novo.admin@example.com',
                'cpf' => '15350946056',
                'password' => 'password',
                'password_confirmation' => 'password',
                'profile' => UserProfile::SystemAdmin->value,
                'active' => ActiveStatus::Active->value,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo.admin@example.com',
            'profile' => UserProfile::SystemAdmin->value,
        ]);
    }

    public function test_system_admin_cannot_edit_ti_admin(): void
    {
        $admin = User::factory()->systemAdmin()->create();
        $tiUser = User::factory()->tiAdmin()->create();

        $this->actingAs($admin)
            ->get(route('users.edit', $tiUser))
            ->assertForbidden();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->attendant()->inactive()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
