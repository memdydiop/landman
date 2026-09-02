<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('À Propos — SIBEA-CI Laboratoire & VRD')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $hero = Cache::remember('about.hero', 300, fn () => \App\Models\SiteSetting::get('about.hero', [
            'title' => 'LABORATOIRE URBAIN & INGENIERIE BTP',
            'body' => 'Génie civil, aménagement foncier, VRD et construction clé en main en Côte d\'Ivoire. Sécurisation juridique ACD et traçabilité technique.',
            'badge' => 'SIBEA-CI • EXPERTISE & AMÉNAGEMENT URBAIN',
            'image' => null,
        ]));

        $progress = \App\Models\SiteSetting::get('about.progress', [
            ['label' => 'BTP & GÉNIE CIVIL', 'pct' => 95],
            ['label' => 'VOIRIES & VRD', 'pct' => 92],
            ['label' => 'GÉODÉSIE & AMÉNAGEMENT FONCIER', 'pct' => 88],
            ['label' => 'OUVRAGES & RÉSIDENCES', 'pct' => 90],
        ]);

        $engagement = \App\Models\SiteSetting::get('about.engagement', [
            'eyebrow' => 'NOTRE ENGAGEMENT DE CHANTIER',
            'title' => 'SIBEA-CI — Ingénierie & Laboratoire Foncier',
            'subtitle' => 'Un contrôle rigoureux de l\'audit foncier à la livraison clé en main',
            'desc1' => 'Nous sécurisons chaque étape de vos projets immobiliers et de BTP en appliquant des normes de construction strictes et un suivi géodésique de précision.',
            'desc2_title' => 'Maîtrise d\'Ouvrage Déléguée & Supervision',
            'desc2' => 'Particuliers, entreprises et collectivités : nous garantissons l\'exécution dans le respect des coûts, du cahier des charges et des délais.',
            'items' => [
                'Construction de bâtiments résidentiels, tertiaires et industriels.',
                'Aménagement foncier, viabilisation complète (VRD) et voiries.',
                'Rénovation lourde et réhabilitation d\'ouvrages d\'art.',
                'Audit foncier préalable et sécurisation des arrêtés de concession définitive (ACD).'
            ],
            'floating' => ['number' => '+100', 'label' => 'HECTARES AMÉNAGÉS', 'desc' => 'Avec traçabilité cadastre & ACD.'],
        ]);

        $valeurs = \App\Models\SiteSetting::get('about.valeurs', [
            'title' => 'NOS VALEURS FONDAMENTALES',
            'subtitle' => 'Des standards techniques et déontologiques stricts pour sécuriser vos investissements.',
            'items' => [
                ['title' => 'Traçabilité & Rigueur', 'desc' => 'Audit systématique des sols, vérification administrative et suivi en temps réel des chantiers.', 'icon' => '📐'],
                ['title' => 'Normes BTP & Durabilité', 'desc' => 'Matériaux certifiés, respect des normes parasismiques et études géotechniques approfondies.', 'icon' => '🏗️'],
                ['title' => 'Garantie & Conformité', 'desc' => 'Livraison dans les délais impartis avec délivrance des certificats de conformité technique.', 'icon' => '🛡️'],
            ],
        ]);

        $pourquoi = \App\Models\SiteSetting::get('about.pourquoi', [
            'eyebrow' => 'POURQUOI CHOISIR SIBEA-CI',
            'title' => 'Une chaîne d\'expertises techniques intégrées',
            'subtitle' => 'De la topographie à la remise des clés, nous centralisons tous les corps d\'état.',
            'items' => [
                ['title' => 'Études géotechniques & VRD', 'desc' => 'Analyse des sols et viabilisation complète avant toute construction.'],
                ['title' => 'Accompagnement juridique & ACD', 'desc' => 'Purge des droits coutumiers et sécurisation des titres fonciers.'],
                ['title' => 'Supervision rigoureuse', 'desc' => 'Conducteurs de travaux dédiés et reporting d\'avancement systématique.'],
                ['title' => 'Maîtrise budgétaire', 'desc' => 'Devis fermes sans réévaluation imprévue en cours de chantier.'],
            ],
            'cta_title' => 'Bâtissons des Infrastructures Durables',
            'cta_desc' => 'Nos équipes d\'ingénieurs et de techniciens qualifiés déploient les meilleures solutions pour vos projets en Côte d\'Ivoire.',
        ]);

        $team = \App\Models\SiteSetting::get('home.team', [
            ['name' => 'Ouattara Bassoma Ziegnougo', 'role' => 'Gérant — SARL', 'avatar' => null],
            ['name' => 'Kouamé Yao', 'role' => 'Ingénieur Civil VRD', 'avatar' => null],
            ['name' => 'Awa Koné', 'role' => 'Conductrice Travaux', 'avatar' => null],
            ['name' => 'Diabaté Moussa', 'role' => 'Électricien Chef', 'avatar' => null],
        ]);

        return view('pages.front.about', [
            'hero' => $hero,
            'progress' => $progress,
            'engagement' => $engagement,
            'valeurs' => $valeurs,
            'pourquoi' => $pourquoi,
            'team' => $team,
        ]);
    }
}; ?>

