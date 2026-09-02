<?php

use App\Enums\PlotStatus;
use App\Models\Plot;
use App\Models\Program;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination, WithFileUploads;

    public Program $program;
    public string $search = '';
    public string $statusFilter = '';
    public bool $showCreate = false;

    // Quick create fields
    public string $reference = '';
    public string $surface_m2 = '';
    public string $price = '';
    public string $status = '';
    public bool $is_viabilise = true;
    public string $juridical_status = '';
    public $plan_pdf;

    // Inline edit
    public array $editing = [];

    public function mount(Program $program): void
    {
        $this->program = $program;
        $this->status = PlotStatus::DISPONIBLE->value;
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function createPlot(): void
    {
        $this->authorize('plots.create');
        $this->reference = strtoupper(trim($this->reference));
        $validated = $this->validate([
            'reference' => ['required', 'string', 'max:50', Rule::unique('plots', 'reference')->where('program_id', $this->program->id)],
            'surface_m2' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'status' => ['required', Rule::in(array_column(PlotStatus::cases(), 'value'))],
            'is_viabilise' => ['boolean'],
            'juridical_status' => ['nullable', 'string', 'max:255'],
            'plan_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
        ]);

        $data = [
            'program_id' => $this->program->id,
            'reference' => strtoupper($validated['reference']),
            'surface_m2' => $validated['surface_m2'],
            'price' => $validated['price'] ?? null,
            'status' => $validated['status'],
            'is_viabilise' => $validated['is_viabilise'],
            'juridical_status' => $validated['juridical_status'] ?? null,
        ];

        if ($this->plan_pdf) {
            $data['plan_pdf_path'] = $this->plan_pdf->store('plots/plans', 'public');
        }

        Plot::create($data);
        $this->reset(['reference', 'surface_m2', 'price', 'juridical_status', 'plan_pdf', 'showCreate']);
        $this->status = PlotStatus::DISPONIBLE->value;
        session()->flash('success', 'Lot créé.');
    }

    public function enableEdit(int $id): void
    {
        $plot = $this->program->plots()->findOrFail($id);
        $this->editing[$id] = [
            'surface_m2' => (string) $plot->surface_m2,
            'price' => $plot->price ? (string) $plot->price : '',
            'status' => $plot->status->value,
        ];
    }

    public function saveInline(int $id): void
    {
        $this->authorize('plots.update');
        $plot = $this->program->plots()->findOrFail($id);
        $data = $this->editing[$id] ?? [];
        $validated = validator($data, [
            'surface_m2' => ['required', 'numeric', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_column(PlotStatus::cases(), 'value'))],
        ])->validate();

        $plot->update([
            'surface_m2' => $validated['surface_m2'],
            'price' => $validated['price'] ?? null,
            'status' => $validated['status'],
        ]);
        unset($this->editing[$id]);
        session()->flash('success', 'Lot '.$plot->reference.' mis à jour.');
    }

    public function delete(int $id): void
    {
        $this->authorize('plots.delete');
        $this->program->plots()->findOrFail($id)->delete();
        session()->flash('success', 'Lot supprimé.');
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('plots.view');
        $plots = $this->program->plots()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('reference', 'like', '%'.$s.'%');
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('reference')
            ->paginate(20);

        return view('pages.admin.plots.index', [
            'plots' => $plots,
            'program' => $this->program,
        ]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Lots — {{ $program->title }}</flux:heading>
            <flux:text>{{ $program->city }} · {{ $program->plots()->count() }} lots · <a href="{{ route('admin.programs.index') }}" wire:navigate class="underline">← Programmes</a></flux:text>
        </div>
        @can('plots.create')
            <flux:button wire:click="$toggle('showCreate')" variant="primary" icon="plus">Nouveau lot</flux:button>
        @endcan
    </div>

    <flux:modal wire:model="showCreate" class="md:w-[720px]">
        <form wire:submit="createPlot" class="space-y-6">
            <div>
                <flux:heading size="lg">Nouveau lot — {{ $program->title }}</flux:heading>
                <flux:text class="mt-1">{{ $program->city }} · {{ $program->plots()->count() }} lots existants</flux:text>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="reference" label="Référence *" placeholder="LOT-A12" required />
                <flux:input wire:model="surface_m2" label="Surface m² *" type="number" step="0.01" required />
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="price" label="Prix (FCFA)" type="number" step="0.01" placeholder="25000000" />
                <flux:select wire:model="status" label="Statut *">
                    @foreach(PlotStatus::cases() as $s) <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option> @endforeach
                </flux:select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="juridical_status" label="Statut juridique" placeholder="ACD, Titre foncier, Attestation..." />
                <div class="flex items-end pb-2"><flux:checkbox wire:model="is_viabilise" label="Viabilisé" /></div>
            </div>
            <flux:input type="file" wire:model="plan_pdf" label="Plan PDF (max 8Mo)" accept="application/pdf" description="Stocké sur Object Storage (S3) en production" />

            @error('reference') <div class="rounded-lg bg-red-50 p-2 text-sm text-red-600">{{ $message }}</div> @enderror
            @error('plan_pdf') <div class="rounded-lg bg-red-50 p-2 text-sm text-red-600">{{ $message }}</div> @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="plus">Créer le lot</flux:button>
            </div>
        </form>
    </flux:modal>

    <div class="flex gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Référence..." class="max-w-xs" />
        <flux:select wire:model.live="statusFilter" placeholder="Statut" class="max-w-[180px]">
            <flux:select.option value="">Tous</flux:select.option>
            @foreach(PlotStatus::cases() as $s) <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option> @endforeach
        </flux:select>
    </div>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50">
                <tr class="text-left">
                    <th class="px-3 py-3">Référence</th>
                    <th class="px-3 py-3">Surface</th>
                    <th class="px-3 py-3">Prix</th>
                    <th class="px-3 py-3">Statut</th>
                    <th class="px-3 py-3">Viabilisé</th>
                    <th class="px-3 py-3">Plan</th>
                    <th class="px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plots as $plot)
                    <tr class="border-t border-zinc-100">
                        <td class="px-3 py-2 font-mono text-xs font-medium">{{ $plot->reference }}</td>
                        <td class="px-3 py-2">
                            @if(isset($editing[$plot->id]))
                                <flux:input wire:model="editing.{{ $plot->id }}.surface_m2" type="number" step="0.01" class="w-24" />
                            @else
                                {{ $plot->surface_m2 }} m²
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if(isset($editing[$plot->id]))
                                <flux:input wire:model="editing.{{ $plot->id }}.price" type="number" step="0.01" class="w-28" />
                            @else
                                {{ $plot->price ? number_format((float)$plot->price, 0, ',', ' ').' FCFA' : '—' }}
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if(isset($editing[$plot->id]))
                                <flux:select wire:model="editing.{{ $plot->id }}.status" class="w-32">
                                    @foreach(PlotStatus::cases() as $s) <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option> @endforeach
                                </flux:select>
                            @else
                                <flux:badge :color="$plot->status->badgeColor()" size="sm">{{ $plot->status->label() }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $plot->is_viabilise ? 'Oui' : 'Non' }} <span class="text-xs text-zinc-500">{{ $plot->juridical_status ?? '' }}</span></td>
                        <td class="px-3 py-2">
                            @if($plot->plan_pdf_path)
                                <a href="{{ route('plots.plan', $plot) }}" target="_blank" class="text-xs underline">PDF</a>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex gap-1">
                                @if(isset($editing[$plot->id]))
                                    <flux:button size="xs" variant="primary" wire:click="saveInline({{ $plot->id }})">Enregistrer</flux:button>
                                    <flux:button size="xs" variant="ghost" wire:click="$unset('editing.{{ $plot->id }}')">Annuler</flux:button>
                                @else
                                    @can('plots.update') <flux:button size="xs" variant="ghost" wire:click="enableEdit({{ $plot->id }})">Éditer</flux:button> @endcan
                                    @can('plots.delete') <flux:button size="xs" variant="ghost" wire:click="delete({{ $plot->id }})" wire:confirm="Supprimer ce lot ?">Supprimer</flux:button> @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-zinc-500">Aucun lot.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $plots->links() }}</div>
</section>
