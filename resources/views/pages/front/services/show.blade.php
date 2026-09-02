<?php

use App\Enums\ServiceType;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.front')] class extends Component {
    public ServiceType $service;
    public ?array $cmsService = null;

    public function mount(ServiceType $service): void
    {
        $this->service = $service;
        $list = Cache::remember('services.list', 300, fn () => SiteSetting::get('services.list', []));
        $this->cmsService = collect($list)->firstWhere('key', $service->value) 
            ?? collect($list)->firstWhere('key', strtolower($service->value));
    }

    public function render(): \Illuminate\View\View
    {
        $projects = Cache::remember('service.projects.' . $this->service->value, 300, function () {
            return Project::published()
                ->where('service_type', $this->service)
                ->latest()
                ->limit(6)
                ->get();
        });

        return view('pages.front.services.show', [
            'service' => $this->service,
            'cmsService' => $this->cmsService,
            'projects' => $projects,
        ]);
    }
}; ?>

<section class="bg-zinc-50/50 min-h-screen pb-16">
    <!-- Fil d'Ariane Technique -->
    <div class="border-b border-zinc-200 bg-white py-3">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-xs font-mono lg:px-8">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-zinc-500 hover:text-primary">ACCUEIL</a> 
                <span class="text-zinc-300">/</span>
                <a href="{{ route('front.services.index') }}" class="text-zinc-500 hover:text-primary">SERVICES</a> 
                <span class="text-zinc-300">/</span>
                <span class="font-bold text-zinc-900 uppercase">{{ $cmsService['title'] ?? $service->label() }}</span>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-amber-600 font-bold">
                <span class="inline-block size-2 rounded-full bg-amber-500 animate-pulse"></span>
                POLE D'EXÉCUTION TERRAIN
            </div>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-3">
            
            <!-- Colonne Gauche : Fiche Technique & Description -->
            <div class="lg:col-span-2">
                <!-- Header de Pôle -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-zinc-900 p-6 rounded-2xl text-white shadow-xl">
                    <div>
                        <span class="inline-block rounded bg-amber-500 px-2 py-0.5 text-[10px] font-black tracking-widest text-zinc-950 uppercase">
                            FICHE CHANTIER SIBEA-CI
                        </span>
                        <h1 class="mt-2 text-2xl sm:text-3xl font-black tracking-tight text-white uppercase">
                            {{ $cmsService['title'] ?? $service->label() }}
                        </h1>
                    </div>
                    <a href="{{ route('front.contact', ['type' => 'devis_btp', 'service' => $service->value]) }}" 
                       class="inline-flex shrink-0 items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-xs font-black tracking-wider text-zinc-950 hover:bg-amber-400 transition">
                        COTATION CHANTIER 24H
                    </a>
                </div>

                <!-- Visuel d'illustration / Photo du terrain -->
                <div class="relative mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-900 shadow-md">
                    @if(!empty($cmsService['image']) && \App\Services\ImageService::exists($cmsService['image']))
                        {!! \App\Services\ImageService::picture($cmsService['image'], $cmsService['title'] ?? $service->label(), ['class'=>'w-full object-cover max-h-[420px]']) !!}
                    @else
                        <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1200&q=80&auto=format&fit=crop" 
                             alt="{{ $cmsService['title'] ?? $service->label() }}" 
                             class="w-full max-h-[420px] object-cover opacity-90" 
                             loading="eager" />
                    @endif
                    <div class="absolute bottom-4 left-4 rounded-lg bg-zinc-900/90 backdrop-blur-md px-3 py-1.5 text-[11px] font-mono text-zinc-300 border border-white/10">
                        📍 CHANTIERS ACTIFS : ABIDJAN / BINGERVILLE / ABATTA
                    </div>
                </div>

                <!-- Texte & Engagements Terrain -->
                <div class="mt-8 rounded-2xl bg-white p-6 sm:p-8 border border-zinc-200 shadow-sm">
                    <h2 class="text-lg font-black text-zinc-900 uppercase tracking-tight">Capacité d'exécution & Champ d'intervention</h2>
                    <div class="mt-1 h-1 w-10 bg-amber-500"></div>

                    <div class="mt-4 text-zinc-700 text-sm leading-relaxed space-y-4">
                        @if(!empty($cmsService['desc']))
                            <p class="text-base font-medium text-zinc-800 leading-relaxed">{{ $cmsService['desc'] }}</p>
                        @else
                            <p class="text-base font-medium text-zinc-800 leading-relaxed">
                                @if($service === ServiceType::BTP) SIBEA-CI assure la prise en charge complète de vos travaux BTP : terrrassement, coulage de dalles, structure béton armé, élévation et réhabilitation lourde. Nos conducteurs de travaux encadrent le chantier quotidiennement avec des équipes qualifiées.
                                @elseif($service === ServiceType::AMENAGEMENT) Viabilisation complète de parcelles et VRD : ouverture de voies, terrassement lourd, pose de caniveaux hydrauliques, raccordement réseaux d'eau (SODECI) et électriques (CIE).
                                @elseif($service === ServiceType::LOTISSEMENT) Sécurisation foncière et aménagement de lotissements : bornage contradictoire, viabilisation des accès, constitution des dossiers ACD avec un suivi administratif rigoureux.
                                @elseif($service === ServiceType::RENOVATION) Réhabilitation technique de bâtiments : reprises sous-œuvre, rénovation de façades, étanchéité, remise aux normes électriques et de plomberie.
                                @elseif($service === ServiceType::ARCHITECTURE) Conception de plans d'exécution béton armé, permis de construire, modélisation 3D et suivi de conformité architecturale sur le chantier.
                                @else Installation de postes transformateurs, éclairage public solaire et réseaux Basse/Moyenne Tension conformes aux normes ivoiriennes.
                                @endif
                            </p>
                        @endif
                    </div>

                    <!-- Grille d'Agréments / Garanties Terrain -->
                    <div class="mt-8 grid gap-4 sm:grid-cols-3 border-t border-zinc-100 pt-6">
                        <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200/60">
                            <div class="text-amber-600 font-bold text-xs uppercase">Garantie & Sécurité</div>
                            <div class="mt-1 text-sm font-black text-zinc-900">Décennale & RSE</div>
                            <div class="mt-1 text-[11px] text-zinc-500">Chantiers aux normes ivoiriennes.</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200/60">
                            <div class="text-amber-600 font-bold text-xs uppercase">Chrono Exécution</div>
                            <div class="mt-1 text-sm font-black text-zinc-900">Planning Tenu</div>
                            <div class="mt-1 text-[11px] text-zinc-500">Rapports d'avancement hebdomadaires.</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200/60">
                            <div class="text-amber-600 font-bold text-xs uppercase">Réactivité</div>
                            <div class="mt-1 text-sm font-black text-zinc-900">Visite Sous 48h</div>
                            <div class="mt-1 text-[11px] text-zinc-500">Déplacement technicien sur site.</div>
                        </div>
                    </div>

                    <!-- Call To Action Direct -->
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-xl bg-zinc-900 p-5 text-white">
                        <div>
                            <div class="text-sm font-bold">Un projet ou un cahier des charges à soumettre ?</div>
                            <div class="text-xs text-zinc-400">Transmettez-nous vos métrés ou demandez une visite de site.</div>
                        </div>
                        <a href="{{ route('front.contact', ['type' => 'devis_btp', 'service' => $service->value]) }}" 
                           class="w-full sm:w-auto text-center rounded-lg bg-amber-500 px-6 py-2.5 text-xs font-black text-zinc-950 hover:bg-amber-400 transition uppercase">
                            LANCER L'ÉTUDE DE DEVIS
                        </a>
                    </div>
                </div>
            </div>

            <!-- Colonne Droite : Sidebar Chantiers en Cours / Réalisations -->
            <div class="space-y-6">
                <!-- Bloc Chantiers de la Categorie -->
                <div class="rounded-2xl bg-white p-6 border border-zinc-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="font-black text-zinc-900 text-sm uppercase tracking-wider">Chantiers & Projets</h3>
                        <span class="rounded bg-zinc-100 px-2 py-0.5 font-mono text-[10px] text-zinc-600">{{ $projects->count() }} réf.</span>
                    </div>
                    <div class="mt-1 h-0.5 w-8 bg-amber-500"></div>

                    <div class="mt-6 space-y-4">
                        @forelse($projects as $project)
                            <a href="{{ route('front.projects.show', $project) }}" class="group flex gap-3 rounded-xl border border-zinc-200 p-2.5 transition hover:border-amber-500 hover:shadow-md bg-white">
                                <div class="size-16 shrink-0 overflow-hidden rounded-lg bg-zinc-900 relative">
                                    @if($project->cover_path && Storage::disk('public')->exists($project->cover_path))
                                        <img src="{{ Storage::disk('public')->url($project->cover_path) }}" alt="{{ $project->title }}" class="size-full object-cover transition group-hover:scale-105" loading="lazy" />
                                    @else
                                        <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=200&q=80&auto=format&fit=crop" alt="{{ $project->title }}" class="size-full object-cover opacity-80" loading="lazy" />
                                    @endif
                                </div>
                                <div class="flex flex-col justify-center min-w-0">
                                    <div class="text-xs font-bold text-zinc-900 truncate group-hover:text-primary">{{ $project->title }}</div>
                                    <div class="mt-1 flex items-center gap-2 text-[10px] text-zinc-500">
                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 font-semibold text-amber-800">
                                            {{ is_object($project->status) ? $project->status->label() : $project->status }}
                                        </span>
                                        <span class="truncate">📍 {{ $project->location }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-xs text-zinc-500">
                                Aucun chantier répertorié pour ce pôle pour le moment.
                            </div>
                        @endforelse

                        <a href="{{ route('front.projects.index', ['service' => $service->value]) }}" 
                           class="block text-center text-xs font-black tracking-widest text-primary hover:text-amber-600 transition pt-2">
                            EXPLORER TOUTES LES RÉALISATIONS →
                        </a>
                    </div>
                </div>

                <!-- Bloc d'Appel Direct WhatsApp Terrain -->
                <div class="rounded-2xl bg-zinc-900 p-6 text-white border border-zinc-800 shadow-lg">
                    <div class="text-xs font-mono text-amber-500">CONTACT DIRECT CONDUCTEUR DE TRAVAUX</div>
                    <div class="mt-2 text-base font-bold">Besoin d'un renseignement immédiat ?</div>
                    <p class="mt-1 text-xs text-zinc-400">Pour les visites de terrain ou vérifications de parcelles à Bingerville/Abatta.</p>
                    <a href="{{ route('front.contact') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-xs font-bold text-white hover:bg-emerald-500 transition">
                        💬 CONTACTER LES ÉQUIPES TERRAIN
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
