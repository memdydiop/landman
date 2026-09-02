<?php

use App\Enums\InquiryStatus;
use App\Enums\PlotStatus;
use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Inquiry;
use App\Models\Partner;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Accueil — SIBEA-CI BTP, VRD & Aménagement Foncier')] class extends Component {
    public string $portfolioFilter = 'all';

    public function render(): \Illuminate\View\View
    {
        $featuredProjects = Cache::remember('home.featuredProjects', 300, fn () => Project::published()->featured()->latest('published_at')->limit(3)->get());
        $recentWorks = Cache::remember('home.recentWorks', 300, fn () => Project::published()->latest('published_at')->limit(8)->get());
        $programs = Cache::remember('home.programs', 300, fn () => Program::published()->withCount(['plots as available_plots_count' => fn ($q) => $q->where('status', PlotStatus::DISPONIBLE)])->latest()->limit(3)->get());
        $availablePlots = Cache::remember('home.availablePlots', 300, fn () => Plot::available()->with('program')->latest()->limit(6)->get());
        
        $testimonialsCached = Cache::remember('home.testimonials', 300, fn () => Testimonial::published()->orderBy('position')->limit(3)->get());
        $partnersCached = Cache::remember('home.partners', 300, fn () => Partner::published()->orderBy('position')->get());

        $hero = SiteSetting::get('home.hero', []);
        $statsSetting = SiteSetting::get('home.stats', []);
        
        $stats = Cache::remember('home.stats', 300, function () use ($statsSetting) {
            return [
                'projects_completed' => $statsSetting['projects_completed'] ?? (Project::where('status', ProjectStatus::LIVRE)->count() ?: 1240),
                'happy_clients' => $statsSetting['happy_clients'] ?? (Inquiry::where('status', InquiryStatus::TRAITE)->count() ?: 1750),
                'surface_total' => (int) ($statsSetting['surface_total'] ?? (Program::published()->sum('total_area') ?: 984000)),
                'awards' => $statsSetting['awards'] ?? 96,
                'plots_available' => Plot::available()->count(),
                'workers' => $statsSetting['workers'] ?? 984,
            ];
        });

        $whyChoose = SiteSetting::get('home.why_choose', null);
        $cmsServices = SiteSetting::get('services.list', []);
        
        if (empty($cmsServices)) {
            $cmsServices = array_map(fn($c) => [
                'key' => $c->value, 
                'title' => $c->label(), 
                'desc' => '', 
                'image' => null
            ], ServiceType::cases());
        }

        $testimonials = $testimonialsCached;
        if ($testimonials->isEmpty()) {
            $testimonials = collect([
                (object) ['name' => 'Kouassi Jean — Abidjan Cocody', 'role' => 'Maître d\'Ouvrage — Villa F4', 'content' => 'Livraison dans les délais contractuels, viabilisation VRD rigoureuse et arrêté ACD vérifié.', 'rating' => 5, 'avatar_path' => null],
                (object) ['name' => 'Awa Koné — Bingerville Abatta', 'role' => 'Promoteur Immobilier', 'content' => 'Supervision géodésique et travaux d\'assainissement irréprochables par l\'équipe SIBEA-CI.', 'rating' => 5, 'avatar_path' => null],
                (object) ['name' => 'Yao Kouamé — Bouaké', 'role' => 'Ingénieur Infrastructure', 'content' => 'Collaboration technique fluide, respect strict du cahier des charges BTP.', 'rating' => 5, 'avatar_path' => null]
            ]);
        }

        $partners = $partnersCached;
        if ($partners->isEmpty()) {
            $partners = collect(['NSIA BANQUE', 'SIB CÔTE D\'IVOIRE', 'BOA CI', 'SOCIETE GENERALE CI', 'BACI'])->map(fn($n) => (object) [
                'name' => $n, 
                'logo_path' => null
            ]);
        }

        $global = SiteSetting::get('global', []);
        $banner = SiteSetting::get('home.banner', [
            'title' => 'Ingénieurs, Conducteurs de Travaux & Aménageurs depuis plus de 20 ans',
            'cta_label' => 'DEMANDER UNE ÉTUDE TECHNIQUE',
            'cta_url' => '/contact',
            'image' => null,
        ]);
        
        $team = SiteSetting::get('home.team', [
            ['name' => 'Ouattara Bassoma Ziegnougo', 'role' => 'Gérant & Direction SARL', 'avatar' => null],
            ['name' => 'Kouamé Yao', 'role' => 'Ingénieur Civil — VRD', 'avatar' => null],
            ['name' => 'Awa Koné', 'role' => 'Chef de Chantier Foncier', 'avatar' => null],
            ['name' => 'Diabaté Moussa', 'role' => 'Responsable Électricité & Réseaux', 'avatar' => null],
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
            'banner' => $banner,
            'team' => $team,
        ]);
    }
}; ?>

