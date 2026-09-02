<?php

use App\Models\Program;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Programmes')] class extends Component {
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $cityFilter = '';
    public string $view = 'grid';

    public bool $showCreate = false;
    public bool $showEdit = false;
    public ?int $editingId = null;
    public string $title = '';
    public string $slug = '';
    public string $city = '';
    public string $address = '';
    public string $total_area = '';
    public string $description = '';
    public bool $is_published = false;
    public $cover;
    public ?string $existing_cover = null;

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

    public function updatedTitle(string $value): void
    {
        if (!$this->editingId && !$this->showEdit) {
            $this->slug = Str::slug($value);
        }
    }

    public function createProgram(): void
    {
        $this->authorize('programs.create');
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('programs', 'slug')],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_published' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ]);
        $data = [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'city' => $validated['city'],
            'address' => $validated['address'] ?? null,
            'total_area' => $validated['total_area'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? now() : null,
        ];
        if ($this->cover) {
            $data['cover_path'] = ImageService::storeOptimized($this->cover, 'programs/covers');
        }
        Program::create($data);
        $this->reset(['showCreate', 'title', 'slug', 'city', 'address', 'total_area', 'description', 'is_published', 'cover', 'existing_cover']);
        session()->flash('success', 'Programme créé.');
    }

    public function startEdit(int $id): void
    {
        $this->authorize('programs.update');
        $prog = Program::findOrFail($id);
        $this->editingId = $prog->id;
        $this->title = $prog->title;
        $this->slug = $prog->slug;
        $this->city = $prog->city;
        $this->address = $prog->address ?? '';
        $this->total_area = $prog->total_area ? (string) $prog->total_area : '';
        $this->description = $prog->description ?? '';
        $this->is_published = $prog->is_published;
        $this->existing_cover = $prog->cover_path;
        $this->cover = null;
        $this->showEdit = true;
        $this->showCreate = false;
    }

    public function updateProgram(): void
    {
        $this->authorize('programs.update');
        if (!$this->editingId) return;
        $prog = Program::findOrFail($this->editingId);
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('programs', 'slug')->ignore($prog->id)],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_published' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ]);
        $data = [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'city' => $validated['city'],
            'address' => $validated['address'] ?? null,
            'total_area' => $validated['total_area'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? ($prog->published_at ?? now()) : null,
        ];
        if ($this->cover) {
            $data['cover_path'] = ImageService::storeOptimized($this->cover, 'programs/covers', 'public', $prog->cover_path);
        }
        $prog->update($data);
        $this->reset(['showEdit', 'editingId', 'title', 'slug', 'city', 'address', 'total_area', 'description', 'is_published', 'cover', 'existing_cover']);
        session()->flash('success', 'Programme mis à jour.');
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
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <flux:heading size="xl">Programmes — Lotissements</flux:heading>
            <flux:text>{{ $programs->total() }} programme(s) · {{ $cities->count() }} ville(s) · gestion foncière Bingerville & régions</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button.group>
                <flux:button :variant="$view==='grid'?'primary':'ghost'" wire:click="$set('view','grid')" icon="squares-2x2" size="sm">Grille</flux:button>
                <flux:button :variant="$view==='list'?'primary':'ghost'" wire:click="$set('view','list')" icon="list-bullet" size="sm">Liste</flux:button>
            </flux:button.group>
            @can('programs.create')
                <flux:button wire:click="$set('showCreate', true)" variant="primary" icon="plus">Nouveau</flux:button>
            @endcan
        </div>
    </div>

    {{-- Stats rapides --}}
    <div class="mb-4 grid gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-3 flex items-center gap-3">
            <div class="flex size-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600"><flux:icon.map-pin class="size-5" /></div>
            <div><div class="text-xs text-zinc-500">Programmes</div><div class="text-lg font-black">{{ $programs->total() }}</div></div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-3 flex items-center gap-3">
            <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><flux:icon.check-circle class="size-5" /></div>
            <div><div class="text-xs text-zinc-500">Villes</div><div class="text-lg font-black">{{ $cities->count() }}</div></div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-3">
            <div class="text-xs text-zinc-500">Total lots</div><div class="text-lg font-black">{{ $programs->sum('plots_count') }} <span class="text-xs font-normal text-emerald-600">{{ $programs->sum('plots_available_count') }} dispo</span></div>
        </div>
        <div class="rounded-xl border border-[#99b3cc] bg-[#f0f4f8] p-3">
            <div class="text-xs text-[#001a33]">Filtre actif</div><div class="text-sm font-semibold text-[#002244] truncate">{{ $cityFilter ?: 'Toutes villes' }} · {{ $search ?: 'sans recherche' }}</div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher titre, slug..." icon="magnifying-glass" class="max-w-xs" />
        <flux:select wire:model.live="cityFilter" placeholder="Ville" class="max-w-[180px]">
            <flux:select.option value="">Toutes les villes</flux:select.option>
            @foreach($cities as $city)
                <flux:select.option value="{{ $city }}">{{ $city }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $cityFilter)
            <flux:button variant="ghost" size="sm" wire:click="$set('search',''); $set('cityFilter','')" icon="x-mark">Effacer</flux:button>
        @endif
    </div>

    <flux:modal wire:model="showCreate" class="md:w-[640px]">
        <form wire:submit="createProgram" class="space-y-6">
            <div>
                <flux:heading size="lg">Nouveau programme</flux:heading>
                <flux:text>Lotissement — Bingerville & régions</flux:text>
            </div>
            <flux:input wire:model="title" label="Titre *" placeholder="Les Jardins de Cocody" required />
            <flux:input wire:model="slug" label="Slug *" description="URL: /lotissements/{{ $slug ?: 'mon-programme' }}" required />
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="city" label="Ville *" placeholder="Bingerville" required />
                <flux:input wire:model="total_area" label="Surface totale (m²)" type="number" step="0.01" />
            </div>
            <flux:input wire:model="address" label="Adresse" placeholder="Abatta, Lot 935..." />
            <flux:textarea wire:model="description" label="Description" rows="3" />
            <flux:checkbox wire:model="is_published" label="Publié (visible front)" />
            <flux:input type="file" wire:model="cover" label="Couverture (4Mo)" accept="image/*" />
            @error('cover') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Créer</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showEdit" class="md:w-[640px]">
        <form wire:submit="updateProgram" class="space-y-6">
            <div>
                <flux:heading size="lg">Éditer programme</flux:heading>
                <flux:text>Modifications instantanées — SIBEA-CI</flux:text>
            </div>
            <flux:input wire:model="title" label="Titre *" required />
            <flux:input wire:model="slug" label="Slug *" required />
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="city" label="Ville *" required />
                <flux:input wire:model="total_area" label="Surface totale (m²)" type="number" step="0.01" />
            </div>
            <flux:input wire:model="address" label="Adresse" />
            <flux:textarea wire:model="description" label="Description" rows="3" />
            <flux:checkbox wire:model="is_published" label="Publié" />
            @if($existing_cover)<div class="text-xs text-zinc-500">Actuel: {{ $existing_cover }} — <a href="{{ Storage::disk('public')->url($existing_cover) }}" target="_blank" class="underline">voir</a></div>@endif
            <flux:input type="file" wire:model="cover" label="Nouvelle couverture (4Mo)" accept="image/*" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost" wire:click="$set('showEdit', false)">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Mettre à jour</flux:button>
            </div>
        </form>
    </flux:modal>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($view === 'grid')
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($programs as $program)
                <div class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 hover:shadow-lg transition">
                    <div class="aspect-[16/9] bg-zinc-100 relative">
                        @if($program->cover_path)
                            <img src="{{ Storage::disk('public')->url($program->cover_path) }}" alt="" class="size-full object-cover group-hover:scale-105 transition duration-700" loading="lazy" />
                        @else
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop" alt="" class="size-full object-cover" />
                        @endif
                        <div class="absolute left-3 top-3 flex gap-1">
                            @if($program->is_published)<flux:badge color="emerald" size="sm">Publié</flux:badge>@else<flux:badge color="zinc" size="sm">Brouillon</flux:badge>@endif
                            <flux:badge color="sky" size="sm">{{ $program->city }}</flux:badge>
                        </div>
                        <div class="absolute bottom-3 left-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold backdrop-blur">{{ $program->plots_available_count }}/{{ $program->plots_count }} dispo</div>
                    </div>
                    <div class="p-4">
                        <div class="truncate text-sm font-bold">{{ $program->title }}</div>
                        <div class="truncate text-xs text-zinc-500">{{ $program->slug }}</div>
                        <div class="mt-2 flex items-center gap-2 text-xs text-zinc-600">
                            <flux:icon.map-pin class="size-3" /> {{ $program->city }} · {{ $program->total_area ? number_format((float)$program->total_area,0,',',' ').' m²' : 'surface —' }}
                        </div>
                        <div class="mt-3 flex flex-wrap gap-1">
                            <flux:button size="xs" variant="primary" :href="route('admin.plots.index', $program)" wire:navigate icon="squares-2x2">Lots</flux:button>
                            @can('programs.update')<flux:button size="xs" variant="ghost" wire:click="startEdit({{ $program->id }})">Éditer</flux:button><flux:button size="xs" variant="ghost" wire:click="togglePublish({{ $program->id }})">{{ $program->is_published ? 'Dépublier' : 'Publier' }}</flux:button>@endcan
                            @can('programs.delete')<flux:button size="xs" variant="ghost" icon="trash" wire:click="delete({{ $program->id }})" wire:confirm="Supprimer ?" class="text-red-600">Suppr</flux:button>@endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed bg-zinc-50 p-10 text-center">
                    <flux:icon.map class="mx-auto size-8 text-zinc-300" />
                    <div class="mt-2 text-sm font-medium">Aucun programme</div><div class="text-xs text-zinc-500">Crée ton premier lotissement Bingerville.</div>
                </div>
            @endforelse
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50">
                    <tr class="text-left">
                        <th class="px-4 py-3">Programme</th>
                        <th class="px-4 py-3">Ville</th>
                        <th class="px-4 py-3">Lots</th>
                        <th class="px-4 py-3">Surface</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                        <tr class="border-t border-zinc-100 hover:bg-zinc-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 overflow-hidden rounded-lg bg-zinc-100 shrink-0">
                                        @if($program->cover_path)<img src="{{ Storage::disk('public')->url($program->cover_path) }}" class="size-full object-cover" />@else<div class="flex size-full items-center justify-center text-zinc-400"><flux:icon.photo class="size-5" /></div>@endif
                                    </div>
                                    <div class="min-w-0"><div class="truncate font-medium">{{ $program->title }}</div><div class="truncate text-xs text-zinc-500">{{ $program->slug }}</div></div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><flux:badge size="sm" color="zinc">{{ $program->city }}</flux:badge></td>
                            <td class="px-4 py-3"><span class="font-medium">{{ $program->plots_count }}</span> <span class="text-xs text-emerald-600">· {{ $program->plots_available_count }} dispo</span></td>
                            <td class="px-4 py-3 text-xs">{{ $program->total_area ? number_format((float)$program->total_area,0,',',' ').' m²' : '—' }}</td>
                            <td class="px-4 py-3">@if($program->is_published)<flux:badge color="emerald" size="sm">Publié</flux:badge>@else<flux:badge color="zinc" size="sm">Brouillon</flux:badge>@endif</td>
                            <td class="px-4 py-3"><div class="flex gap-1"><flux:button size="xs" variant="ghost" :href="route('admin.plots.index', $program)" wire:navigate>Lots</flux:button>@can('programs.update')<flux:button size="xs" variant="ghost" wire:click="startEdit({{ $program->id }})">Éditer</flux:button>@endcan</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-zinc-500">Aucun programme.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-4">{{ $programs->links() }}</div>
</section>
