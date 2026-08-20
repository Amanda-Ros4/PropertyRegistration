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

    public function test_system_admin_can_create_attendant_with_active_status(): void
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
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo.atendente@example.com',
            'profile' => UserProfile::Attendant->value,
            'active' => ActiveStatus::Active->value,
            'email_verified_at' => null,
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
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo.admin@example.com',
            'profile' => UserProfile::SystemAdmin->value,
        ]);
    }

    public function test_system_admin_can_view_but_not_update_ti_admin(): void
    {
        $admin = User::factory()->systemAdmin()->create();
        $tiUser = User::factory()->tiAdmin()->create();

        $this->actingAs($admin)
            ->get(route('users.edit', $tiUser))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('users.update', $tiUser), [
                'name' => 'Nome Alterado',
                'profile' => UserProfile::TiAdmin->value,
                'active' => ActiveStatus::Active->value,
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_be_deleted(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();
        $attendant = User::factory()->attendant()->create();

        $this->actingAs($tiAdmin)
            ->delete('/users/'.$attendant->id)
            ->assertStatus(405);

        $this->assertDatabaseHas('users', ['id' => $attendant->id]);
    }

    public function test_ti_admin_user_edit_page_allows_email_editing(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();
        $attendant = User::factory()->attendant()->create();

        $this->actingAs($tiAdmin)
            ->get(route('users.edit', $attendant))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Edit')
                ->where('canEditEmail', true)
                ->where('canUpdate', true)
                ->has('user.email')
            );
    }

    public function test_ti_admin_can_update_email_but_not_cpf(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();
        $attendant = User::factory()->attendant()->create([
            'email' => 'original@example.com',
            'cpf' => '52998224725',
        ]);

        $this->actingAs($tiAdmin)
            ->put(route('users.update', $attendant), [
                'name' => 'Nome Atualizado',
                'email' => 'novo@example.com',
                'cpf' => '39053344705',
                'profile' => UserProfile::Attendant->value,
            ])
            ->assertRedirect(route('users.index'));

        $attendant->refresh();

        $this->assertSame('Nome Atualizado', $attendant->name);
        $this->assertSame('novo@example.com', $attendant->email);
        $this->assertSame('52998224725', $attendant->cpf);
    }

    public function test_system_admin_cannot_update_email_or_cpf(): void
    {
        $admin = User::factory()->systemAdmin()->create();
        $attendant = User::factory()->attendant()->create([
            'email' => 'original@example.com',
            'cpf' => '52998224725',
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $attendant), [
                'name' => 'Nome Atualizado',
                'email' => 'novo@example.com',
                'cpf' => '39053344705',
                'profile' => UserProfile::Attendant->value,
            ])
            ->assertRedirect(route('users.index'));

        $attendant->refresh();

        $this->assertSame('Nome Atualizado', $attendant->name);
        $this->assertSame('original@example.com', $attendant->email);
        $this->assertSame('52998224725', $attendant->cpf);
    }

    public function test_ti_admin_can_change_user_profile(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();
        $attendant = User::factory()->attendant()->create();

        $this->actingAs($tiAdmin)
            ->put(route('users.update', $attendant), [
                'name' => $attendant->name,
                'email' => $attendant->email,
                'profile' => UserProfile::SystemAdmin->value,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertSame(UserProfile::SystemAdmin, $attendant->fresh()->profile);
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

    public function test_user_id_is_auto_generated_sequentially(): void
    {
        $admin = User::factory()->systemAdmin()->create();
        $firstId = $admin->id;

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Atendente Sequencial',
                'email' => 'sequencial@example.com',
                'cpf' => '15350946056',
                'password' => 'password',
                'password_confirmation' => 'password',
                'profile' => UserProfile::Attendant->value,
            ])
            ->assertRedirect(route('users.index'));

        $created = User::query()->where('email', 'sequencial@example.com')->first();

        $this->assertNotNull($created);
        $this->assertSame($firstId + 1, $created->id);
    }

    public function test_user_id_cannot_be_set_manually_on_create(): void
    {
        $admin = User::factory()->systemAdmin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'id' => 9999,
                'name' => 'Atendente Manual Id',
                'email' => 'manual.id@example.com',
                'cpf' => '39053344705',
                'password' => 'password',
                'password_confirmation' => 'password',
                'profile' => UserProfile::Attendant->value,
            ])
            ->assertSessionHasErrors('id');
    }

    public function test_ti_admin_can_deactivate_user_via_active_endpoint(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();
        $attendant = User::factory()->attendant()->create();

        $this->actingAs($tiAdmin)
            ->from(route('users.edit', $attendant))
            ->patch(route('users.active.update', $attendant), [
                'active' => ActiveStatus::Inactive->value,
            ])
            ->assertRedirect(route('users.edit', $attendant));

        $this->assertSame(ActiveStatus::Inactive, $attendant->fresh()->active);
    }

    public function test_user_cannot_deactivate_self_via_active_endpoint(): void
    {
        $tiAdmin = User::factory()->tiAdmin()->create();

        $this->actingAs($tiAdmin)
            ->from(route('users.edit', $tiAdmin))
            ->patch(route('users.active.update', $tiAdmin), [
                'active' => ActiveStatus::Inactive->value,
            ])
            ->assertSessionHasErrors('active');

        $this->assertSame(ActiveStatus::Active, $tiAdmin->fresh()->active);
    }
}
