<?php

namespace Tests\Feature\Front;

use App\Enums\InquiryType;
use App\Enums\PlotStatus;
use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Livewire\Front\InquiryWizard;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('SIBEA-CI')->assertSee('RÉALISATIONS RÉCENTES')->assertSee('POURQUOI NOUS CHOISIR');
    }

    public function test_programs_catalog_renders_and_filters(): void
    {
        Program::factory()->create(['is_published' => true, 'city' => 'Abidjan', 'title' => 'Programme A']);
        Program::factory()->create(['is_published' => false, 'title' => 'Brouillon']);

        $this->get(route('front.programs.index'))->assertOk()->assertSee('Programme A')->assertDontSee('Brouillon');

        $this->get(route('front.programs.index', ['city' => 'Abidjan']))->assertOk()->assertSee('Programme A');
    }

    public function test_program_show_lists_plots_with_status(): void
    {
        $program = Program::factory()->create(['is_published' => true]);
        $plotAvailable = Plot::factory()->for($program)->create(['status' => PlotStatus::DISPONIBLE, 'reference' => 'LOT-TEST-AV']);
        $plotSold = Plot::factory()->for($program)->create(['status' => PlotStatus::VENDU]);

        $this->get(route('front.programs.show', $program))->assertOk()
            ->assertSee($program->title)
            ->assertSee('LOT-TEST-AV')
            ->assertSee('DISPONIBLE')
            ->assertSee('VENDU');
    }

    public function test_projects_catalog_filterable_by_service_and_status(): void
    {
        Project::factory()->create(['is_published' => true, 'service_type' => ServiceType::BTP, 'status' => ProjectStatus::LIVRE, 'title' => 'Projet BTP Livre']);
        Project::factory()->create(['is_published' => true, 'service_type' => ServiceType::AMENAGEMENT, 'status' => ProjectStatus::EN_COURS, 'title' => 'Projet VRD EnCours']);
        Project::factory()->create(['is_published' => false, 'title' => 'Brouillon']);

        $this->get(route('front.projects.index'))->assertOk()->assertSee('Projet BTP Livre')->assertSee('Projet VRD EnCours')->assertDontSee('Brouillon');

        $this->get(route('front.projects.index', ['service' => ServiceType::BTP->value]))->assertOk()->assertSee('Projet BTP Livre')->assertDontSee('Projet VRD EnCours');
    }

    public function test_project_show_displays_fiche(): void
    {
        $project = Project::factory()->create([
            'is_published' => true,
            'title' => 'Villa Test Detail',
            'slug' => 'villa-test-detail',
            'technical_sheet' => ['maitre_ouvrage' => 'Test MO'],
        ]);

        $this->get(route('front.projects.show', $project))->assertOk()->assertSee('Villa Test Detail')->assertSee('Test MO');
    }

    public function test_contact_page_renders_and_can_create_inquiry(): void
    {
        $this->get(route('front.contact'))->assertOk()->assertSee('Contact');

        $program = Program::factory()->create(['is_published' => true]);
        $plot = Plot::factory()->for($program)->create(['status' => PlotStatus::DISPONIBLE]);

        Livewire::test(InquiryWizard::class)
            ->set('inquiry_type', InquiryType::ACHAT_LOT->value)
            ->set('service_type', ServiceType::LOTISSEMENT->value)
            ->call('next')
            ->set('program_id', $program->id)
            ->set('plot_id', $plot->id)
            ->call('next')
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('message', 'Intéressé')
            ->set('rgpd', true)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('inquiries', ['email' => 'john@example.com', 'plot_id' => $plot->id]);
    }

    public function test_unpublished_program_returns_404(): void
    {
        $program = Program::factory()->create(['is_published' => false]);

        $this->get(route('front.programs.show', $program))->assertNotFound();
    }
}
