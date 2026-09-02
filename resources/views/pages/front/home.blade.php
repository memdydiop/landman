<?php

use App\Enums\PlotStatus;
use App\Enums\ProjectStatus;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Accueil — BTP, Aménagement, Lotissement')] class extends Component {
    public string $portfolioFilter = 'all';

    public function render(): \Illuminate\View\View
    {
        $featuredProjects = Cache::remember('home.featuredProjects', 300, fn () => Project::published()->featured()->latest('published_at')->limit(3)->get());
        $recentWorks = Cache::remember('home.recentWorks', 300, fn () => Project::published()->latest('published_at')->limit(8)->get());
        $programs = Cache::remember('home.programs', 300, fn () => Program::published()->withCount(['plots as available_plots_count' => fn ($q) => $q->where('status', PlotStatus::DISPONIBLE)])->latest()->limit(3)->get());
        $availablePlots = Cache::remember('home.availablePlots', 300, fn () => Plot::available()->with('program')->latest()->limit(6)->get());
        // Témoignages & Partenaires — source Eloquent (Sidebar CRUD), gérés via admin/testimonials & admin/partners
        $testimonialsCached = Cache::remember('home.testimonials', 300, fn () => \App\Models\Testimonial::published()->orderBy('position')->limit(3)->get());
        $partnersCached = Cache::remember('home.partners', 300, fn () => \App\Models\Partner::published()->orderBy('position')->get());

        $hero = SiteSetting::get('home.hero', []);
        $statsSetting = SiteSetting::get('home.stats', []);
        $stats = Cache::remember('home.stats', 300, function () use ($statsSetting) {
            return [
                'projects_completed' => $statsSetting['projects_completed'] ?? (Project::where('status', ProjectStatus::LIVRE)->count() ?: 1240),
                'happy_clients' => $statsSetting['happy_clients'] ?? (\App\Models\Inquiry::where('status', \App\Enums\InquiryStatus::TRAITE)->count() ?: 1750),
                'surface_total' => (int) ($statsSetting['surface_total'] ?? (Program::published()->sum('total_area') ?: 984000)),
                'awards' => $statsSetting['awards'] ?? 96,
                'plots_available' => Plot::available()->count(),
                'workers' => $statsSetting['workers'] ?? 984,
            ];
        });
        $whyChoose = SiteSetting::get('home.why_choose', null);
        $cmsServices = SiteSetting::get('services.list', []);
        // 4 pôles transversaux: si vide, fallback sur ServiceType
        if (empty($cmsServices)) {
            $cmsServices = array_map(fn($c) => ['key' => $c->value, 'title' => $c->label(), 'desc' => '', 'image' => null], \App\Enums\ServiceType::cases());
        }
        $testimonials = $testimonialsCached;
        $partners = $partnersCached;
        if ($testimonials->isEmpty()) {
            $testimonials = collect([(object) ['name' => 'Kouassi Jean — Cocody', 'role' => 'Propriétaire Villa 4 pièces', 'content' => 'SIBEA-CI a livré notre villa dans les délais, avec un suivi VRD impeccable et un ACD sécurisé. Une équipe réactive et professionnelle.', 'rating' => 5], (object) ['name' => 'Awa Koné — Bingerville', 'role' => 'Gérante PME', 'content' => 'Lotissement Abatta : viabilisation et ACD obtenus sans accroc. SIBEA-CI maîtrise le foncier.', 'rating' => 5], (object) ['name' => 'Yao Kouamé — Bouaké', 'role' => 'Conducteur de Travaux', 'content' => 'Collaboration BTP fluide, respect du budget et délais. Je recommande SIBEA-CI.', 'rating' => 5]]);
        }
        if ($partners->isEmpty()) {
            $partners = collect(['NSIA Banque', 'SIB', 'BOA CI', 'SGCI', 'BACI'])->map(fn($n) => (object) ['name' => $n, 'logo_path' => null]);
        }

        $global = SiteSetting::get('global', []);
        $homeOffers = SiteSetting::get('home.offers', ['RÉNOVATION','CONSEIL','CONSTRUCTION','ARCHITECTURE','ÉLECTRICITÉ']);
        $homeDetails = SiteSetting::get('home.details', [
            ['title' => 'Plomberie & VRD', 'desc' => 'VRD, assainissement, réseaux EU/EP — conforme normes ivoiriennes.'],
            ['title' => 'Peinture Murale', 'desc' => 'Finitions haut de gamme, peinture, revêtements, étanchéité.'],
            ['title' => 'Toiture Métallique', 'desc' => 'Charpente métallique, toiture bac acier, anti-corrosion côtière.'],
            ['title' => 'Préparation des Sols', 'desc' => 'Terrassement, plateforme, préparation sols avant construction.'],
        ]);
        $banner = SiteSetting::get('home.banner', [
            'title' => 'Entrepreneurs & Conducteurs de travaux depuis 1981',
            'cta_label' => 'DEMANDER UN DEVIS',
            'cta_url' => '/contact',
            'image' => null,
        ]);
        $team = SiteSetting::get('home.team', [
            ['name' => 'Ouattara Bassoma Ziegnougo', 'role' => 'Gérant — SARL', 'avatar' => null],
            ['name' => 'Kouamé Yao', 'role' => 'Ingénieur Civil VRD', 'avatar' => null],
            ['name' => 'Awa Koné', 'role' => 'Conductrice Travaux', 'avatar' => null],
            ['name' => 'Diabaté Moussa', 'role' => 'Électricien Chef', 'avatar' => null],
        ]);

        return view('pages.front.home', [
            'featuredProjects' => $featuredProjects,
            'recentWorks' => $recentWorks,
            'programs' => $programs,
            'availablePlots' => $availablePlots,
            'stats' => $stats,
            'hero' => $hero,
            'whyChoose' => $whyChoose,
            'cmsServices' => $cmsServices,
            'testimonials' => $testimonials,
            'partners' => $partners,
            'global' => $global,
            'homeOffers' => $homeOffers,
            'homeDetails' => $homeDetails,
            'banner' => $banner,
            'team' => $team,
        ]);
    }
}; ?>

