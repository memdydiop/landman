<?php

use App\Models\Program;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Programmes')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $cityFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('programs.delete');
        Program::findOrFail($id)->delete();
        session()->flash('success', 'Programme supprimé.');
    }

    public function togglePublish(int $id): void
    {
        $this->authorize('programs.publish');
        $program = Program::findOrFail($id);
        $program->update([
            'is_published' => ! $program->is_published,
            'published_at' => ! $program->is_published ? now() : null,
        ]);
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('programs.view');
        $programs = Program::query()
            ->withCount(['plots', 'plots as plots_available_count' => fn ($q) => $q->where('status', \App\Enums\PlotStatus::DISPONIBLE)])
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('title', 'like', '%'.$s.'%')->orWhere('slug', 'like', '%'.$s.'%');
            })
            ->when($this->cityFilter, fn ($q) => $q->where('city', $this->cityFilter))
            ->latest()
            ->paginate(12);

        $cities = Program::select('city')->distinct()->pluck('city');

        return view('pages.admin.programs.index', [
            'programs' => $programs,
            'cities' => $cities,
        ]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Programmes (Lotissements)</flux:heading>
            <flux:text>Gestion des sites fonciers — {{ $programs->total() }} programme(s)</flux:text>
        </div>
        @can('programs.create')
            <flux:button :href="route('admin.programs.create')" wire:navigate variant="primary" icon="plus">Nouveau programme</flux:button>
        @endcan
    </div>

    <div class="flex gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher titre/slug..." class="max-w-xs" />
        <flux:select wire:model.live="cityFilter" placeholder="Filtrer par ville" class="max-w-xs">
            <flux:select.option value="">Toutes les villes</flux:select.option>
            @foreach($cities as $city)
                <flux:select.option value="{{ $city }}">{{ $city }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50">
                <tr class="text-left">
                    <th class="px-4 py-3">Programme</th>
                    <th class="px-4 py-3">Ville</th>
                    <th class="px-4 py-3">Lots</th>
                    <th class="px-4 py-3">Surface totale</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                    <tr class="border-t border-zinc-100">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $program->title }}</div>
                            <div class="text-xs text-zinc-500">{{ $program->slug }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $program->city }}</td>
                        <td class="px-4 py-3">
                            <span class="text-zinc-700">{{ $program->plots_count }} lots</span>
                            <span class="text-xs text-emerald-600">· {{ $program->plots_available_count }} dispo</span>
                        </td>
                        <td class="px-4 py-3">{{ $program->total_area ? number_format((float)$program->total_area, 0, ',', ' ').' m²' : '—' }}</td>
                        <td class="px-4 py-3">
                            @if($program->is_published)
                                <flux:badge color="emerald" size="sm">Publié</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">Brouillon</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <flux:button size="xs" variant="ghost" :href="route('admin.plots.index', $program)" wire:navigate>Lots</flux:button>
                                @can('programs.update')
                                    <flux:button size="xs" variant="ghost" :href="route('admin.programs.edit', $program)" wire:navigate>Éditer</flux:button>
                                    <flux:button size="xs" variant="ghost" wire:click="togglePublish({{ $program->id }})">{{ $program->is_published ? 'Dépublier' : 'Publier' }}</flux:button>
                                @endcan
                                @can('programs.delete')
                                    <flux:button size="xs" variant="ghost" wire:click="delete({{ $program->id }})" wire:confirm="Supprimer ce programme et ses lots ?">Supprimer</flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-zinc-500">Aucun programme.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $programs->links() }}</div>
</section>
