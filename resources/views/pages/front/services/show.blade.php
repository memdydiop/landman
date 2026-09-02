<?php

use App\Enums\ServiceType;
use App\Models\Project;
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
        $list = Cache::remember('services.list', 300, fn () => \App\Models\SiteSetting::get('services.list', []));
        $this->cmsService = collect($list)->firstWhere('key', $service->value);
    }

    public function render(): \Illuminate\View\View
    {
        $projects = Project::published()->where('service_type', $this->service)->latest()->limit(6)->get();

        return view('pages.front.services.show', [
            'service' => $this->service,
            'cmsService' => $this->cmsService,
            'projects' => $projects,
        ]);
    }
}; ?>

<section>
    <div class="bg-zinc-100 py-4">
        <div class="mx-auto max-w-7xl px-4 text-sm lg:px-8">
            <a href="{{ route('home') }}" class="text-zinc-500 hover:text-[#003366]">Accueil</a> <span class="text-zinc-400">/</span>
            <a href="{{ route('front.services.index') }}" class="text-zinc-500 hover:text-[#003366]">Services</a> <span class="text-zinc-400">/</span>
            <span class="font-medium">{{ $service->label() }}</span>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h1 class="text-3xl font-black">{{ $cmsService['title'] ?? $service->label() }}</h1>
                <div class="mt-2 h-1 w-12 bg-secondary"></div>
                @if(!empty($cmsService['image']) && \App\Services\ImageService::exists($cmsService['image']))
                    <div class="mt-6 overflow-hidden rounded-2xl">
                        {!! \App\Services\ImageService::picture($cmsService['image'], $cmsService['title'] ?? $service->label(), ['class'=>'w-full object-cover']) !!}
                    </div>
                @else
                    <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1200&q=80&auto=format&fit=crop" alt="{{ $cmsService['title'] ?? $service->label() }}" class="mt-6 w-full rounded-2xl object-cover" loading="lazy" />
                @endif
                <div class="prose prose-zinc mt-6 max-w-none text-sm leading-relaxed">
                    @if(!empty($cmsService['desc']))
                        <p>{{ $cmsService['desc'] }}</p>
                        <p class="text-xs text-zinc-500 mt-2">Fiche éditée depuis <a href="{{ route('admin.cms.services') }}" class="underline">CMS → Services</a> (clé : <code>{{ $cmsService['key'] }}</code>).</p>
                    @else
                        <p>
                            @if($service === ServiceType::BTP) Notre expertise BTP couvre le gros œuvre, la construction de villas et tertiaire, le génie civil — études, suivi, livraison clés en main à Abidjan. Agréments ministériels, garantie décennale, RSE chantier.
                            @elseif($service === ServiceType::AMENAGEMENT) Aménagement urbain VRD : voirie, réseaux divers, terrassement, assainissement — viabilisation complète, 50 ha aménagés, conformité CIE/SODECI.
                            @elseif($service === ServiceType::LOTISSEMENT) Lotissement & Foncier : terrains viabilisés Bingerville, Abatta, ACD sécurisé, plan de masse PDF, accompagnement administratif, viabilisation.
                            @elseif($service === ServiceType::RENOVATION) Rénovation & réhabilitation : mise aux normes, réhabilitation villas et immeubles, finitions haut de gamme.
                            @elseif($service === ServiceType::ARCHITECTURE) Conception architecturale : plans, permis de construire, BIM, suivi architectural, optimisation.
                            @else Systèmes électriques : éclairage public, réseaux basse tension, conformité, maintenance.
                            @endif
                        </p>
                    @endif
                    <ul class="mt-4 list-disc pl-5">
                        <li>Agréments & assurance décennale</li>
                        <li class="inline-flex items-center gap-1.5">Devis sous 24h — réponse <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg></li>
                        <li>Suivi chantier & livraison garantis</li>
                    </ul>
                </div>
                <div class="mt-8">
                    <a href="{{ route('front.contact', ['type' => 'devis_btp', 'service' => $service->value]) }}" class="rounded-full bg-[#003366] px-8 py-3 text-sm font-bold text-white hover:bg-[#002244]">DEMANDER UN DEVIS — {{ $service->shortLabel() }}</a>
                </div>
            </div>

            <div>
                <h3 class="font-bold">Réalisations — {{ $service->label() }}</h3>
                <div class="mt-4 space-y-4">
                    @forelse($projects as $project)
                        <a href="{{ route('front.projects.show', $project) }}" class="flex gap-3 rounded-xl border border-zinc-200 p-3 hover:shadow">
                            <div class="size-20 shrink-0 overflow-hidden rounded-lg bg-zinc-100">
                                @if($project->cover_path)
                                    <img src="{{ Storage::disk('public')->url($project->cover_path) }}" alt="" class="size-full object-cover" loading="lazy" />
                                @else
                                    <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=200&q=80&auto=format&fit=crop" alt="" class="size-full object-cover" loading="lazy" />
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-medium line-clamp-1">{{ $project->title }}</div>
                                <div class="text-xs text-zinc-500">{{ $project->status->label() }} · {{ $project->location }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed p-6 text-center text-sm text-zinc-500">Aucune réalisation pour ce service — bientôt disponible.</div>
                    @endforelse
                    <a href="{{ route('front.projects.index', ['service' => $service->value]) }}" class="block text-center text-xs font-bold tracking-widest text-[#003366] hover:underline">VOIR TOUTES →</a>
                </div>
            </div>
        </div>
    </div>
</section>
