<?php

declare(strict_types=1);

namespace App\Livewire\Front;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Enums\PlotStatus;
use App\Enums\ServiceType;
use App\Mail\InquiryAdminNotification;
use App\Mail\InquiryClientConfirmation;
use App\Models\Inquiry;
use App\Models\Plot;
use App\Models\Program;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class InquiryWizard extends Component
{
    public int $step = 1;

    public string $inquiry_type = '';

    public string $service_type = '';

    public string $name = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $company = '';

    public string $email = '';

    public string $phone = '';

    public string $message = '';

    public ?int $program_id = null;

    public ?int $plot_id = null;

    public string $budget = '';

    public string $budget_range = '';

    public string $surface_wanted = '';

    public string $project_size = '';

    public string $project_type = '';

    public string $location = '';

    public string $deadline = '';

    /** @var string[] */
    public array $services_needed = [];

    public bool $rgpd = false;

    public bool $sent = false;

    public ?int $createdInquiryId = null;

    public string $website = ''; // honeypot

    public function mount(): void
    {
        $type = request()->query('type');
        if ($type && in_array($type, array_column(InquiryType::cases(), 'value'), true)) {
            $this->inquiry_type = $type;
        } else {
            $this->inquiry_type = InquiryType::CONTACT->value;
        }

        if (request()->query('plot')) {
            $this->plot_id = (int) request()->query('plot');
            $this->inquiry_type = InquiryType::ACHAT_LOT->value;
            $plot = Plot::with('program')->find($this->plot_id);
            if ($plot) {
                $this->program_id = $plot->program_id;
                $this->service_type = ServiceType::LOTISSEMENT->value;
                $this->message = "Intéressé(e) par le lot {$plot->reference} — {$plot->program->title}";
            }
        }

        if (request()->query('program')) {
            $this->program_id = (int) request()->query('program');
            if (! $this->plot_id) {
                $this->inquiry_type = InquiryType::ACHAT_LOT->value;
                $this->service_type = ServiceType::LOTISSEMENT->value;
            }
        }

        if (request()->query('project')) {
            $this->inquiry_type = InquiryType::DEVIS_BTP->value;
            $this->service_type = ServiceType::BTP->value;
        }

        if (empty($this->service_type)) {
            $this->service_type = match ($this->inquiry_type) {
                InquiryType::DEVIS_BTP->value => ServiceType::BTP->value,
                InquiryType::ACHAT_LOT->value => ServiceType::LOTISSEMENT->value,
                default => '',
            };
        }
    }

    /**
     * @return Collection<int, Program>
     */
    #[Computed]
    public function programs(): Collection
    {
        return Program::published()->withCount(['plots as available_plots_count' => fn ($q) => $q->where('status', PlotStatus::DISPONIBLE)])->orderBy('title')->get();
    }

    /**
     * @return Collection<int, Plot>
     */
    #[Computed]
    public function plotsForProgram(): Collection
    {
        if (! $this->program_id) {
            /** @var Collection<int, Plot> $empty */
            $empty = new Collection;

            return $empty;
        }

        // M6 : ne proposer que les lots disponibles (vendus désactivés côté UI)
        return Plot::where('program_id', $this->program_id)
            ->where('status', PlotStatus::DISPONIBLE)
            ->orderBy('reference')
            ->get();
    }

    /**
     * @return Collection<int, Plot>
     */
    #[Computed]
    public function allPlotsForProgram(): Collection
    {
        if (! $this->program_id) {
            return new Collection;
        }

        return Plot::where('program_id', $this->program_id)->orderBy('reference')->get();
    }

    #[Computed]
    public function selectedPlot(): ?Plot
    {
        return $this->plot_id ? Plot::with('program')->find($this->plot_id) : null;
    }

    public function next(): void
    {
        $this->validateStep($this->step);
        $this->step++;

        if ($this->step === 2 && $this->inquiry_type === InquiryType::CONTACT->value) {
            // Contact general skips details, go to step 3
            // keep service_type optional
        }
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function updatedInquiryType(string $value): void
    {
        $this->service_type = match ($value) {
            InquiryType::DEVIS_BTP->value => ServiceType::BTP->value,
            InquiryType::ACHAT_LOT->value => ServiceType::LOTISSEMENT->value,
            InquiryType::PARTENARIAT->value => ServiceType::AMENAGEMENT->value,
            default => '',
        };

        if ($value !== InquiryType::ACHAT_LOT->value) {
            $this->program_id = null;
            $this->plot_id = null;
        }
    }

    public function updatedProgramId(mixed $value): void
    {
        $this->plot_id = null;
    }

    protected function validateStep(int $step): void
    {
        if ($step === 1) {
            $this->validate([
                'inquiry_type' => ['required', Rule::in(array_column(InquiryType::cases(), 'value'))],
                'service_type' => ['nullable', Rule::in(array_column(ServiceType::cases(), 'value'))],
            ]);
        }

        if ($step === 2) {
            if ($this->inquiry_type === InquiryType::DEVIS_BTP->value) {
                $this->validate([
                    'service_type' => ['required', Rule::in(array_column(ServiceType::cases(), 'value'))],
                    'project_type' => ['nullable', 'string', 'max:50'],
                    'project_size' => ['nullable', 'string', 'max:50'],
                    'services_needed' => ['nullable', 'array'],
                    'services_needed.*' => ['string', Rule::in(['Génie Civil', 'Lotissement et aménagement', 'Conception', 'Rénovation', 'Gestion de projet'])],
                    'deadline' => ['nullable', 'string', 'max:50'],
                    'budget_range' => ['nullable', 'string', 'max:50'],
                    'location' => ['nullable', 'string', 'max:255'],
                    'surface_wanted' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
                    'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
                ]);
            }

            if ($this->inquiry_type === InquiryType::ACHAT_LOT->value) {
                $this->validate([
                    'program_id' => ['required', 'exists:programs,id'],
                    'plot_id' => ['nullable', Rule::exists('plots', 'id')->where(fn ($q) => $q->where('program_id', $this->program_id ?? -1))],
                    'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
                    'budget_range' => ['nullable', 'string', 'max:50'],
                ]);
            }

            // Validation générique taille/délai pour autres types
            if (! in_array($this->inquiry_type, [InquiryType::DEVIS_BTP->value, InquiryType::ACHAT_LOT->value], true)) {
                $this->validate([
                    'project_size' => ['nullable', 'string', 'max:50'],
                    'deadline' => ['nullable', 'string', 'max:50'],
                    'budget_range' => ['nullable', 'string', 'max:50'],
                ]);
            }
        }
    }

    public function submit(): void
    {
        // Honeypot
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }

        // Rate limit 5/min/IP
        $key = 'inquiry:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Trop de demandes. Réessayez dans '.RateLimiter::availableIn($key).'s.');

            return;
        }
        RateLimiter::hit($key, 60);

        // Prénom/Nom split — fallback name
        if (empty(trim($this->name)) && (! empty(trim($this->first_name)) || ! empty(trim($this->last_name)))) {
            $this->name = trim($this->first_name.' '.$this->last_name);
        }

        $this->validate([
            'inquiry_type' => ['required', Rule::in(array_column(InquiryType::cases(), 'value'))],
            'service_type' => ['nullable', Rule::in(array_column(ServiceType::cases(), 'value'))],
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-\(\)]{8,30}$/'],
            'message' => ['nullable', 'string', 'max:5000'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'plot_id' => ['nullable', Rule::exists('plots', 'id')->where(fn ($q) => $q->where('program_id', $this->program_id ?? -1))],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'budget_range' => ['nullable', 'string', 'max:50'],
            'surface_wanted' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'project_size' => ['nullable', 'string', 'max:50'],
            'project_type' => ['nullable', 'string', 'max:50'],
            'deadline' => ['nullable', 'string', 'max:50'],
            'services_needed' => ['nullable', 'array'],
            'services_needed.*' => ['string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'rgpd' => ['accepted'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        $ip = request()->ip();
        $ipHash = $ip ? hash('sha256', $ip.config('app.key')) : null;

        $meta = [
            'source' => 'front-wizard',
            'ip_hash' => $ipHash,
            'budget' => $this->budget ?: null,
            'budget_range' => $this->budget_range ?: null,
            'surface_wanted' => $this->surface_wanted ?: null,
            'project_size' => $this->project_size ?: null,
            'project_type' => $this->project_type ?: null,
            'deadline' => $this->deadline ?: null,
            'services_needed' => ! empty($this->services_needed) ? $this->services_needed : null,
            'company' => $this->company ?: null,
            'first_name' => $this->first_name ?: null,
            'last_name' => $this->last_name ?: null,
            'location' => $this->location ?: null,
            'step' => $this->step,
            'imported_from' => 'tunnel-4-etapes',
        ];

        $inquiry = Inquiry::create([
            'inquiry_type' => $this->inquiry_type,
            'service_type' => $this->service_type ?: null,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'message' => $this->message ?: null,
            'program_id' => $this->program_id,
            'plot_id' => $this->plot_id,
            'status' => InquiryStatus::NOUVEAU,
            'meta' => array_filter($meta, fn (mixed $v): bool => filled($v)),
        ]);

        $this->createdInquiryId = $inquiry->id;
        $this->sent = true;

        // Emails métier — queue via Managed Queues (scale-to-zero, 0€ vide)
        try {
            $adminEmail = config('mail.admin') ?? env('MAIL_ADMIN', env('MAIL_FROM_ADDRESS'));
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new InquiryAdminNotification($inquiry));
            }
            if (filled($inquiry->email)) {
                Mail::to($inquiry->email)->queue(new InquiryClientConfirmation($inquiry));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function resetWizard(): void
    {
        $this->reset(['step', 'name', 'first_name', 'last_name', 'company', 'email', 'phone', 'message', 'budget', 'budget_range', 'surface_wanted', 'project_size', 'project_type', 'deadline', 'services_needed', 'location', 'rgpd', 'sent', 'createdInquiryId', 'website', 'program_id', 'plot_id', 'inquiry_type', 'service_type']);
        $this->step = 1;
        $this->inquiry_type = InquiryType::CONTACT->value;
        $this->service_type = '';
    }

    public function render(): View
    {
        return view('livewire.front.inquiry-wizard');
    }
}
