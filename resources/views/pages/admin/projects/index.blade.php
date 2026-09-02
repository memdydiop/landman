<?php

use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Projets')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $serviceFilter = '';
    public string $statusFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        $this->authorize('projects.delete');
        Project::findOrFail($id)->delete();
        session()->flash('success', 'Projet supprimé.');
    }

    public function togglePublish(int $id): void
    {
        $this->authorize('projects.publish');
        $p = Project::findOrFail($id);
        $p->update(['is_published' => ! $p->is_published, 'published_at' => ! $p->is_published ? now() : null]);
    }

    public function toggleFeatured(int $id): void
    {
        $this->authorize('projects.update');
        $p = Project::findOrFail($id);
        $p->update(['is_featured' => ! $p->is_featured]);
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('projects.view');
        $projects = Project::query()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('title', 'like', '%'.$s.'%');
            })
            ->when($this->serviceFilter, fn ($q) => $q->where('service_type', $this->serviceFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(12);

        return view('pages.admin.projects.index', ['projects' => $projects]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Réalisations BTP & Aménagement</flux:heading>
            <flux:text>Galerie filtrable par ServiceType / ProjectStatus — {{ $projects->total() }} projet(s)</flux:text>
        </div>
        @can('projects.create')
            <flux:button :href="route('admin.projects.create')" wire:navigate variant="primary" icon="plus">Nouveau projet</flux:button>
        @endcan
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="max-w-xs" />
        <flux:select wire:model.live="serviceFilter" placeholder="Service" class="max-w-[180px]">
            <flux:select.option value="">Tous services</flux:select.option>
            @foreach(\App\Enums\ServiceType::cases() as $case)
                <flux:select.option value="{{ $case->value }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="statusFilter" placeholder="Statut" class="max-w-[160px]">
            <flux:select.option value="">Tous statuts</flux:select.option>
            @foreach(\App\Enums\ProjectStatus::cases() as $case)
                <flux:select.option value="{{ $case->value }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50">
                <tr class="text-left">
                    <th class="px-4 py-3">Projet</th>
                    <th class="px-4 py-3">Service</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Localisation</th>
                    <th class="px-4 py-3">Média</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr class="border-t border-zinc-100">
                        <td class="px-4 py-3">
                            <div class="font-medium flex items-center gap-2">
                                {{ $project->title }}
                                @if($project->is_featured) <flux:badge color="amber" size="sm">À la une</flux:badge> @endif
                            </div>
                            <div class="text-xs text-zinc-500">{{ $project->slug }} · {{ $project->year ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3"><flux:badge color="zinc" size="sm">{{ $project->service_type->label() }}</flux:badge></td>
                        <td class="px-4 py-3"><flux:badge :color="$project->status->badgeColor()" size="sm">{{ $project->status->label() }}</flux:badge></td>
                        <td class="px-4 py-3">{{ $project->location ?? '—' }} @if($project->surface_m2) <span class="text-xs text-zinc-500">· {{ $project->surface_m2 }} m²</span> @endif</td>
                        <td class="px-4 py-3">{{ $project->media()->count() }} images</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @can('projects.update')
                                    <flux:button size="xs" variant="ghost" :href="route('admin.projects.edit', $project)" wire:navigate>Éditer</flux:button>
                                    <flux:button size="xs" variant="ghost" wire:click="toggleFeatured({{ $project->id }})">{{ $project->is_featured ? 'Désépingler' : 'Épingler' }}</flux:button>
                                    <flux:button size="xs" variant="ghost" wire:click="togglePublish({{ $project->id }})">{{ $project->is_published ? 'Dépublier' : 'Publier' }}</flux:button>
                                @endcan
                                @can('projects.delete')
                                    <flux:button size="xs" variant="ghost" wire:click="delete({{ $project->id }})" wire:confirm="Supprimer ce projet ?">Supprimer</flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-zinc-500">Aucun projet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $projects->links() }}</div>
</section>
