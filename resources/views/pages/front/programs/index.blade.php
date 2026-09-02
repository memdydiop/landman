<?php

use App\Enums\PlotStatus;
use App\Models\Program;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.front')] #[Title('Lotissements & Foncier — SIBEA-CI')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $city = '';

    #[Url]
    public string $availability = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCity(): void { $this->resetPage(); }
    public function updatingAvailability(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $programs = Program::published()
            ->withCount([
                'plots as plots_total',
                'plots as plots_available' => fn ($q) => $q->where('status', PlotStatus::DISPONIBLE),
                'plots as plots_reserved' => fn ($q) => $q->where('status', PlotStatus::RESERVE),
                'plots as plots_sold' => fn ($q) => $q->where('status', PlotStatus::VENDU),
            ])
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where(function ($sub) use ($s) {
                    $sub->where('title', 'like', '%'.$s.'%')
                        ->orWhere('city', 'like', '%'.$s.'%');
                });
            })
            ->when($this->city, fn ($q) => $q->where('city', $this->city))
            ->when($this->availability === 'available', fn ($q) => $q->whereHas('plots', fn ($qq) => $qq->where('status', PlotStatus::DISPONIBLE)))
            ->latest('published_at')
            ->paginate(9);

        $cities = Program::published()->select('city')->whereNotNull('city')->distinct()->pluck('city');

        $hero = Cache::remember('programs.hero', 300, fn () => SiteSetting::get('programs.hero', [
            'title' => 'FONCIER & AMÉNAGEMENT URBAIN',
            'body' => 'Catalogue foncier viabilisé en temps réel — Disponibilités, Plans de masse, ACD, Travaux de VRD et Ouverture de voies.',
            'badge' => 'AMÉNAGEMENT FONCIER & LOTISSEMENTS',
            'image' => null,
        ]));

        return view('pages.front.programs.index', [
            'programs' => $programs,
            'cities' => $cities,
            'hero' => $hero,
        ]);
    }
}; ?>