<section class="bg-white">
    <!-- Hero épuré AfricaSpace — vidéo drone + slides CMS — responsive -->
    <div class="relative flex min-h-[480px] h-[64svh] sm:min-h-[520px] sm:h-[68svh] lg:min-h-[640px] lg:h-[78vh] xl:min-h-[700px] max-h-[900px] items-center overflow-hidden bg-zinc-900">
        @if(!empty($hero['slide1_image']) && Storage::disk('public')->exists($hero['slide1_image']))
            <img src="{{ Storage::disk('public')->url($hero['slide1_image']) }}" alt="SIBEA-CI hero" class="absolute inset-0 size-full object-cover opacity-40" loading="eager" />
        @else
            <video autoplay muted loop playsinline
                poster="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80&auto=format&fit=crop"
                class="absolute inset-0 size-full object-cover opacity-50">
                <source src="https://videos.pexels.com/video-files/18069234/18069234-uhd_1440_1440_24fps.mp4"
                    type="video/mp4">
            </video>
        @endif
        <div class="absolute inset-0 bg-zinc-900/60"></div>
        <div class="relative mx-auto flex w-full max-w-7xl items-center px-4 lg:px-8 py-10 sm:py-12">
            <div class="max-w-3xl border-l-4 border-white/30 pl-6">
                <div class="text-xs tracking-[0.3em] text-[#4d7aa3]">
                    {{ $hero['badge'] ?? 'SARL • 2022 • IDU CI-2022-0016466 Q' }}</div>
                <flux:heading level="1"
                    class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white! leading-[1.1] mb-6">
                    {{ $hero['title_line1'] ?? 'SIBEA-CI' }}<br>
                    <span class="font-black">{{ $hero['title_line2'] ?? 'Laboratoire urbain' }}</span>@if(!empty($hero['title_line3']))<br><span class="font-light">{{ $hero['title_line3'] }}</span>@endif
                </flux:heading level="1">
                <p class="mt-4 max-w-xl text-sm leading-relaxed text-zinc-200">
                    {{ $hero['subtitle'] ?? 'Recherche, conseil et études en développement urbain. BTP, Électricité, Pétrole et Agro-industrie — réponses concrètes et contextualisées aux enjeux africains. Siège : Abidjan Bingerville, Abatta Lot 935 Îlot 86 — Dir. Ouattara Bassoma Ziegnougo.' }}
                </p>
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('front.projects.index') }}"
                        class="bg-white px-6 py-3 text-xs font-bold tracking-widest text-zinc-900 hover:bg-zinc-100">{{ $hero['cta_primary'] ?? 'EXPLORER LES RÉALISATIONS' }}</a>
                    <a href="{{ route('front.contact') }}"
                        class="border border-white px-6 py-3 text-xs font-bold tracking-widest text-white hover:bg-white hover:text-zinc-900">{{ $hero['cta_secondary'] ?? 'DEMANDER UNE ÉTUDE' }}</a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-6 right-6 hidden text-xs tracking-widest text-white/60 lg:block">ABATTA —
            BINGERVILLE • CÔTE D'IVOIRE</div>
    </div>

    <!-- 4 pôles transversaux — AfricaSpace -->
    <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <flux:heading level="2" size="xl" class="text-2xl font-light">
                    4 pôles <span class="font-black">transversaux</span>
                </flux:heading>
                <flux:text class="mt-0 max-w-3xl text-xs leading-relaxed text-zinc-500">
                    Grâce à la transversalité de ces thématiques, SIBEA-CI offre des réponses concrètes et contextualisées aux enjeux urbains africains.
                </flux:text>
            </div>
            <a href="{{ route('front.services.index') }}"
                class="text-xs font-bold tracking-widest text-primary hover:underline">TOUS LES SERVICES →</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-4">
            @php
                $poles = $cmsServices;
                $poleFallbacks = [
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop',
                ];
            @endphp
            @foreach (array_slice($poles,0,4) as $pole)
                @php
                    $title = $pole['title'] ?? $pole['key'];
                    $desc = $pole['desc'] ?? '';
                    $key = $pole['key'];
                    $hasImage = !empty($pole['image']) && Storage::disk('public')->exists($pole['image']);
                    $fallback = $poleFallbacks[$loop->index % count($poleFallbacks)];
                @endphp
                <a href="{{ route('front.services.show', $key) }}"
                    class="group relative overflow-hidden rounded-2xl min-h-[360px] flex flex-col justify-between p-7 shadow-sm hover:shadow-2xl transition-all duration-500 border border-zinc-200">
                    <!-- Image de fond — CMS ou fallback Unsplash -->
                    @if($hasImage)
                        <img src="{{ Storage::disk('public')->url($pole['image']) }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/60 to-zinc-900/10"></div>
                        <div class="absolute inset-0 bg-primary/20 mix-blend-multiply opacity-60 group-hover:opacity-30 transition duration-500"></div>
                    @else
                        <img src="{{ $fallback }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/70 to-zinc-900/20"></div>
                        <div class="absolute inset-0 bg-primary/10 group-hover:bg-primary/0 transition duration-500"></div>
                    @endif
                    <div class="relative">
                        <div class="text-xs tracking-widest text-white/60">
                            0{{ $loop->index + 1 }} — {{ strtoupper($key) }}
                        </div>
                        <h3 class="mt-3 text-xl font-black leading-tight text-white drop-shadow">{{ $title }}</h3>
                    </div>
                    <div class="relative">
                        <p class="text-sm leading-relaxed text-zinc-200 line-clamp-3">{{ $desc ?: 'Expertise SIBEA-CI — '.$title.' — réponses concrètes et contextualisées.' }}</p>
                        <div class="mt-4 inline-flex items-center gap-2 text-xs font-bold tracking-widest text-white group-hover:gap-3 transition-all">
                            DÉCOUVRIR <span class="transition-transform group-hover:translate-x-1">→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Terrains ACD — remonté après Hero/4 pôles pour conversion -->
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8 bg-amber-50/30 rounded-2xl border border-amber-100">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-black">Terrains viabilisés — ACD sécurisé</h2>
                <p class="text-xs tracking-widest text-zinc-500">Bingerville Abatta · Lotissements SIBEA-CI — viabilisation & titres fonciers</p>
            </div>
            <a href="{{ route('front.programs.index') }}" class="text-sm font-bold tracking-widest text-primary hover:underline">CATALOGUE COMPLET →</a>
        </div>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @forelse($availablePlots->take(3) as $plot)
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 hover:shadow transition">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-sm font-bold">{{ $plot->reference }}</span>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700">{{ $plot->status->label() }}</span>
                    </div>
                    <div class="mt-2 text-sm font-medium text-zinc-900">{{ $plot->program->title }} — {{ $plot->program->city }}</div>
                    <div class="mt-1 flex gap-4 text-xs text-zinc-600">
                        <span>{{ $plot->surface_m2 }} m²</span>
                        @if ($plot->price)<span class="font-bold text-primary">{{ number_format((float) $plot->price, 0, ',', ' ') }} FCFA</span>@endif
                    </div>
                    <a href="{{ route('front.programs.show', $plot->program) }}" class="mt-3 inline-flex text-xs font-bold tracking-widest text-primary hover:underline">VOIR LOT →</a>
                </div>
            @empty
                <div class="col-span-3 rounded-xl border border-dashed p-6 text-center text-sm text-zinc-500">Aucun lot disponible — catalogue complet bientôt.</div>
            @endforelse
        </div>
    </div>

    <!-- Stats — éditable CMS (5 compteurs) -->
    <div class="bg-zinc-900 py-10 text-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 text-center lg:grid-cols-5 lg:px-8">
            <div>
                <div class="text-4xl font-black text-[#4d7aa3]">
                    {{ number_format($stats['projects_completed'], 0, ',', ' ') }}</div>
                <div class="mt-1 text-xs tracking-widest text-zinc-400">PROJETS LIVRÉS</div>
            </div>
            <div>
                <div class="text-4xl font-black text-[#4d7aa3]">
                    {{ number_format($stats['happy_clients'], 0, ',', ' ') }}</div>
                <div class="mt-1 text-xs tracking-widest text-zinc-400">CLIENTS SATISFAITS</div>
            </div>
            <div>
                <div class="text-4xl font-black text-[#4d7aa3]">{{ number_format($stats['workers'], 0, ',', ' ') }}
                </div>
                <div class="mt-1 text-xs tracking-widest text-zinc-400">OUVRIERS EMPLOYÉS</div>
            </div>
            <div>
                <div class="text-4xl font-black text-[#4d7aa3]">{{ $stats['awards'] }}</div>
                <div class="mt-1 text-xs tracking-widest text-zinc-400">PRIX REMPORTÉS</div>
            </div>
            <div>
                <div class="text-4xl font-black text-[#4d7aa3]">{{ number_format($stats['surface_total'], 0, ',', ' ') }}</div>
                <div class="mt-1 text-xs tracking-widest text-zinc-400">M² AMÉNAGÉS</div>
            </div>
        </div>
    </div>

    <!-- RÉALISATIONS RÉCENTES — Tous / Architecture / Building ... -->
    <div x-data="{ portfolioFilter: 'all' }" class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="text-center">
            <flux:heading level="2" class="text-3xl! font-black! tracking-tight!">RÉALISATIONS RÉCENTES</flux:heading>
            <div class="mx-auto mt-2 h-1 w-12 bg-secondary"></div>
        </div>
        <div class="mt-6 flex flex-wrap justify-center gap-2 text-xs tracking-widest">
            <button @click="portfolioFilter = 'all'"
                :class="portfolioFilter === 'all' ? 'bg-primary text-white font-bold' : 'bg-zinc-100'"
                class="rounded-full px-4 py-1">Tous</button>
            @foreach (\App\Enums\ServiceType::cases() as $s)
                <button class="rounded-full px-4 py-1"
                    @click="portfolioFilter = '{{ $s->value }}'"
                    :class="portfolioFilter === '{{ $s->value }}' ? 'bg-primary text-white font-bold' : 'bg-primary/10 text-primary'" >
                    {{ $s->label() }}
                </button>
            @endforeach
        </div>
        <div class="mt-8 grid gap-4 md:grid-cols-4">
            @php $fallbacks = ['photo-1504307651254-35680f356dfd','photo-1486406146926-c627a92ad1ab','photo-1581091226825-a6a2a5aee158','photo-1503387762-592deb58ef4e','photo-1600585154340-be6161a56a0c','photo-1600596542815-ffad4c1539a9','photo-1581091226825-a6a2a5aee158','photo-1473341304170-971dccb5ac1e']; @endphp
            @forelse($recentWorks as $work)
                <a href="{{ route('front.projects.show', $work) }}"
                    x-show="portfolioFilter==='all' || portfolioFilter==='{{ $work->service_type->value }}'"
                    class="group relative overflow-hidden rounded-xl bg-zinc-100">
                    <div class="aspect-square overflow-hidden">
                        @if ($work->cover_path)
                            <img src="{{ Storage::disk('public')->url($work->cover_path) }}" alt="{{ $work->title }}"
                                class="size-full object-cover transition group-hover:scale-110" loading="lazy" />
                        @else
                            <img src="https://images.unsplash.com/{{ $fallbacks[$loop->index % count($fallbacks)] }}?w=600&q=80&auto=format&fit=crop"
                                alt="{{ $work->title }} — {{ $work->service_type->label() }} Abidjan"
                                class="size-full object-cover" loading="lazy" />
                        @endif
                    </div>
                    <div
                        class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-zinc-900/80 to-transparent p-4 opacity-0 transition group-hover:opacity-100">
                        <div class="text-sm font-bold text-white">{{ \Illuminate\Support\Str::upper($work->title) }}
                        </div>
                        <div class="text-xs text-zinc-300">{{ $work->service_type->label() }}</div>
                    </div>
                    <div class="absolute right-3 top-3 rounded-full bg-white/90 p-1 opacity-0 group-hover:opacity-100">⧉
                    </div>
                </a>
            @empty
                <div class="col-span-4 rounded-xl border border-dashed p-8 text-center text-zinc-500">Aucun projet
                    publié — créez-en depuis le backoffice.</div>
            @endforelse
        </div>
    </div>

    <!-- EXPERTISES TECHNIQUES — tableau unique (fusion des 2 sections Services redondantes) -->
    <div class="bg-zinc-50 py-16">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-black">EXPERTISES TECHNIQUES</h2>
                <div class="mt-2 h-1 w-12 bg-secondary"></div>
                <p class="mt-4 text-sm text-zinc-600">VRD, BTP, hydraulique — 6 pôles SIBEA-CI en une vue.</p>
            </div>
            <div class="mt-8 overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="bg-zinc-900 text-white"><tr><th class="px-4 py-3 text-left">Pôle</th><th class="px-4 py-3 text-left">Domaine</th><th class="px-4 py-3 text-left">Livrables</th></tr></thead>
                    <tbody>
                        @foreach($cmsServices as $svc)
                            <tr class="border-t border-zinc-100">
                                <td class="px-4 py-3 font-bold text-primary">{{ $svc['title'] }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $svc['desc'] ? Str::limit($svc['desc'], 80) : '—' }}</td>
                                <td class="px-4 py-3"><a href="{{ route('front.services.show', $svc['key']) }}" class="text-xs font-bold tracking-widest text-primary hover:underline">DÉTAIL →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Contractors Banner — CMS -->
    <div class="relative overflow-hidden bg-zinc-900 py-12">
        @if(!empty($banner['image']) && Storage::disk('public')->exists($banner['image']))
            <img src="{{ Storage::disk('public')->url($banner['image']) }}" alt="" class="absolute inset-0 size-full object-cover opacity-20" loading="lazy" />
        @else
            <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80&auto=format&fit=crop"
                alt="" class="absolute inset-0 size-full object-cover opacity-20" loading="lazy" />
        @endif
        <div
            class="relative mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 lg:flex-row lg:px-8">
            <h2 class="text-2xl font-black text-white">{{ $banner['title'] }}</h2>
            <a href="{{ $banner['cta_url'] ?? route('front.contact') }}"
                class="rounded bg-primary px-8 py-3 text-sm font-bold text-white hover:bg-[#002244]">{{ $banner['cta_label'] ?? 'DEMANDER UN DEVIS' }}</a>
        </div>
    </div>

    <!-- WHAT OTHER SAY — Témoignages (Sidebar CRUD Eloquent) -->
    <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-black">TÉMOIGNAGES CLIENTS</h2>
            <div class="mx-auto mt-2 h-1 w-12 bg-secondary"></div>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @foreach ($testimonials as $t)
                <div class="rounded-xl border border-zinc-200 p-6 text-center">
                    @if (!empty($t->avatar_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($t->avatar_path) }}"
                            alt="{{ $t->name }}" class="mx-auto size-16 rounded-full object-cover"
                            loading="lazy" />
                    @else
                        <img src="https://i.pravatar.cc/100?img={{ $loop->index + 1 }}" alt="{{ $t->name }}"
                            class="mx-auto size-16 rounded-full object-cover" loading="lazy" />
                    @endif
                    <div class="mt-3 font-bold">{{ $t->name }}</div>
                    <div class="text-xs text-zinc-500">{{ $t->role }}</div>
                    <p class="mt-3 text-sm text-zinc-600">{{ $t->content }}</p>
                    <div class="mt-2 text-xs text-amber-400">★{{ $t->rating }}/5</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- POURQUOI NOUS CHOISIR — CMS dynamique -->
    <div class="bg-zinc-50 py-16">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    @if(!empty($hero['slide2_image']) && Storage::disk('public')->exists($hero['slide2_image']))
                        <img src="{{ Storage::disk('public')->url($hero['slide2_image']) }}" alt="SIBEA-CI équipe" class="rounded-xl object-cover w-full" loading="lazy" />
                    @else
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop" alt="Group" class="rounded-xl" loading="lazy" />
                    @endif
                </div>
                <div>
                    <h2 class="text-2xl font-black">{{ $whyChoose['title'] ?? 'POURQUOI NOUS CHOISIR ?' }}</h2>
                    <div class="mt-6 space-y-6">
                        @php $items = $whyChoose['items'] ?? [['label'=>"Des équipes aux années d'expérience",'desc'=>'30 ans cumulés, chefs de chantier certifiés, formation sécurité continue. Chantiers Abidjan, Bouaké, Yamoussoukro.'],['label'=>'Une qualité qui perdure après la livraison','desc'=>'SAV, garantie décennale, suivi VRD post-livraison, entretien voirie.'],['label'=>'Nous utilisons la technologie pour aller plus vite','desc'=>'BIM, drone relevé topo, WebP/AVIF, Lean construction pour délais tenus.'],['label'=>'Nos équipes formées en continu à la sécurité','desc'=>'RSE, EPI, sécurité chantier, normes ivoiriennes.']]; @endphp
                        @foreach($items as $item)
                            <div>
                                <h4 class="font-bold">{{ $item['label'] }}</h4>
                                <p class="text-sm text-zinc-600">{{ $item['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NOTRE ÉQUIPE -->
    <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-black">NOTRE ÉQUIPE</h2>
            <div class="mx-auto mt-2 h-1 w-12 bg-secondary"></div>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-4">
            @foreach ($team as $member)
                <div class="text-center">
                    @if(!empty($member['avatar']) && Storage::disk('public')->exists($member['avatar']))
                        <img src="{{ Storage::disk('public')->url($member['avatar']) }}" alt="{{ $member['name'] }}"
                            class="mx-auto aspect-square w-full rounded-xl object-cover" loading="lazy" />
                    @else
                        <img src="https://i.pravatar.cc/400?img={{ $loop->index + 10 }}" alt="{{ $member['name'] }}"
                            class="mx-auto aspect-square w-full rounded-xl object-cover" loading="lazy" />
                    @endif
                    <div class="mt-3 font-bold">{{ $member['name'] }}</div>
                    <div class="text-xs text-zinc-500">{{ $member['role'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Partenaires — logos (Sidebar CRUD Eloquent) -->
    <div class="border-y border-zinc-200 bg-zinc-50 py-8">
        <div
            class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-8 px-4 opacity-60 grayscale lg:px-8">
            @foreach ($partners as $partner)
                @if (!empty($partner->logo_path))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($partner->logo_path) }}"
                        alt="{{ $partner->name }}" class="h-8" loading="lazy" />
                @else
                    <img src="https://via.placeholder.com/120x40/ffffff/000000?text={{ urlencode($partner->name) }}"
                        alt="{{ $partner->name }}" class="h-8" loading="lazy" />
                @endif
            @endforeach
        </div>
    </div>

    <!-- CTA -->
    <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="rounded-3xl bg-primary p-8 text-white lg:p-12">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Un projet ? Un terrain ?</h2>
                    <p class="mt-2 text-[#e6ecf2]">Devis BTP ou réservation de lot — réponse sous 24h.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('front.contact') }}"
                        class="rounded-full bg-white px-8 py-3 text-sm font-semibold text-[#002244] hover:bg-zinc-100">Demander
                        un devis</a>
                    @php $wa = preg_replace('/[^0-9]/', '', $global['company_whatsapp'] ?? $global['company_phone'] ?? '2250700000000'); @endphp
                    <a href="https://wa.me/{{ $wa }}" target="_blank" class="rounded-full bg-emerald-600 p-3 text-white hover:bg-emerald-700 inline-flex items-center justify-center" aria-label="WhatsApp"><svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg></a>
                </div>
            </div>
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('front.home.africaspace') }}"
                class="text-xs tracking-widest text-zinc-500 hover:text-primary">Voir variante AfricaSpace — hero épuré
                laboratoire →</a>
        </div>
    </div>
</section>
