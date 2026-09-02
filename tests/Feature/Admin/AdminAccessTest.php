<?php

namespace Tests\Feature\Admin;

use App\Models\Program;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_programs_index_renders(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)->get(route('admin.programs.index'))->assertOk();
    }

    public function test_projects_index_renders(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)->get(route('admin.projects.index'))->assertOk();
    }

    public function test_plots_index_renders(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $program = Program::factory()->create();

        $this->actingAs($user)->get(route('admin.plots.index', $program))->assertOk();
    }

    public function test_inquiries_index_renders(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)->get(route('admin.inquiries.index'))->assertOk();
    }

    public function test_users_index_requires_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Editeur BTP');

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_inquiry_export_requires_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Editeur BTP'); // has inquiries.view but not export

        $this->actingAs($user)->get(route('admin.inquiries.export'))->assertForbidden();

        $commercial = User::factory()->create();
        $commercial->assignRole('Commercial Lotissement');

        $this->actingAs($commercial)->get(route('admin.inquiries.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
