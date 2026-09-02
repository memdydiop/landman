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
        
        // Recherche insensible à la casse / sécurisée par rapport à la valeur de l'Enum
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

<section>
    <!-- Fil d'Ariane -->
    <div class="bg-zinc-100 py-4">
        <div class="mx-auto max-w-7xl px-4 text-sm lg:px-8">
            <a href="{{ route('home') }}" class="text-zinc-500 hover:text-primary">Accueil</a> 
            <span class="text-zinc-400">/</span>
            <a href="{{ route('front.services.index') }}" class="text-zinc-500 hover:text-primary">Services</a> 
            <span class="text-zinc-400">/</span>
            <span class="font-medium text-zinc-900">{{ $cmsService['title'] ?? $service->label() }}</span>
        </div>
    </div>

    <!-- Contenu Principal -->
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h1 class="text-3xl font-black text-zinc-900">{{ $cmsService['title'] ?? $service->label() }}</h1>
                <div class="mt-2 h-1 w-12 bg-secondary"></div>
                
                @if(!empty($cmsService['image']) && \App\Services\ImageService::exists($cmsService['image']))
                    <div class="mt-6 overflow-hidden rounded-2xl">
                        {!! \App\Services\ImageService::picture($cmsService['image'], $cmsService['title'] ?? $service->label(), ['class'=>'w-full object-cover max-h-[450px]']) !!}
                    </div>
                @else
                    <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1200&q=80&auto=format&fit=crop" 
                         alt="{{ $cmsService['title'] ?? $service->label() }}" 
                         class="mt-6 w-full max-h-[450px] rounded-2xl object-cover" 
                         loading="eager" />
                @endif

                <div class="prose prose-zinc mt-6 max-w-none text-sm leading-relaxed">
                    @if(!empty($cmsService['desc']))
                        <p class="text-zinc-700 text-base leading-relaxed">{{ $cmsService['desc'] }}</p>
                    @else
                        <p class="text-zinc-700 text-base leading-relaxed">
                            @if($service === ServiceType::BTP) Notre expertise BTP couvre le gros œuvre, la construction de villas et tertiaire, le génie civil — études, suivi, livraison clés en main à Abidjan. Agréments ministériels, garantie décennale, RSE chantier.
                            @elseif($service === ServiceType::AMENAGEMENT) Aménagement urbain VRD : voirie, réseaux divers, terrassement, assainissement — viabilisation complète, 50 ha aménagés, conformité CIE/SODECI.
                            @elseif($service === ServiceType::LOTISSEMENT) Lotissement & Foncier : terrains viabilisés Bingerville, Abatta, ACD sécurisé, plan de masse PDF, accompagnement administratif, viabilisation.
                            @elseif($service === ServiceType::RENOVATION) Rénovation & réhabilitation : mise aux normes, réhabilitation villas et immeubles, finitions haut de gamme.
                            @elseif($service === ServiceType::ARCHITECTURE) Conception architecturale : plans, permis de construire, BIM, suivi architectural, optimisation.
                            @else Systèmes électriques : éclairage public, réseaux basse tension, conformité, maintenance.
                            @endif
                        </p>
                    @endif

                    <ul class="mt-6 space-y-2 list-none pl-0">
                        <li class="flex items-center gap-2 text-zinc-800 font-medium">
                            <svg class="size-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Agréments & assurance décennale
                        </li>
                        <li class="flex items-center gap-2 text-zinc-800 font-medium">
                            <svg class="size-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Devis sous 24h & accompagnement dédié
                        </li>
                        <li class="flex items-center gap-2 text-zinc-800 font-medium">
                            <svg class="size-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Suivi de chantier & livraison garantis
                        </li>
                    </ul>
                </div>

                <div class="mt-10">
                    <a href="{{ route('front.contact', ['type' => 'devis_btp', 'service' => $service->value]) }}" 
                       class="inline-flex items-center justify-center rounded-full bg-primary px-8 py-3.5 text-sm font-bold text-white transition hover:bg-[#002244] shadow-lg hover:shadow-xl">
                        DEMANDER UN DEVIS — {{ method_exists($service, 'shortLabel') ? $service->shortLabel() : $service->label() }}
                    </a>
                </div>
            </div>

            <!-- Sidebar Réalisations Associees -->
            <div class="lg:border-l lg:border-zinc-200 lg:pl-8">
                <h3 class="font-black text-lg text-zinc-900">Réalisations — {{ $service->label() }}</h3>
                <div class="mt-4 space-y-4">
                    @forelse($projects as $project)
                        <a href="{{ route('front.projects.show', $project) }}" class="flex gap-3 rounded-xl border border-zinc-200 p-3 transition hover:shadow-md hover:border-zinc-300 bg-white">
                            <div class="size-20 shrink-0 overflow-hidden rounded-lg bg-zinc-100">
                                @if($project->cover_path && Storage::disk('public')->exists($project->cover_path))
                                    <img src="{{ Storage::disk('public')->url($project->cover_path) }}" alt="{{ $project->title }}" class="size-full object-cover" loading="lazy" />
                                @else
                                    <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=200&q=80&auto=format&fit=crop" alt="{{ $project->title }}" class="size-full object-cover" loading="lazy" />
                                @endif
                            </div>
                            <div class="flex flex-col justify-center">
                                <div class="text-sm font-bold text-zinc-900 line-clamp-1">{{ $project->title }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ is_object($project->status) ? $project->status->label() : $project->status }} · {{ $project->location }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500">
                            Aucune réalisation associée enregistrée pour ce service.
                        </div>
                    @endforelse

                    <a href="{{ route('front.projects.index', ['service' => $service->value]) }}" 
                       class="block text-center text-xs font-bold tracking-widest text-primary hover:underline mt-6">
                        VOIR TOUTES LES RÉALISATIONS →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
