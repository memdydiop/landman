<?php

namespace Tests\Feature\Admin;

use App\Enums\InquiryStatus;
use App\Enums\PlotStatus;
use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Inquiry;
use App\Models\Program;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_can_create_program(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin);

        Livewire::test('pages::admin.programs.form')
            ->set('title', 'Les Jardins de Cocody')
            ->set('slug', 'les-jardins-de-yoff')
            ->set('city', 'Abidjan')
            ->set('address', 'Cocody Riviera')
            ->set('total_area', '15000')
            ->set('description', 'Programme test')
            ->set('is_published', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('programs', [
            'slug' => 'les-jardins-de-yoff',
            'city' => 'Abidjan',
        ]);
    }

    public function test_can_create_project_with_enums(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        Livewire::test('pages::admin.projects.form')
            ->set('title', 'Villa Test')
            ->set('slug', 'villa-test')
            ->set('service_type', ServiceType::BTP->value)
            ->set('status', ProjectStatus::LIVRE->value)
            ->set('location', 'Abidjan')
            ->set('surface_m2', '350')
            ->set('is_published', true)
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::where('slug', 'villa-test')->first();
        $this->assertNotNull($project);
        $this->assertTrue($project->service_type === ServiceType::BTP);
        $this->assertTrue($project->status === ProjectStatus::LIVRE);
    }

    public function test_can_create_plot_and_quick_update(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $program = Program::factory()->create();

        // Create via component
        $component = Livewire::test('pages::admin.plots.index', ['program' => $program])
            ->set('reference', 'LOT-TEST01')
            ->set('surface_m2', '400')
            ->set('price', '5000000')
            ->set('status', PlotStatus::DISPONIBLE->value)
            ->call('createPlot')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('plots', ['reference' => 'LOT-TEST01', 'program_id' => $program->id]);

        $plot = $program->plots()->where('reference', 'LOT-TEST01')->first();

        // Inline edit
        Livewire::test('pages::admin.plots.index', ['program' => $program])
            ->call('enableEdit', $plot->id)
            ->set('editing.'.$plot->id.'.price', '6000000')
            ->set('editing.'.$plot->id.'.status', PlotStatus::RESERVE->value)
            ->call('saveInline', $plot->id)
            ->assertHasNoErrors();

        $plot->refresh();
        $this->assertEquals('6000000.00', $plot->price);
        $this->assertTrue($plot->status === PlotStatus::RESERVE);
    }

    public function test_inquiry_status_update_and_export(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $inquiry = Inquiry::factory()->create(['status' => InquiryStatus::NOUVEAU]);

        Livewire::test('pages::admin.inquiries.index')
            ->call('updateStatus', $inquiry->id, InquiryStatus::TRAITE->value)
            ->assertHasNoErrors();

        $inquiry->refresh();
        $this->assertTrue($inquiry->status === InquiryStatus::TRAITE);

        $response = $this->get(route('admin.inquiries.export'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_user_role_assignment(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $user = User::factory()->create();

        Livewire::test('pages::admin.users.index')
            ->call('toggleRole', $user->id, 'Commercial Lotissement')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue($user->hasRole('Commercial Lotissement'));
    }
}
