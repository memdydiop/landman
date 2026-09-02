<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_super_admin_can_create_user_with_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $this->actingAs($admin);

        Livewire::test('pages::admin.users.form')
            ->set('name', 'Nouvel User')
            ->set('email', 'nouvel@landman.ci')
            ->set('password', 'Str0ng!Pass123')
            ->set('password_confirmation', 'Str0ng!Pass123')
            ->set('roles', ['Editeur BTP'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'nouvel@landman.ci']);
        $user = User::whereEmail('nouvel@landman.ci')->first();
        $this->assertTrue($user->hasRole('Editeur BTP'));
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Editeur BTP');
        $this->actingAs($user);

        Livewire::test('pages::admin.users.form')
            ->assertForbidden();
    }

    public function test_password_must_be_strong(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $this->actingAs($admin);

        Livewire::test('pages::admin.users.form')
            ->set('name', 'Test')
            ->set('email', 'test@landman.ci')
            ->set('password', 'weak')
            ->set('password_confirmation', 'weak')
            ->call('save')
            ->assertHasErrors(['password']);
    }
}