<section class="bg-zinc-100/70 min-h-screen pb-12">
    
    {{-- HERO CHANTIER & LABORATOIRE URBAIN --}}
    <div class="relative flex min-h-[500px] h-[68svh] sm:h-[72svh] lg:h-[80vh] max-h-[850px] items-center overflow-hidden bg-zinc-950 border-b border-zinc-800">
        @if(!empty($hero['slide1_image']) && Storage::disk('public')->exists($hero['slide1_image']))
            <img src="{{ Storage::disk('public')->url($hero['slide1_image']) }}" alt="SIBEA-CI Chantier" class="absolute inset-0 size-full object-cover opacity-35" loading="eager" />
        @else
            <video autoplay muted loop playsinline
                poster="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80&auto=format&fit=crop"
                class="absolute inset-0 size-full object-cover opacity-30">
                <source src="https://videos.pexels.com/video-files/18069234/18069234-uhd_1440_1440_24fps.mp4" type="video/mp4">
            </video>
        @endif
        
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/60 to-transparent"></div>

        <div class="relative mx-auto flex w-full max-w-7xl items-center px-4 lg:px-8 py-10">
            <div class="max-w-3xl border-l-4 border-amber-500 pl-6 space-y-4">
                <div class="inline-flex items-center gap-2 rounded bg-amber-500/20 px-3 py-1 font-mono text-xs font-bold text-amber-400 border border-amber-500/30 uppercase tracking-widest">
                    <span>🏗️</span> {{ $hero['badge'] ?? 'SARL • IDU CI-2022-0016466 Q • ABIDJAN BINGERVILLE' }}
                </div>
                
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white uppercase tracking-tight leading-[1.1]">
                    {{ $hero['title_line1'] ?? 'SIBEA-CI' }}<br>
                    <span class="text-amber-500">{{ $hero['title_line2'] ?? 'INGÉNIERIE & BTP' }}</span>
                    @if(!empty($hero['title_line3']))
                        <br><span class="font-light text-zinc-300 text-2xl sm:text-3xl lg:text-4xl">{{ $hero['title_line3'] }}</span>
                    @endif
                </h1>

                <p class="max-w-xl text-xs sm:text-sm leading-relaxed text-zinc-300 font-normal">
                    {{ $hero['subtitle'] ?? 'Aménagement foncier sécurisé, viabilisation VRD, génie civil et construction clé en main. Réponse technique directe aux exigences des chantiers en Côte d\'Ivoire.' }}
                </p>

                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ route('front.projects.index') }}"
                        class="rounded-xl bg-amber-500 px-6 py-3.5 font-mono text-xs font-black tracking-wider text-zinc-950 hover:bg-amber-400 transition uppercase shadow-lg shadow-amber-500/10">
                        {{ $hero['cta_primary'] ?? 'VOIR RÉALISATIONS CHANTIER' }}
                    </a>
                    <a href="{{ route('front.contact') }}"
                        class="rounded-xl border border-zinc-700 bg-zinc-900/80 backdrop-blur-sm px-6 py-3.5 font-mono text-xs font-bold tracking-wider text-white hover:bg-zinc-800 transition uppercase">
                        {{ $hero['cta_secondary'] ?? 'DEMANDER UN DEVIS / AUDIT' }}
                    </a>
                </div>
            </div>
        </div>

        <div class="absolute bottom-6 right-6 hidden font-mono text-[10px] tracking-widest text-zinc-400 lg:block bg-zinc-900/90 border border-zinc-800 px-3 py-1.5 rounded">
            📍 ABATTA — BINGERVILLE • CÔTE D'IVOIRE
        </div>
    </div>

    {{-- STATS DE CHANTIER (BARRE DE COMMANDEMENT) --}}
    <div class="bg-zinc-900 border-b border-zinc-800 py-8 text-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 text-center lg:grid-cols-5 lg:px-8">
            <div class="border-r border-zinc-800/80 last:border-none">
                <div class="font-mono text-3xl sm:text-4xl font-black text-amber-500">{{ number_format($stats['projects_completed'], 0, ',', ' ') }}</div>
                <div class="mt-1 font-mono text-[10px] font-bold tracking-widest text-zinc-400 uppercase">PROJETS LIVRÉS</div>
            </div>
            <div class="border-r border-zinc-800/80 last:border-none">
                <div class="font-mono text-3xl sm:text-4xl font-black text-amber-500">{{ number_format($stats['happy_clients'], 0, ',', ' ') }}</div>
                <div class="mt-1 font-mono text-[10px] font-bold tracking-widest text-zinc-400 uppercase">ACD & CLIENTS SÉCURISÉS</div>
            </div>
            <div class="border-r border-zinc-800/80 last:border-none">
                <div class="font-mono text-3xl sm:text-4xl font-black text-amber-500">{{ number_format($stats['workers'], 0, ',', ' ') }}</div>
                <div class="mt-1 font-mono text-[10px] font-bold tracking-widest text-zinc-400 uppercase">TECHNICIENS & OUVRIERS</div>
            </div>
            <div class="border-r border-zinc-800/80 last:border-none">
                <div class="font-mono text-3xl sm:text-4xl font-black text-amber-500">{{ $stats['awards'] }}%</div>
                <div class="mt-1 font-mono text-[10px] font-bold tracking-widest text-zinc-400 uppercase">CONFORMITÉ TECHNIQUE</div>
            </div>
            <div class="col-span-2 lg:col-span-1">
                <div class="font-mono text-3xl sm:text-4xl font-black text-amber-500">{{ number_format($stats['surface_total'], 0, ',', ' ') }}</div>
                <div class="mt-1 font-mono text-[10px] font-bold tracking-widest text-zinc-400 uppercase">M² VIABILISÉS & VRD</div>
            </div>
        </div>
    </div>

    {{-- 4 PÔLES D'EXPERTISE CHANTIER --}}
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <span class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest">STRUCTURE & EXÉCUTION</span>
                <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 uppercase tracking-tight">4 PÔLES D'EXPERTISE FONDAMENTAUX</h2>
                <p class="mt-1 text-xs text-zinc-500">De l'étude de sol géotechnique à la livraison des infrastructures urbaines.</p>
            </div>
            <a href="{{ route('front.services.index') }}"
                class="inline-flex items-center gap-1.5 font-mono text-xs font-bold tracking-wider text-amber-600 hover:text-amber-700 uppercase">
                TOUTES NOS EXPERTISES →
            </a>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @php
                $poles = $cmsServices;
                $poleFallbacks = [
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop',
                ];
            @endphp
            @foreach (array_slice($poles, 0, 4) as $pole)
                @php
                    $title = $pole['title'] ?? $pole['key'];
                    $desc = $pole['desc'] ?? '';
                    $key = $pole['key'];
                    $hasImage = !empty($pole['image']) && Storage::disk('public')->exists($pole['image']);
                    $fallback = $poleFallbacks[$loop->index % count($poleFallbacks)];
                    $validServiceKey = \App\Enums\ServiceType::tryFrom($key) ? $key : 'btp';
                @endphp
                <a href="{{ route('front.services.show', $validServiceKey) }}"
                    class="group relative overflow-hidden rounded-2xl min-h-[380px] flex flex-col justify-between p-6 shadow-sm hover:shadow-xl transition-all duration-300 border border-zinc-300/80 bg-zinc-900">
                    
                    @if($hasImage)
                        <img src="{{ Storage::disk('public')->url($pole['image']) }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover opacity-60 transition duration-500 group-hover:scale-105" loading="lazy" />
                    @else
                        <img src="{{ $fallback }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover opacity-60 transition duration-500 group-hover:scale-105" loading="lazy" />
                    @endif
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/70 to-zinc-950/20"></div>

                    <div class="relative z-10">
                        <div class="font-mono text-[10px] font-bold tracking-widest text-amber-400 bg-zinc-900/80 px-2.5 py-1 rounded w-fit border border-amber-500/30 uppercase">
                            PÔLE 0{{ $loop->index + 1 }}
                        </div>
                        <h3 class="mt-3 text-lg font-black leading-tight text-white uppercase">{{ $title }}</h3>
                    </div>

                    <div class="relative z-10 space-y-3">
                        <p class="text-xs leading-relaxed text-zinc-300 line-clamp-3">{{ $desc ?: 'Ingénierie SIBEA-CI — '.$title.' — conformité technique et exécution terrain.' }}</p>
                        <div class="inline-flex items-center gap-2 font-mono text-[11px] font-bold tracking-wider text-amber-400 group-hover:text-amber-300 uppercase">
                            EXPLORER <span class="transition-transform group-hover:translate-x-1">→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- TERRAINS SÉCURISÉS & ACD --}}
    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <div class="rounded-2xl border border-zinc-300/80 bg-white p-6 lg:p-8 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-zinc-100 pb-4">
                <div>
                    <span class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest">LOTISSEMENT & AMÉNAGEMENT</span>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-900 uppercase">TERRAINS VIABILISÉS AVEC ACD SÉCURISÉ</h2>
                    <p class="text-xs text-zinc-500">Achat direct, viabilisation complète (eau, électricité, voirie VRD) à Abidjan & Bingerville Abatta.</p>
                </div>
                <a href="{{ route('front.programs.index') }}" class="font-mono text-xs font-bold text-amber-600 hover:text-amber-700 uppercase shrink-0">
                    CATALOGUE LOTS →
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @forelse($availablePlots->take(3) as $plot)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 hover:border-amber-500 transition shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-black text-zinc-900 bg-amber-500/20 px-2 py-0.5 rounded border border-amber-500/30">
                                REF: {{ $plot->reference }}
                            </span>
                            <span class="rounded bg-emerald-100 px-2 py-0.5 font-mono text-[10px] font-bold text-emerald-800">
                                {{ $plot->status->label() }}
                            </span>
                        </div>

                        <div>
                            <div class="text-xs font-bold text-zinc-900">{{ $plot->program->title }}</div>
                            <div class="text-[11px] text-zinc-500">📍 {{ $plot->program->city }}</div>
                        </div>

                        <div class="flex items-center justify-between font-mono text-xs border-t border-zinc-200/80 pt-2">
                            <span class="text-zinc-600">{{ $plot->surface_m2 }} m²</span>
                            @if ($plot->price)
                                <span class="font-black text-amber-600">{{ number_format((float) $plot->price, 0, ',', ' ') }} FCFA</span>
                            @endif
                        </div>

                        <a href="{{ route('front.programs.show', $plot->program) }}" class="block text-center rounded bg-zinc-900 px-3 py-2 font-mono text-[10px] font-bold text-white hover:bg-zinc-800 uppercase">
                            RÉSERVER CE LOT →
                        </a>
                    </div>
                @empty
                    <div class="col-span-3 rounded-xl border border-dashed border-zinc-300 p-6 text-center text-xs text-zinc-500 font-mono">
                        Aucun lot immédiatement disponible — Consultation du registre foncier en cours.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RÉALISATIONS DE CHANTIER (PORTFOLIO FILTER) --}}
    <div x-data="{ portfolioFilter: 'all' }" class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <span class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest">TRAÇABILITÉ CHANTIER</span>
                <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 uppercase">RÉALISATIONS RÉCENTES DE CHANTIER</h2>
            </div>

            <!-- Filtres des projets -->
            <div class="flex flex-wrap gap-1.5 font-mono text-[10px]">
                <button @click="portfolioFilter = 'all'"
                    :class="portfolioFilter === 'all' ? 'bg-zinc-900 text-amber-400 font-bold' : 'bg-white text-zinc-700 border border-zinc-200'"
                    class="rounded px-3 py-1.5 uppercase transition">Tous</button>
                @foreach (\App\Enums\ServiceType::cases() as $s)
                    <button @click="portfolioFilter = '{{ $s->value }}'"
                        :class="portfolioFilter === '{{ $s->value }}' ? 'bg-zinc-900 text-amber-400 font-bold' : 'bg-white text-zinc-700 border border-zinc-200'"
                        class="rounded px-3 py-1.5 uppercase transition">
                        {{ $s->label() }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            @php $fallbacks = ['photo-1504307651254-35680f356dfd','photo-1486406146926-c627a92ad1ab','photo-1581091226825-a6a2a5aee158','photo-1503387762-592deb58ef4e','photo-1600585154340-be6161a56a0c','photo-1600596542815-ffad4c1539a9','photo-1581091226825-a6a2a5aee158','photo-1473341304170-971dccb5ac1e']; @endphp
            @forelse($recentWorks as $work)
                @php $typeValue = is_object($work->service_type) ? $work->service_type->value : $work->service_type; @endphp
                <a href="{{ route('front.projects.show', $work) }}"
                    x-show="portfolioFilter === 'all' || portfolioFilter === '{{ $typeValue }}'"
                    class="group relative overflow-hidden rounded-xl border border-zinc-300/80 bg-zinc-900 shadow-sm aspect-square">
                    
                    @if ($work->cover_path)
                        <img src="{{ Storage::disk('public')->url($work->cover_path) }}" alt="{{ $work->title }}" class="size-full object-cover transition duration-500 group-hover:scale-105 opacity-80" loading="lazy" />
                    @else
                        <img src="https://images.unsplash.com/{{ $fallbacks[$loop->index % count($fallbacks)] }}?w=600&q=80&auto=format&fit=crop" alt="{{ $work->title }}" class="size-full object-cover opacity-80 transition duration-500 group-hover:scale-105" loading="lazy" />
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-transparent p-4 flex flex-col justify-end">
                        <span class="font-mono text-[9px] font-bold text-amber-400 uppercase tracking-widest">
                            {{ is_object($work->service_type) ? $work->service_type->label() : $work->service_type }}
                        </span>
                        <h3 class="text-xs font-black text-white uppercase mt-0.5 line-clamp-1">{{ $work->title }}</h3>
                    </div>

                    <div class="absolute top-2 right-2 bg-zinc-900/90 text-amber-400 p-1 rounded font-mono text-[10px] border border-amber-500/30 opacity-0 group-hover:opacity-100 transition">
                        🔍
                    </div>
                </a>
            @empty
                <div class="col-span-4 rounded-xl border border-dashed border-zinc-300 p-8 text-center text-xs text-zinc-500 font-mono">
                    Aucun chantier ou projet publié actuellement dans ce pôle.
                </div>
            @endforelse
        </div>
    </div>

    {{-- BANNIÈRE DE DIRECTION DE CHANTIER --}}
    <div class="relative overflow-hidden bg-zinc-950 py-12 border-y border-zinc-800">
        @if(!empty($banner['image']) && Storage::disk('public')->exists($banner['image']))
            <img src="{{ Storage::disk('public')->url($banner['image']) }}" alt="SIBEA-CI Chantier" class="absolute inset-0 size-full object-cover opacity-20" loading="lazy" />
        @else
            <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1920&q=80&auto=format&fit=crop" alt="SIBEA-CI Chantier" class="absolute inset-0 size-full object-cover opacity-20" loading="lazy" />
        @endif
        
        <div class="relative mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 lg:flex-row lg:px-8 text-center lg:text-left">
            <div>
                <span class="font-mono text-[10px] font-bold text-amber-400 uppercase tracking-widest">MAÎTRISE D'OUVRAGE & SUPERVISION</span>
                <h2 class="text-xl sm:text-2xl font-black text-white uppercase mt-1">{{ $banner['title'] }}</h2>
            </div>
            <a href="{{ $banner['cta_url'] ?? route('front.contact') }}" 
               class="rounded-xl bg-amber-500 px-6 py-3.5 font-mono text-xs font-black text-zinc-950 hover:bg-amber-400 transition uppercase shrink-0">
                {{ $banner['cta_label'] ?? 'DEMANDER UN DEVIS' }}
            </a>
        </div>
    </div>

    {{-- TÉMOIGNAGES CLIENTS & MAÎTRES D'OUVRAGE --}}
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="rounded-2xl border border-zinc-300/80 bg-white p-8 shadow-sm space-y-6">
            <div class="text-center max-w-xl mx-auto space-y-1">
                <span class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest">GARANTIE DE SATISFACTION</span>
                <h2 class="text-2xl font-black text-zinc-900 uppercase">AVIS ET RETOURS MAÎTRES D'OUVRAGE</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($testimonials as $t)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-5 flex flex-col justify-between space-y-3">
                        <p class="text-xs leading-relaxed text-zinc-700 italic">“{{ $t->content }}”</p>
                        
                        <div class="pt-3 border-t border-zinc-200/80 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-zinc-900 text-amber-400 font-mono font-bold flex items-center justify-center text-xs shrink-0">
                                {{ strtoupper(substr($t->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-xs font-black text-zinc-900 uppercase">{{ $t->name }}</div>
                                <div class="text-[10px] font-mono text-amber-600">{{ $t->role }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ÉQUIPE TECHNIQUE & ENCADREMENT --}}
    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <div class="rounded-2xl border border-zinc-300/80 bg-white p-8 shadow-sm space-y-6">
            <div class="text-center max-w-xl mx-auto space-y-1">
                <span class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest">ENCADREMENT TECHNIQUE</span>
                <h2 class="text-2xl font-black text-zinc-900 uppercase">CONDUCTEURS & INGÉNIEURS DE CHANTIER</h2>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-4">
                @foreach ($team as $member)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 overflow-hidden shadow-sm text-center">
                        <div class="h-48 w-full bg-zinc-900 flex items-center justify-center relative overflow-hidden">
                            @if(!empty($member['avatar']) && Storage::disk('public')->exists($member['avatar']))
                                <img src="{{ Storage::disk('public')->url($member['avatar']) }}" alt="{{ $member['name'] }}" class="w-full h-full object-cover object-top" loading="lazy" />
                            @else
                                <div class="w-14 h-14 rounded-xl border border-amber-500/40 bg-zinc-800 flex items-center justify-center text-amber-500 font-mono font-black text-lg">
                                    {{ strtoupper(substr($member['name'], 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="p-4 bg-white border-t border-zinc-100">
                            <h3 class="text-xs font-black text-zinc-900 uppercase leading-tight">{{ $member['name'] }}</h3>
                            <p class="mt-1 font-mono text-[10px] font-bold text-amber-600 uppercase">{{ $member['role'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- PARTENAIRES & INSTITUTIONS --}}
    <div class="mx-auto max-w-7xl px-4 py-4 lg:px-8">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 opacity-80 grayscale hover:grayscale-0 transition-all">
            <div class="text-center font-mono text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-4">
                ÉTABLISSEMENTS & PARTENAIRES DE CONFIANCE
            </div>
            <div class="flex flex-wrap items-center justify-center gap-8">
                @foreach ($partners as $partner)
                    <span class="font-mono text-xs font-black tracking-wider text-zinc-700 bg-zinc-100 px-3 py-1.5 rounded border border-zinc-200">
                        {{ $partner->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- POSTE DE COMMANDEMENT & CONTACT WHATSAPP --}}
    <div class="mx-auto max-w-7xl px-4 pt-8 lg:px-8">
        <div class="rounded-2xl bg-zinc-900 p-8 lg:p-10 text-white border border-amber-500/30 shadow-xl flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <span class="font-mono text-[10px] font-bold text-amber-400 uppercase tracking-widest">ASSISTANCE RAPIDE CHANTIER</span>
                <h3 class="text-xl sm:text-2xl font-black uppercase mt-1">VOUS AVEZ UN PROJET DE CONSTRUCTION OU UN TERRAIN ?</h3>
                <p class="mt-1 text-xs text-zinc-400">Réponse sous 24h — Devis BTP, étude VRD ou réservation foncière.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('front.contact') }}" class="rounded-xl bg-amber-500 px-6 py-3.5 font-mono text-xs font-black text-zinc-950 hover:bg-amber-400 transition uppercase">
                    DEMANDER UN DEVIS →
                </a>

                @php
                    $waRaw = $global['company_whatsapp'] ?? $global['company_phone'] ?? '2250700000000';
                    $wa = preg_replace('/[^0-9]/', '', $waRaw);
                    if (strlen($wa) === 10 && str_starts_with($wa, '07')) $wa = '225'.ltrim($wa, '0');
                    if (strlen($wa) === 10 && !str_starts_with($wa, '225')) $wa = '225'.$wa;
                @endphp
                
                <a href="https://wa.me/{{ $wa }}" target="_blank" class="rounded-xl bg-emerald-600 p-3.5 text-white hover:bg-emerald-500 transition inline-flex items-center justify-center" aria-label="WhatsApp">
                    <svg class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
