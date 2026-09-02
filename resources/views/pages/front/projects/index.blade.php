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
                $q->where('title', 'like', '%'.$s.'%')
                  ->orWhere('location', 'like', '%'.$s.'%');
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
                'body' => 'Gros œuvre, VRD, aménagement foncier et installations techniques à Abidjan et Bingerville. Consultez nos opérations livrées et en cours.',
                'badge' => 'SUIVI DE CHANTIERS & RÉFÉRENCES',
                'image' => null,
            ]),
        );

        return view('pages.front.projects.index', [
            'projects' => $projects, 
            'hero' => $hero
        ]);
    }
}; ?>

<section class="bg-zinc-100/70 min-h-screen pb-12">
    {{-- Hero Page --}}
    <x-page-hero-simple
        :title="$hero['title'] ?: 'Chantiers & Réalisations Terrain'"
        :subtitle="$hero['body'] ?: 'Gros œuvre, VRD, aménagement foncier et installations techniques à Abidjan et Bingerville. Consultez nos opérations livrées et en cours.'"
        :badge="$hero['badge'] ?: 'SUIVI DE CHANTIERS & RÉFÉRENCES'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Chantiers SIBEA-CI'"
        :breadcrumb="[['label'=>'Réalisations','url'=>route('front.projects.index')]]"
    />

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <!-- Barre de Filtres Poste de Commandement -->
        <div class="rounded-2xl border border-zinc-300/80 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="relative flex-1">
                    <input wire:model.live.debounce.300ms="search" 
                           type="text"
                           placeholder="Rechercher une référence, ville, zone de chantier..." 
                           class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-xs font-medium focus:border-amber-500 focus:bg-white focus:ring-0" />
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <select wire:model.live="service" class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 text-xs font-bold text-zinc-800 focus:border-amber-500 focus:ring-0">
                        <option value="">TOUS LES PÔLES BTP</option>
                        @foreach(ServiceType::cases() as $s) 
                            <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option> 
                        @endforeach
                    </select>

                    <select wire:model.live="status" class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2.5 text-xs font-bold text-zinc-800 focus:border-amber-500 focus:ring-0">
                        <option value="">TOUS LES STATUTS</option>
                        @foreach(ProjectStatus::cases() as $s) 
                            <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option> 
                        @endforeach
                    </select>

                    @if($search || $service || $status)
                        <button wire:click="$set('search',''); $set('service',''); $set('status','')" 
                                class="rounded-xl bg-zinc-200 px-3 py-2.5 text-xs font-bold tracking-wider text-zinc-700 hover:bg-zinc-300 transition">
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
                'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop',
            ];
        @endphp

        <!-- Grille des Projets -->
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($projects as $project)
                @php
                    $coverUrl = $project->cover_path ? Storage::disk('public')->url($project->cover_path) : $fallbacks[$loop->index % count($fallbacks)];
                    $serviceLabel = $project->service_type instanceof ServiceType ? $project->service_type->label() : ($project->service_type ?? 'BTP');
                    $statusLabel = $project->status instanceof ProjectStatus ? $project->status->label() : ($project->status ?? 'Livré');
                    $statusValue = $project->status instanceof ProjectStatus ? $project->status->value : $project->status;
                    $itemIndex = ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration;
                @endphp
                <a href="{{ route('front.projects.show', $project) }}" 
                   class="group relative flex min-h-[380px] flex-col justify-between overflow-hidden rounded-2xl border border-zinc-300/80 bg-zinc-900 p-6 shadow-md transition-all duration-500 hover:border-amber-500 hover:shadow-2xl">
                    
                    <img src="{{ $coverUrl }}" alt="{{ $project->title }}" class="absolute inset-0 size-full object-cover opacity-60 transition duration-700 group-hover:scale-105" loading="lazy" />
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/60 to-transparent"></div>

                    <!-- Header Fiche Chantier -->
                    <div class="relative flex items-center justify-between">
                        <span class="rounded bg-amber-500/90 px-2 py-0.5 font-mono text-[11px] font-black text-zinc-950">
                            REF-{{ sprintf('%02d', $itemIndex) }}
                        </span>
                        <span class="rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md
                            @if($statusValue === 'livre') bg-emerald-600/80 
                            @elseif($statusValue === 'en_cours') bg-amber-600/90 
                            @else bg-zinc-700/80 @endif">
                            ● {{ $statusLabel }}
                        </span>
                    </div>

                    <!-- Footer Informations Terrain -->
                    <div class="relative">
                        <div class="font-mono text-[11px] font-bold text-amber-400 uppercase tracking-widest">
                            {{ $serviceLabel }} {{ $project->location ? '· 📍 '.$project->location : '' }}
                        </div>
                        <h3 class="mt-2 text-xl font-black leading-tight text-white uppercase group-hover:text-amber-400 transition-colors line-clamp-2">
                            {{ $project->title }}
                        </h3>

                        <!-- Données Techniques Clés -->
                        <div class="mt-3 flex items-center gap-4 text-[11px] font-mono text-zinc-300 border-t border-white/10 pt-3">
                            @if($project->surface_m2)
                                <div><span class="text-zinc-500">SURFACE:</span> {{ number_format($project->surface_m2, 0, ',', ' ') }} m²</div>
                            @endif
                            @if($project->year)
                                <div><span class="text-zinc-500">ANNÉE:</span> {{ $project->year }}</div>
                            @endif
                        </div>

                        <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white/10 backdrop-blur-sm px-3.5 py-1.5 text-xs font-black tracking-wider text-white group-hover:bg-amber-500 group-hover:text-zinc-950 transition-all">
                            FICHE DU CHANTIER <span>→</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 bg-white p-12 text-center">
                    <div class="font-mono text-xs font-bold text-zinc-400">AUCUN CHANTIER NE CORRESPOND AUX CRITÈRES SELECTIONNÉS</div>
                    <button wire:click="$set('search',''); $set('service',''); $set('status','')" 
                            class="mt-4 inline-flex items-center rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-zinc-950 hover:bg-amber-400">
                        RÉINITIALISER LES FILTRES
                    </button>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $projects->links() }}
        </div>
    </div>
</section>