<section class="bg-zinc-950 text-white min-h-screen pb-20">
    {{-- Hero Page Foncier Sombre --}}
    <x-page-hero-simple
        :title="$hero['title'] ?: 'FONCIER & AMÉNAGEMENT URBAIN'"
        :subtitle="$hero['body'] ?: 'Catalogue foncier viabilisé en temps réel — Disponibilités, Plans de masse, ACD, Travaux de VRD et Ouverture de voies.'"
        :badge="$hero['badge'] ?: 'AMÉNAGEMENT FONCIER & LOTISSEMENTS'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Lotissements SIBEA-CI'"
        :breadcrumb="[['label'=>'Lotissements','url'=>route('front.programs.index')]]"
    />

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <!-- Poste de Commandement Foncier / Filtres -->
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/90 p-4 shadow-xl backdrop-blur-md">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="relative flex-1">
                    <input wire:model.live.debounce.300ms="search" 
                           type="text"
                           placeholder="Rechercher un programme, commune, site..." 
                           class="w-full rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-2.5 text-xs font-mono text-zinc-100 placeholder-zinc-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" />
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <select wire:model.live="city" class="rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-xs font-mono font-bold text-zinc-200 focus:border-amber-500 focus:outline-none">
                        <option value="">TOUTES LES VILLES / ZONES</option>
                        @foreach($cities as $c) 
                            <option value="{{ $c }}">{{ strtoupper($c) }}</option> 
                        @endforeach
                    </select>

                    <select wire:model.live="availability" class="rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2.5 text-xs font-mono font-bold text-zinc-200 focus:border-amber-500 focus:outline-none">
                        <option value="">TOUS LES PROGRAMMES</option>
                        <option value="available">AVEC LOTS DISPONIBLES</option>
                    </select>

                    @if($search || $city || $availability)
                        <button wire:click="$set('search',''); $set('city',''); $set('availability','')" 
                                class="rounded-xl bg-zinc-800 px-3 py-2.5 text-xs font-mono font-bold tracking-wider text-amber-400 hover:bg-zinc-700 transition">
                            EFFACER
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

        <!-- Grille des Programmes Fonciers -->
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($programs as $program)
                @php
                    $hasImage = !empty($program->cover_path) && Storage::disk('public')->exists($program->cover_path);
                    $coverUrl = $hasImage ? Storage::disk('public')->url($program->cover_path) : $fallbacks[$loop->index % count($fallbacks)];
                    $itemIndex = ($programs->currentPage() - 1) * $programs->perPage() + $loop->iteration;
                @endphp
                
                <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition-all duration-500 hover:border-amber-500 hover:shadow-2xl hover:shadow-amber-500/10">
                    <!-- Image Foncier avec Overlay BTP -->
                    <div class="relative h-56 w-full overflow-hidden bg-zinc-950">
                        <img src="{{ $coverUrl }}" alt="{{ $program->title }}" class="h-full w-full object-cover opacity-65 transition duration-700 group-hover:scale-105" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/40 to-transparent"></div>
                        
                        <!-- Badges Haute Précision -->
                        <div class="absolute top-4 left-4 right-4 flex items-center justify-between">
                            <span class="rounded bg-amber-500 px-2 py-0.5 font-mono text-[10px] font-black text-zinc-950 tracking-wider uppercase">
                                PROGRAMME #{{ sprintf('%02d', $itemIndex) }}
                            </span>
                            <span class="rounded bg-zinc-950/80 backdrop-blur-md border border-zinc-700 px-2.5 py-0.5 font-mono text-[10px] font-bold text-amber-400 uppercase tracking-wider">
                                📍 {{ strtoupper($program->city) }}
                            </span>
                        </div>
                    </div>

                    <!-- Métriques Techniques Foncier -->
                    <div class="flex flex-1 flex-col justify-between p-6">
                        <div>
                            <div class="font-mono text-[11px] font-bold text-amber-400 uppercase tracking-widest">
                                {{ $program->address ?: 'Site Viabilisé' }}
                            </div>
                            <h3 class="mt-2 text-lg font-black leading-snug text-white uppercase group-hover:text-amber-400 transition-colors line-clamp-2">
                                {{ $program->title }}
                            </h3>
                        </div>

                        <!-- Métraux & Jauge des Lots -->
                        <div class="mt-6 border-t border-zinc-800/80 pt-4">
                            <div class="grid grid-cols-3 gap-2 text-center font-mono text-[11px] mb-4">
                                <div class="rounded-lg bg-zinc-950 p-2 border border-zinc-800">
                                    <span class="block text-[9px] text-zinc-500 uppercase">DISPO</span>
                                    <span class="font-black text-emerald-400 text-sm">{{ $program->plots_available }}</span>
                                </div>
                                <div class="rounded-lg bg-zinc-950 p-2 border border-zinc-800">
                                    <span class="block text-[9px] text-zinc-500 uppercase">RÉSERVÉS</span>
                                    <span class="font-bold text-amber-400 text-sm">{{ $program->plots_reserved }}</span>
                                </div>
                                <div class="rounded-lg bg-zinc-950 p-2 border border-zinc-800">
                                    <span class="block text-[9px] text-zinc-500 uppercase">TOTAL</span>
                                    <span class="font-bold text-zinc-300 text-sm">{{ $program->plots_total }}</span>
                                </div>
                            </div>

                            @if($program->total_area)
                                <div class="font-mono text-[11px] text-zinc-400 mb-4 flex justify-between">
                                    <span class="text-zinc-500 uppercase">SUPERFICIE GLOBALE:</span>
                                    <span class="font-bold text-zinc-200">{{ number_format((float)$program->total_area, 0, ',', ' ') }} m²</span>
                                </div>
                            @endif

                            <a href="{{ route('front.programs.show', $program) }}" 
                               class="flex items-center justify-center gap-2 w-full rounded-xl border border-zinc-700 bg-zinc-800/60 py-2.5 font-mono text-xs font-bold text-white uppercase tracking-wider group-hover:border-amber-500 group-hover:bg-amber-500 group-hover:text-zinc-950 transition-all">
                                EXPLORER LE PLAN DE MASSE <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-zinc-800 bg-zinc-900/50 p-12 text-center">
                    <div class="font-mono text-xs font-bold text-zinc-500 uppercase">AUCUN PROGRAMME FONCIER NE CORRESPOND AUX CRITÈRES</div>
                    <button wire:click="$set('search',''); $set('city',''); $set('availability','')" 
                            class="mt-4 inline-flex items-center rounded-xl bg-amber-500 px-4 py-2 text-xs font-mono font-black text-zinc-950 hover:bg-amber-400 transition">
                        RÉINITIALISER LES FILTRES
                    </button>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $programs->links() }}
        </div>
    </div>
</section>