<section class="bg-zinc-100/70 min-h-screen pb-12">
    {{-- Hero Page À Propos --}}
    <x-page-hero-simple
        :title="($hero['title'] ?? '') ?: 'LABORATOIRE URBAIN & INGENIERIE BTP'"
        :subtitle="($hero['body'] ?? $hero['subtitle'] ?? '') ?: 'Génie civil, aménagement foncier, VRD et construction clé en main en Côte d\'Ivoire.'"
        :badge="($hero['badge'] ?? '') ?: 'SIBEA-CI • EXPERTISE & AMÉNAGEMENT URBAIN'"
        :image="$hero['image'] ?? null"
        :image-alt="($hero['title'] ?? 'À propos SIBEA-CI')"
        :breadcrumb="[['label'=>'À propos','url'=>route('front.about')]]"
    />

    <!-- Section Engagement & Chiffres Clés -->
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Visuels Immersifs Terrain -->
            <div class="lg:col-span-6 relative">
                <div class="relative overflow-hidden rounded-2xl border border-zinc-300/80 bg-zinc-900 shadow-md">
                    @if(!empty($hero['image']) && Storage::disk('public')->exists($hero['image']))
                        <img src="{{ Storage::disk('public')->url($hero['image']) }}" alt="SIBEA-CI chantier" class="w-full h-[420px] object-cover opacity-85" loading="lazy" />
                    @else
                        <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80" alt="SIBEA-CI chantier" class="w-full h-[420px] object-cover opacity-85" loading="lazy" />
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-transparent to-transparent"></div>
                </div>

                <!-- Deuxième image superposée (Style Plan/VRD) -->
                <div class="hidden md:block absolute -bottom-6 -right-6 w-52 h-52 overflow-hidden rounded-2xl border-4 border-white shadow-xl bg-zinc-900">
                    <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=400&q=80" alt="Supervision BTP" class="w-full h-full object-cover" loading="lazy" />
                </div>

                <!-- Floating Card Technique -->
                <div class="absolute -bottom-6 sm:-bottom-8 left-4 sm:left-6 bg-zinc-900 text-white p-5 rounded-2xl border border-amber-500/50 shadow-2xl max-w-[280px]">
                    <div class="font-mono text-3xl font-black text-amber-500">{{ $engagement['floating']['number'] ?? '+100' }}</div>
                    <div class="font-mono text-[10px] font-bold text-amber-400 mt-1 uppercase tracking-wider">{{ $engagement['floating']['label'] ?? 'HECTARES AMÉNAGÉS' }}</div>
                    <p class="text-[11px] text-zinc-300 mt-1.5 leading-tight">{{ $engagement['floating']['desc'] ?? 'Traçabilité totale, conformité cadastre & zéro litige foncier.' }}</p>
                </div>
            </div>

            <!-- Contenu Engagement -->
            <div class="lg:col-span-6 space-y-6">
                <div>
                    <span class="rounded bg-amber-500/20 px-2.5 py-1 font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest border border-amber-500/30">
                        {{ $engagement['eyebrow'] ?? 'NOTRE ENGAGEMENT DE CHANTIER' }}
                    </span>
                    <h2 class="mt-3 text-2xl sm:text-3xl font-black text-zinc-900 uppercase tracking-tight leading-tight">
                        {{ $engagement['title'] ?? 'SIBEA-CI — Ingénierie & Laboratoire Foncier' }}
                    </h2>
                    <p class="mt-2 text-sm font-bold text-amber-600 uppercase">
                        {{ $engagement['subtitle'] ?? 'Un contrôle rigoureux de l\'audit foncier à la livraison' }}
                    </p>
                </div>

                <div class="space-y-3 text-xs sm:text-sm leading-relaxed text-zinc-700">
                    <p>{{ $engagement['desc1'] ?? '' }}</p>
                    <h4 class="font-black uppercase text-zinc-900 text-xs">{{ $engagement['desc2_title'] ?? 'Maîtrise d\'Ouvrage Déléguée & Supervision' }}</h4>
                    <p>{{ $engagement['desc2'] ?? '' }}</p>
                </div>

                <!-- Liste des Engagements -->
                <div class="rounded-2xl border border-zinc-300/80 bg-white p-4 shadow-sm">
                    <ul class="space-y-2.5">
                        @foreach(($engagement['items'] ?? []) as $li)
                            <li class="flex items-start gap-3 text-xs font-semibold text-zinc-800">
                                <span class="mt-0.5 flex size-4 items-center justify-center rounded bg-amber-500 text-zinc-950 font-black text-[10px] shrink-0">✓</span>
                                <span>{{ $li }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Valeurs Fondamentales (Style Cartes Industrielles) -->
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="rounded-2xl border border-zinc-300/80 bg-white p-8 shadow-sm">
            <div class="text-center max-w-2xl mx-auto mb-8 space-y-2">
                <span class="font-mono text-[11px] font-bold text-amber-600 uppercase tracking-widest">EXCELLENCE D'EXÉCUTION</span>
                <h2 class="text-2xl font-black text-zinc-900 uppercase tracking-tight">{{ $valeurs['title'] ?? 'NOS VALEURS FONDAMENTALES' }}</h2>
                <p class="text-xs text-zinc-500">{{ $valeurs['subtitle'] ?? 'Des standards techniques et déontologiques stricts pour sécuriser vos investissements.' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(($valeurs['items'] ?? []) as $v)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/80 p-6 shadow-sm hover:border-amber-500 transition-all flex flex-col">
                        <div class="w-10 h-10 rounded-lg bg-zinc-900 flex items-center justify-center text-amber-500 text-xl mb-4 shadow">
                            {{ $v['icon'] }}
                        </div>
                        <h3 class="text-sm font-black text-zinc-900 uppercase tracking-wide">{{ $v['title'] }}</h3>
                        <p class="mt-2 text-xs leading-relaxed text-zinc-600">{{ $v['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Pourquoi Nous Choisir & Barres de Progression -->
    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-2 items-start">
            
            <!-- Liste d'Avantages -->
            <div class="space-y-4">
                <div class="space-y-1">
                    <span class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest">{{ $pourquoi['eyebrow'] ?? 'POURQUOI CHOISIR SIBEA-CI' }}</span>
                    <h2 class="text-2xl font-black text-zinc-900 uppercase tracking-tight">{{ $pourquoi['title'] ?? 'Une chaîne d\'expertises intégrées' }}</h2>
                    <p class="text-xs text-zinc-500">{{ $pourquoi['subtitle'] ?? 'De la topographie à la remise des clés, nous centralisons tous les corps d\'état.' }}</p>
                </div>

                <div class="space-y-3">
                    @foreach(($pourquoi['items'] ?? []) as $item)
                        <div class="flex gap-3.5 p-4 rounded-xl border border-zinc-200 bg-white shadow-sm">
                            <span class="flex size-5 items-center justify-center rounded bg-zinc-900 text-amber-500 text-xs font-black shrink-0 mt-0.5">✓</span>
                            <div>
                                <div class="text-xs font-black text-zinc-900 uppercase">{{ $item['title'] }}</div>
                                <div class="mt-1 text-xs leading-relaxed text-zinc-600">{{ $item['desc'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bloc CTA & Niveaux de Maitrise Technique -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-zinc-300/80 bg-zinc-900 p-6 text-white shadow-md">
                    <h4 class="font-black text-base uppercase text-amber-400">{{ $pourquoi['cta_title'] ?? 'Bâtissons des Infrastructures Durables' }}</h4>
                    <p class="mt-2 text-xs leading-relaxed text-zinc-300">{{ $pourquoi['cta_desc'] ?? 'Nos équipes d\'ingénieurs et de techniciens qualifiés déploient les meilleures solutions pour vos projets en Côte d\'Ivoire.' }}</p>
                    <a href="{{ route('front.contact') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-black text-zinc-950 hover:bg-amber-400 transition uppercase">
                        📞 PARLER À UN INGENIEUR →
                    </a>
                </div>

                <!-- Indicateurs de Compétences Techniques -->
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm space-y-4">
                    <h4 class="font-black text-xs uppercase text-zinc-900 border-b border-zinc-100 pb-2">Taux d'Exécution & Domaines d'Expertise</h4>
                    @foreach($progress as $p)
                        <div class="font-mono text-xs">
                            <div class="flex justify-between font-bold">
                                <span class="text-zinc-800">{{ $p['label'] }}</span>
                                <span class="text-amber-600">{{ $p['pct'] }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-zinc-100 overflow-hidden border border-zinc-200/60">
                                <div class="h-full rounded-full bg-amber-500 transition-all duration-500" style="width: {{ $p['pct'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Équipe & Direction Technique — CMS -->
    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <div class="rounded-2xl border border-zinc-300/80 bg-white p-8 shadow-sm">
            <div class="max-w-3xl mx-auto text-center mb-8 space-y-2">
                <span class="rounded bg-amber-500/20 px-3 py-1 font-mono text-[10px] font-bold text-amber-600 uppercase tracking-wider border border-amber-500/30">
                    {{ $equipe['badge'] ?? 'GOUVERNANCE & DIRECTION' }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 uppercase tracking-tight">{{ $equipe['title'] ?? 'Une chaîne d\'expertises qualifiée' }}</h2>
                <p class="text-xs text-zinc-500">{{ $equipe['subtitle'] ?? 'Mobilisés autour de chaque opération pour maîtriser l\'exécution et sécuriser les investissements.' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($team as $member)
                    @php
                        $avatar = $member['avatar'] ?? null;
                        $hasAvatar = !empty($avatar) && Storage::disk('public')->exists($avatar);
                        $isLeadership = $loop->first;
                    @endphp
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 overflow-hidden shadow-sm hover:border-amber-500 transition-all flex flex-col group">
                        <div class="h-60 w-full relative overflow-hidden bg-zinc-900 flex items-center justify-center">
                            @if($hasAvatar)
                                <img src="{{ Storage::disk('public')->url($avatar) }}" alt="{{ $member['name'] }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-zinc-900">
                                    <div class="w-16 h-16 rounded-xl border border-amber-500/40 bg-zinc-800 flex items-center justify-center text-amber-500 font-mono font-black text-xl mb-2">
                                        {{ strtoupper(substr($member['name'], 0, 2)) }}
                                    </div>
                                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $member['name'] }}</span>
                                </div>
                            @endif
                            <div class="absolute top-3 left-3">
                                <span class="rounded font-mono text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 {{ $isLeadership ? 'bg-amber-500 text-zinc-950' : 'bg-zinc-800/90 text-white backdrop-blur-sm' }}">
                                    {{ $isLeadership ? 'DIRECTION' : 'EXPERTISE' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-5 space-y-1.5 flex-1 bg-white">
                            <h3 class="font-black text-zinc-900 text-sm uppercase leading-snug">{{ $member['name'] }}</h3>
                            <p class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-wider">{{ $member['role'] }}</p>
                            <p class="text-[11px] leading-relaxed text-zinc-500 line-clamp-2">Encadrement technique SIBEA-CI, suivi de chantier et conformité BTP.</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CTA Final Unifié — CMS -->
    <div class="mx-auto max-w-7xl px-4 pt-4 lg:px-8">
        <div class="rounded-2xl bg-zinc-900 p-8 lg:p-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 border border-amber-500/30 shadow-xl">
            <div>
                <span class="font-mono text-[10px] font-bold text-amber-400 uppercase tracking-widest">POSTE DE COMMANDEMENT</span>
                <h3 class="text-xl sm:text-2xl font-black text-white uppercase mt-1">{{ $cta['title'] ?? 'PARLONS DE VOTRE PROJET OU CHANTIER' }}</h3>
                <p class="mt-1 text-xs text-zinc-400">{{ $cta['subtitle'] ?? 'Étude de faisabilité, devis BTP / VRD et réservation foncière sous 24h.' }}</p>
            </div>
            <a href="{{ $cta['button_url'] ?? route('front.contact') }}" 
               class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-6 py-3.5 text-xs font-black tracking-wider text-zinc-950 hover:bg-amber-400 transition uppercase shrink-0">
                {{ $cta['button_label'] ?? 'DEMANDER UNE ÉTUDE TECHNIQUE →' }}
            </a>
        </div>
    </div>
</section>
