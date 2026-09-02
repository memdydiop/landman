<?php

use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.front')] #[Title('Chantiers & Réalisations — SIBEA-CI')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $service = '';

    #[Url]
    public string $status = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingService(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $projects = Project::published()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where(function ($sub) use ($s) {
                    $sub->where('title', 'like', '%'.$s.'%')
                        ->orWhere('location', 'like', '%'.$s.'%');
                });
            })
            ->when($this->service, fn ($q) => $q->where('service_type', $this->service))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('published_at')
            ->paginate(12);

        $hero = Cache::remember(
            'projects.hero', 
            300, 
            fn () => SiteSetting::get('projects.hero', [
                'title' => 'Chantiers & Réalisations Terrain',
                'body' => 'Gros œuvre, VRD, aménagement foncier et installations techniques. Consultez nos références d\'exécution.',
                'badge' => 'RÉFÉRENCES & RÉSULTATS',
                'image' => null,
            ]),
        );

        return view('pages.front.projects.index', [
            'projects' => $projects, 
            'hero' => $hero
        ]);
    }
}; ?>

<section class="bg-zinc-950 text-white min-h-screen pb-20">
    {{-- Hero Page BTP Sombre --}}
    <x-page-hero-simple
        :title="$hero['title'] ?: 'Chantiers & Réalisations Terrain'"
        :subtitle="$hero['body'] ?: 'Gros œuvre, VRD, aménagement foncier et installations techniques. Consultez nos références d\'exécution.'"
        :badge="$hero['badge'] ?: 'RÉFÉRENCES & RÉSULTATS'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Chantiers SIBEA-CI'"
        :breadcrumb="[['label'=>'Réalisations','url'=>route('front.projects.index')]]"
    />

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <!-- Barre de Filtres Style Poste de Commandement BTP -->
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/90 p-4 shadow-xl backdrop-blur-md">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="relative flex-1">
                    <input wire:model.live.debounce.300ms="search" 
                           type="text"
                           placeholder="Rechercher une référence, ville, zone de chantier..." 
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-xs font-mono text-zinc-100 placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" />
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <select wire:model.live="service" class="rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-xs font-mono font-bold text-zinc-200 focus:border-amber-500 focus:outline-none">
                        <option value="">TOUS LES PÔLES BTP</option>
                        @foreach(ServiceType::cases() as $s) 
                            <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option> 
                        @endforeach
                    </select>

                    <select wire:model.live="status" class="rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-xs font-mono font-bold text-zinc-200 focus:border-amber-500 focus:outline-none">
                        <option value="">TOUS LES STATUTS</option>
                        @foreach(ProjectStatus::cases() as $s) 
                            <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option> 
                        @endforeach
                    </select>

                    @if($search || $service || $status)
                        <button wire:click="$set('search',''); $set('service',''); $set('status','')" 
                                class="rounded-xl bg-zinc-800 px-3 py-2.5 text-xs font-mono font-bold tracking-wider text-amber-400 hover:bg-zinc-700 transition">
                            EFFACER FILTRES
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @php
            $fallbacks = [
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80&auto=format&fit=crop',
            ];
        @endphp

        <!-- Grille des Cartes Chantiers -->
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($projects as $project)
                @php
                    $coverUrl = $project->cover_path ? Storage::disk('public')->url($project->cover_path) : $fallbacks[$loop->index % count($fallbacks)];
                    $serviceLabel = $project->service_type instanceof ServiceType ? $project->service_type->label() : ($project->service_type ?? 'BTP');
                    $statusLabel = $project->status instanceof ProjectStatus ? $project->status->label() : ($project->status ?? 'Livré');
                    $statusValue = $project->status instanceof ProjectStatus ? $project->status->value : $project->status;
                    $itemIndex = ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration;
                @endphp
                
                <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition-all duration-500 hover:border-amber-500 hover:shadow-2xl hover:shadow-amber-500/10">
                    <!-- Image de Fond & Layer Sombre -->
                    <div class="relative h-56 w-full overflow-hidden bg-zinc-950">
                        <img src="{{ $coverUrl }}" alt="{{ $project->title }}" class="h-full w-full object-cover opacity-70 transition duration-700 group-hover:scale-105" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/40 to-transparent"></div>
                        
                        <!-- Badges Haute Précision -->
                        <div class="absolute top-4 left-4 right-4 flex items-center justify-between">
                            <span class="rounded bg-amber-500 px-2 py-0.5 font-mono text-[10px] font-black text-zinc-950 tracking-wider uppercase">
                                REF-{{ sprintf('%02d', $itemIndex) }}
                            </span>
                            <span class="rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md
                                @if($statusValue === 'livre') bg-emerald-600/90 
                                @elseif($statusValue === 'en_cours') bg-amber-600/90 
                                @else bg-zinc-700/90 @endif">
                                ● {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <!-- Contenu Technique -->
                    <div class="flex flex-1 flex-col justify-between p-6">
                        <div>
                            <div class="font-mono text-[11px] font-bold text-amber-400 uppercase tracking-widest">
                                {{ $serviceLabel }} {{ $project->location ? '· 📍 '.$project->location : '' }}
                            </div>
                            <h3 class="mt-2 text-lg font-black leading-snug text-white uppercase group-hover:text-amber-400 transition-colors line-clamp-2">
                                {{ $project->title }}
                            </h3>
                        </div>

                        <!-- Spécifications au mètre carré / durée -->
                        <div class="mt-6 border-t border-zinc-800/80 pt-4">
                            <div class="grid grid-cols-2 gap-2 font-mono text-[11px] text-zinc-400">
                                <div><span class="text-zinc-600 uppercase">SURFACE:</span> {{ $project->surface_m2 ? number_format($project->surface_m2, 0, ',', ' ').' m²' : '—' }}</div>
                                <div><span class="text-zinc-600 uppercase">LIVRAISON:</span> {{ $project->year ?? '—' }}</div>
                            </div>

                            <a href="{{ route('front.projects.show', $project) }}" 
                               class="mt-5 flex items-center justify-center gap-2 w-full rounded-xl border border-zinc-700 bg-zinc-800/60 py-2.5 font-mono text-xs font-bold text-white uppercase tracking-wider group-hover:border-amber-500 group-hover:bg-amber-500 group-hover:text-zinc-950 transition-all">
                                VOIR LA FICHE TECHNIQUE <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-zinc-800 bg-zinc-900/50 p-12 text-center">
                    <div class="font-mono text-xs font-bold text-zinc-500 uppercase">AUCUN CHANTIER NE CORRESPOND AUX CRITÈRES SÉLECTIONNÉS</div>
                    <button wire:click="$set('search',''); $set('service',''); $set('status','')" 
                            class="mt-4 inline-flex items-center rounded-xl bg-amber-500 px-4 py-2 text-xs font-mono font-black text-zinc-950 hover:bg-amber-400 transition">
                        RÉINITIALISER LES FILTRES
                    </button>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $projects->links() }}
        </div>
    </div>
</section>
