<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('À propos — SIBEA-CI Laboratoire')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $hero = Cache::remember('about.hero', 300, fn () => \App\Models\SiteSetting::get('about.hero', [
            'title' => 'À propos — SIBEA-CI',
            'body' => 'Laboratoire urbain ivoirien — BTP, VRD, foncier sécurisé. 30 ans cumulés, agréments, ACD.',
            'badge' => 'LABORATOIRE URBAIN • ABIDJAN 2020',
            'image' => null,
        ]));
        $progress = \App\Models\SiteSetting::get('about.progress', [
            ['label' => 'BTP & GÉNIE CIVIL', 'pct' => 95],
            ['label' => 'ÉLECTRICITÉ', 'pct' => 88],
            ['label' => 'PÉTROLE & ÉNERGIE', 'pct' => 85],
            ['label' => 'AGRO-INDUSTRIE', 'pct' => 90],
        ]);
        $team = \App\Models\SiteSetting::get('home.team', [
            ['name' => 'Ouattara Bassoma Ziegnougo', 'role' => 'Gérant — SARL', 'avatar' => null],
            ['name' => 'Richard Wagner', 'role' => 'Ingénieur Civil', 'avatar' => null],
            ['name' => 'Sarah Spence', 'role' => 'Assistante Conducteur', 'avatar' => null],
            ['name' => 'John Halpern', 'role' => 'Conducteur de Travaux', 'avatar' => null],
        ]);
        return view('pages.front.about', [
            'hero' => $hero,
            'progress' => $progress,
            'team' => $team,
        ]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        :title="$hero['title'] ?: 'Bâtir l\'avenir de l\'Afrique<br>avec rigueur, confiance et intégrité.'"
        :subtitle="$hero['body'] ?? $hero['subtitle'] ?? 'SIBEA-CI accompagne particuliers, entreprises et collectivités en Côte d\'Ivoire — du lotissement viabilisé à la construction livrée, avec ACD et garanties.'"
        :badge="$hero['badge'] ?? 'LABORATOIRE URBAIN • ABIDJAN 2020'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'À propos SIBEA-CI'"
        :breadcrumb="[['label'=>'À propos de nous','url'=>route('front.about')]]"
    />

    <!-- Engagement — AfricaSpace 12col + ARRA double image & icon-list -->
    <div class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Left images — ARRA style: double image with bob -->
                <div class="lg:col-span-6 relative order-2 lg:order-1">
                    <div class="relative rounded-none overflow-hidden shadow-xl border border-zinc-100">
                        @if(!empty($hero['image']) && Storage::disk('public')->exists($hero['image']))
                            <img src="{{ Storage::disk('public')->url($hero['image']) }}" alt="SIBEA-CI chantier" class="w-full h-[380px] object-cover" loading="lazy" />
                        @else
                            <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80" alt="SIBEA-CI chantier" class="w-full h-[380px] object-cover" loading="lazy" />
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#003366]/30 to-transparent"></div>
                    </div>
                    <!-- second image overlapping — ARRA -->
                    <div class="hidden md:block absolute -bottom-6 -right-6 w-48 h-48 rounded-none overflow-hidden shadow-xl border-4 border-white">
                        <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=400&q=80" alt="Travaux publics" class="w-full h-full object-cover" loading="lazy" />
                    </div>
                    <!-- floating card — AfricaSpace -->
                    <div class="absolute -bottom-6 sm:-bottom-8 -left-2 sm:left-6 bg-white p-6 shadow-2xl border-2 border-[#003366] max-w-[260px]">
                        <p class="text-3xl font-black text-[#003366] font-mono">+10 projets</p>
                        <p class="text-xs font-bold text-[#87CEEB] mt-1 uppercase tracking-wider">Conformité technique à 100%</p>
                        <p class="text-[10px] text-zinc-500 mt-1.5 leading-tight">Livrés avec traçabilité totale — zéro litige foncier, zéro défaut de structure.</p>
                    </div>
                </div>
                <!-- Right text — ARRA heading + AfricaSpace typography -->
                <div class="lg:col-span-6 space-y-6 order-1 lg:order-2">
                    <div class="space-y-3">
                        <h6 class="text-xs font-extrabold text-[#003366] uppercase tracking-widest flex items-center gap-2"><span class="text-[#87CEEB]">◈</span> À propos de nous</h6>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#003366] tracking-tight leading-tight">SIBEA-CI — Laboratoire urbain ivoirien <span class="block text-[#003366]/80 font-light">Construisons ensemble votre avenir en Côte d'Ivoire</span></h2>
                    </div>
                    <div class="space-y-4 text-sm leading-relaxed text-zinc-600">
                        <p>Chez <strong class="text-[#003366]">SIBEA-CI</strong>, nous transformons vos idées en réalité en combinant <strong>expertise, innovation et qualité</strong>. Spécialistes du <strong>BTP, du génie civil et des infrastructures</strong>, nous accompagnons nos clients dans la conception et la réalisation de projets durables, adaptés aux exigences techniques et environnementales ivoiriennes.</p>
                        <h4 class="font-bold text-zinc-900">Des Solutions Adaptées à Tous Vos Projets</h4>
                        <p>Que vous soyez un particulier, une entreprise ou une collectivité, nous mettons notre savoir-faire à votre service pour construire, rénover ou moderniser vos bâtiments et infrastructures. Nous intervenons dans plusieurs domaines :</p>
                    </div>
                    <!-- Icon list — ARRA check-circle -->
                    <ul class="space-y-2">
                        @foreach([ 'Construction de bâtiments résidentiels et commerciaux.', 'Travaux publics et infrastructures urbaines (VRD).', 'Rénovation et réhabilitation de structures existantes.', 'Génie civil et ouvrages industriels — ACD sécurisé.' ] as $li)
                            <li class="flex gap-3 text-sm text-zinc-700">
                                <span class="mt-0.5 flex size-5 items-center justify-center rounded-full bg-emerald-500 text-white text-xs">✓</span>
                                <span>{{ $li }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Valeurs fondamentales — AfricaSpace 3 cards -->
    <div class="py-16 lg:py-24 bg-[#E3F2FD]/20 border-y border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
                <h2 class="text-3xl font-extrabold text-[#003366] tracking-tight">Nos valeurs fondamentales</h2>
                <p class="text-sm text-zinc-500 font-light">Des principes techniques et déontologiques stricts au service de la sécurisation de vos investissements fonciers et BTP.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach([
                    ['title'=>'Intégrité & Rigueur','desc'=>'Traçabilité totale, zéro intermédiaire douteux, surveillance rigoureuse ACD, titres fonciers, permis et purges coutumières.','icon'=>'⬡'],
                    ['title'=>'Innovation Durable','desc'=>'Matériaux résistants et écologiques, BIM, drone, WebP/AVIF, éco-conception pour durabilité.','icon'=>'◈'],
                    ['title'=>'Accompagnement Humain','desc'=>'Écoute, étude personnalisée, suivi de chantier et SAV — de l\'étude à la remise des clés.','icon'=>'◆'],
                ] as $v)
                    <div class="bg-white p-8 border border-zinc-100 shadow-sm hover:shadow-md transition-all flex flex-col">
                        <div class="w-12 h-12 bg-[#E3F2FD] flex items-center justify-center text-[#003366] font-bold text-lg mb-5">{{ $v['icon'] }}</div>
                        <h3 class="text-lg font-bold text-[#003366] tracking-tight">{{ $v['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-zinc-600">{{ $v['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Pourquoi nous choisir — ARRA icon-list + AfricaSpace progress -->
    <div class="py-16 lg:py-24 bg-white border-t border-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12 space-y-3">
                <h6 class="text-xs font-extrabold text-[#003366] uppercase tracking-widest flex items-center justify-center gap-2"><span class="text-[#87CEEB]">◈</span> Pourquoi SIBEA-CI ?</h6>
                <h2 class="text-3xl font-extrabold text-zinc-900">Pourquoi nous choisir ?</h2>
                <p class="text-sm text-zinc-500">Une chaîne d'expertises intégrée — de l'audit foncier à la livraison — pour sécuriser chaque étape.</p>
            </div>
            <div class="grid gap-12 lg:grid-cols-2 items-start">
                <ul class="space-y-3">
                    @foreach([
                        ['title'=>'Un accompagnement personnalisé','desc'=>'Chaque projet est unique. Nous étudions vos besoins pour proposer des solutions sur mesure.'],
                        ['title'=>'Des matériaux de qualité','desc'=>'Matériaux résistants et écologiques pour garantir la durabilité de vos infrastructures.'],
                        ['title'=>'Une équipe d’experts qualifiés','desc'=>'Ingénieurs, architectes et techniciens engagés pour livrer des ouvrages fiables et conformes aux normes.'],
                        ['title'=>'Respect des délais et du budget','desc'=>'Nous livrons dans les meilleures conditions, en tenant compte de vos contraintes financières.'],
                    ] as $item)
                        <li class="flex gap-3 p-4 rounded-xl border border-zinc-100 bg-zinc-50">
                            <span class="flex size-6 items-center justify-center rounded-full bg-emerald-500 text-white text-xs shrink-0 mt-0.5">✓</span>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $item['title'] }}</div>
                                <div class="text-xs leading-relaxed text-zinc-600">{{ $item['desc'] }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="space-y-6">
                    <div class="rounded-2xl bg-zinc-50 p-6 border border-zinc-100">
                        <h4 class="font-bold text-zinc-900">Construisons Ensemble un Avenir Durable</h4>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-600">Nous mettons tout en œuvre pour bâtir des infrastructures modernes, solides et respectueuses de l’environnement. Nos solutions intègrent les dernières innovations en matière de construction durable.</p>
                        <a href="{{ route('front.contact') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">📞 Contactez-nous →</a>
                    </div>
                    <!-- Progress bars — CMS -->
                    <div class="space-y-4">
                        @foreach($progress as $p)
                            <div>
                                <div class="flex justify-between text-xs font-bold tracking-widest"><span>{{ $p['label'] }}</span><span class="text-primary">{{ $p['pct'] }}%</span></div>
                                <div class="mt-1.5 h-2.5 rounded-full bg-zinc-200 overflow-hidden">
                                    <div class="h-2.5 rounded-full bg-[#003366] transition-all" style="width: {{ $p['pct'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Équipe & Gouvernance — AfricaSpace -->
    <div class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12 space-y-4">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-[#003366] bg-[#87CEEB]/20 px-3.5 py-1 inline-block">ÉQUIPE & GOUVERNANCE PLURIDISCIPLINAIRE</span>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-[#003366] tracking-tight">Une chaîne d'expertises intégrée de bout en bout</h2>
                <p class="text-sm text-zinc-600 leading-relaxed">Une équipe pluridisciplinaire mobilisée autour de chaque opération pour sécuriser l'investissement, maîtriser l'exécution et valoriser durablement le patrimoine.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($team as $member)
                    @php
                        $avatar = $member['avatar'] ?? null;
                        $hasAvatar = !empty($avatar) && Storage::disk('public')->exists($avatar);
                        $isLeadership = $loop->first;
                    @endphp
                    <div class="bg-white rounded-none border {{ $isLeadership ? 'border-[#003366] ring-1 ring-[#003366]/20' : 'border-zinc-200' }} overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col group">
                        <div class="h-64 w-full relative overflow-hidden bg-slate-100 flex items-center justify-center">
                            @if($hasAvatar)
                                <img src="{{ Storage::disk('public')->url($avatar) }}" alt="{{ $member['name'] }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-b from-[#003366]/5 to-[#003366]/15">
                                    <div class="w-20 h-20 border-2 border-[#003366] bg-[#003366] flex items-center justify-center text-white font-mono font-extrabold text-xl mb-3">{{ strtoupper(substr($member['name'],0,2)) }}</div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#003366]">{{ $member['name'] }}</span>
                                </div>
                            @endif
                            <div class="absolute top-3 left-3">
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-1 {{ $isLeadership ? 'bg-[#003366] text-white' : 'bg-white/95 text-[#003366] backdrop-blur-sm border' }}">{{ $isLeadership ? 'Direction' : 'Expertise' }}</span>
                            </div>
                        </div>
                        <div class="p-6 space-y-2 flex-1">
                            <h3 class="font-bold text-zinc-900">{{ $member['name'] }}</h3>
                            <p class="text-xs tracking-widest text-zinc-500 uppercase">{{ $member['role'] }}</p>
                            <p class="text-xs leading-relaxed text-zinc-600 line-clamp-3">Expertise SIBEA-CI — {{ $member['role'] }} — rigueur, suivi et conformité.</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CTA final -->
    <div class="mx-auto max-w-7xl px-4 pb-16 lg:px-8">
        <div class="rounded-2xl bg-zinc-900 p-8 lg:p-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <h3 class="text-xl font-black text-white">Parlons de votre projet</h3>
                <p class="mt-2 text-sm text-zinc-400">Devis sous 24h — BTP, VRD, lotissement, énergie.</p>
            </div>
            <a href="{{ route('front.contact') }}" class="rounded-full bg-white px-8 py-3 text-sm font-bold text-zinc-900 hover:bg-zinc-100">CONTACTER SIBEA-CI →</a>
        </div>
    </div>
</section>
