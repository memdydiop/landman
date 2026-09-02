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
    public string $view = 'grid';

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
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <flux:heading size="xl">Réalisations — BTP & Aménagement</flux:heading>
            <flux:text>{{ $projects->total() }} projet(s) · VRD & BTP SIBEA-CI</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button.group>
                <flux:button :variant="$view==='grid'?'primary':'ghost'" wire:click="$set('view','grid')" icon="squares-2x2" size="sm">Grille</flux:button>
                <flux:button :variant="$view==='list'?'primary':'ghost'" wire:click="$set('view','list')" icon="list-bullet" size="sm">Liste</flux:button>
            </flux:button.group>
            @can('projects.create')
                <flux:button :href="route('admin.projects.create')" wire:navigate variant="primary" icon="plus">Nouveau</flux:button>
            @endcan
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher titre..." icon="magnifying-glass" class="max-w-xs" />
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
        @if($search || $serviceFilter || $statusFilter)
            <flux:button variant="ghost" size="sm" wire:click="$set('search',''); $set('serviceFilter',''); $set('statusFilter','')" icon="x-mark">Effacer</flux:button>
        @endif
    </div>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    @if($view === 'grid')
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($projects as $project)
                <div class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 hover:shadow-lg transition">
                    <div class="aspect-[16/10] bg-zinc-100 relative">
                        @if($project->cover_path)
                            <img src="{{ Storage::disk('public')->url($project->cover_path) }}" alt="" class="size-full object-cover group-hover:scale-105 transition duration-700" loading="lazy" />
                        @else
                            <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop" alt="" class="size-full object-cover" />
                        @endif
                        <div class="absolute left-3 top-3 flex gap-1">
                            <flux:badge :color="$project->status->badgeColor()" size="sm">{{ $project->status->label() }}</flux:badge>
                            <flux:badge color="zinc" size="sm">{{ $project->service_type->label() }}</flux:badge>
                        </div>
                        @if($project->is_featured)<div class="absolute right-3 top-3"><flux:badge color="amber" size="sm">À la une</flux:badge></div>@endif
                        <div class="absolute bottom-3 left-3 rounded-full bg-white/90 px-2.5 py-1 text-xs backdrop-blur">{{ $project->media()->count() }} images</div>
                    </div>
                    <div class="p-4">
                        <div class="truncate text-sm font-bold">{{ $project->title }}</div>
                        <div class="truncate text-xs text-zinc-500">{{ $project->slug }} · {{ $project->year ?? '—' }}</div>
                        <div class="mt-1 text-xs text-zinc-600">{{ $project->location ?? '—' }} @if($project->surface_m2) · {{ $project->surface_m2 }} m² @endif</div>
                        <div class="mt-3 flex flex-wrap gap-1">
                            @can('projects.update')<flux:button size="xs" variant="ghost" :href="route('admin.projects.edit', $project)" wire:navigate>Éditer</flux:button><flux:button size="xs" variant="ghost" wire:click="toggleFeatured({{ $project->id }})">{{ $project->is_featured ? 'Désépingler' : 'Épingler' }}</flux:button><flux:button size="xs" variant="ghost" wire:click="togglePublish({{ $project->id }})">{{ $project->is_published ? 'Dépublier' : 'Publier' }}</flux:button>@endcan
                            @can('projects.delete')<flux:button size="xs" variant="ghost" wire:click="delete({{ $project->id }})" wire:confirm="Supprimer ?" class="text-red-600">Suppr</flux:button>@endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed bg-zinc-50 p-10 text-center"><div class="text-sm font-medium">Aucun projet</div><div class="text-xs text-zinc-500">Crée ta première réalisation BTP.</div></div>
            @endforelse
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800">
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
                        <tr class="border-t border-zinc-100 hover:bg-zinc-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 overflow-hidden rounded-lg bg-zinc-100 shrink-0">
                                        @if($project->cover_path)<img src="{{ Storage::disk('public')->url($project->cover_path) }}" class="size-full object-cover" />@else<div class="flex size-full items-center justify-center text-zinc-400"><flux:icon.photo class="size-5" /></div>@endif
                                    </div>
                                    <div class="min-w-0"><div class="truncate font-medium flex items-center gap-1">{{ $project->title }} @if($project->is_featured)<flux:badge color="amber" size="sm">★</flux:badge>@endif</div><div class="truncate text-xs text-zinc-500">{{ $project->slug }}</div></div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><flux:badge color="zinc" size="sm">{{ $project->service_type->label() }}</flux:badge></td>
                            <td class="px-4 py-3"><flux:badge :color="$project->status->badgeColor()" size="sm">{{ $project->status->label() }}</flux:badge></td>
                            <td class="px-4 py-3 text-xs">{{ $project->location ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $project->media()->count() }} img</td>
                            <td class="px-4 py-3"><div class="flex gap-1">@can('projects.update')<flux:button size="xs" variant="ghost" :href="route('admin.projects.edit', $project)" wire:navigate>Éditer</flux:button>@endcan</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-zinc-500">Aucun projet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
    <div class="mt-4">{{ $projects->links() }}</div>
</section>
