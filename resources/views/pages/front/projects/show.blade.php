<?php

use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.front')] class extends Component {
    public Project $project;

    public function mount(Project $project): void
    {
        abort_unless($project->is_published, 404);
        $this->project = $project->load('media');
    }
}; ?>

<section class="bg-zinc-950 text-white min-h-screen pb-20">
    <!-- Fil d'Ariane Technique BTP -->
    <div class="border-b border-zinc-800/80 bg-zinc-900/60 py-3">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-xs font-mono lg:px-8">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-zinc-400 hover:text-amber-400 transition">ACCUEIL</a> 
                <span class="text-zinc-600">/</span>
                <a href="{{ route('front.projects.index') }}" class="text-zinc-400 hover:text-amber-400 transition">CHANTIERS</a> 
                <span class="text-zinc-600">/</span>
                <span class="font-bold text-amber-400 uppercase truncate max-w-[200px] sm:max-w-none">{{ $project->title }}</span>
            </div>
            <a href="{{ route('front.projects.index') }}" class="hidden sm:inline-flex text-xs font-bold text-zinc-400 hover:text-amber-400 transition">
                ← RETOUR CATALOGUE
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        
        <!-- En-tête Fiche d'Exécution Chantier -->
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded bg-amber-500 px-2 py-0.5 font-mono text-[10px] font-black text-zinc-950 uppercase">
                            {{ $project->service_type instanceof ServiceType ? $project->service_type->label() : $project->service_type }}
                        </span>
                        <span class="rounded font-mono text-[10px] font-bold px-2.5 py-0.5 uppercase
                            @if(($project->status->value ?? $project->status) === 'livre') bg-emerald-600 text-white 
                            @elseif(($project->status->value ?? $project->status) === 'en_cours') bg-amber-600 text-white 
                            @else bg-zinc-700 text-zinc-200 @endif">
                            ● {{ $project->status instanceof ProjectStatus ? $project->status->label() : $project->status }}
                        </span>
                        @if($project->is_featured) 
                            <span class="rounded bg-zinc-800 border border-amber-500/30 px-2 py-0.5 font-mono text-[10px] text-amber-400">RÉFÉRENCE MAJEURE</span> 
                        @endif
                    </div>
                    <h1 class="mt-3 text-2xl font-black uppercase text-white sm:text-3xl tracking-tight">{{ $project->title }}</h1>
                </div>

                <a href="{{ route('front.contact', ['project' => $project->id]) }}" 
                   class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-xs font-mono font-black tracking-wider text-zinc-950 hover:bg-amber-400 transition">
                    DEMANDER UNE ÉTUDE SIMILAIRE
                </a>
            </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            <!-- Suivi d'Exécution Visualisation (Photos) -->
            <div class="lg:col-span-3 space-y-4">
                <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
                    @if($project->cover_path && Storage::disk('public')->exists($project->cover_path))
                        <img src="{{ Storage::disk('public')->url($project->cover_path) }}" alt="{{ $project->title }}" class="w-full object-cover max-h-[480px]" loading="eager" />
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center font-mono text-xs text-zinc-600">
                            AUCUNE PHOTO DE COUVERTURE DISPONIBLE
                        </div>
                    @endif
                </div>

                <!-- Vues Terrain Multiples -->
                @if($project->media && $project->media->isNotEmpty())
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 shadow-sm">
                        <div class="font-mono text-xs font-bold text-amber-400 uppercase tracking-wider mb-3">GALERIE & SUIVI DU CHANTIER</div>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($project->media as $media)
                                <div class="overflow-hidden rounded-xl border border-zinc-800 bg-zinc-950 group">
                                    <img src="{{ Storage::disk($media->disk ?? 'public')->url($media->path) }}" alt="Vue terrain SIBEA" class="aspect-[4/3] w-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Fiche de Spécifications Techniques -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Métriques & Métraux -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-sm">
                    <h3 class="font-mono font-black text-white text-sm uppercase tracking-wider">Données d'exécution</h3>
                    <div class="mt-1 h-0.5 w-8 bg-amber-500"></div>

                    <div class="mt-4 grid grid-cols-2 gap-3 font-mono text-xs">
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-zinc-500 uppercase">Localisation</div>
                            <div class="mt-1 font-bold text-zinc-200">{{ $project->location ?? 'Non précisée' }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-zinc-500 uppercase">Surface Traitée</div>
                            <div class="mt-1 font-bold text-zinc-200">{{ $project->surface_m2 ? number_format($project->surface_m2, 0, ',', ' ').' m²' : '—' }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-zinc-500 uppercase">Durée Travaux</div>
                            <div class="mt-1 font-bold text-zinc-200">{{ $project->duration_months ? $project->duration_months.' mois' : '—' }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-zinc-500 uppercase">Année Livraison</div>
                            <div class="mt-1 font-bold text-zinc-200">{{ $project->year ?? '—' }}</div>
                        </div>
                    </div>

                    @if($project->description)
                        <div class="mt-6 border-t border-zinc-800/80 pt-4">
                            <h4 class="font-mono font-bold text-xs uppercase text-amber-400">Description des ouvrages</h4>
                            <p class="mt-2 whitespace-pre-line text-xs text-zinc-300 leading-relaxed">{{ $project->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Spécifications Techniques Formatées -->
                @if(is_array($project->technical_sheet) && count($project->technical_sheet) > 0)
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-sm">
                        <h3 class="font-mono font-black text-white text-sm uppercase tracking-wider">Cahier des charges</h3>
                        <div class="mt-1 h-0.5 w-8 bg-amber-500"></div>

                        <dl class="mt-4 space-y-2 font-mono text-xs">
                            @foreach($project->technical_sheet as $key => $value)
                                <div class="flex justify-between gap-4 border-b border-zinc-800 py-2 last:border-0">
                                    <dt class="text-zinc-500 uppercase">{{ Str::headline((string)$key) }}</dt>
                                    <dd class="font-bold text-zinc-200 text-right">{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <!-- CTA Directs -->
                <div class="flex flex-col gap-3">
                    <a href="{{ route('front.contact', ['project' => $project->id]) }}" 
                       class="w-full text-center rounded-xl bg-amber-500 py-3.5 font-mono text-xs font-black text-zinc-950 hover:bg-amber-400 transition uppercase">
                        DEMANDER UN DEVIS EN PÔLE TECHNIQUE
                    </a>
                    <a href="{{ route('front.programs.index') }}" 
                       class="w-full text-center rounded-xl border border-zinc-800 bg-zinc-900 py-3.5 font-mono text-xs font-bold text-zinc-300 hover:bg-zinc-800 transition uppercase">
                        CONSULTER LES PROGRAMMES ET LOTISSEMENTS
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
