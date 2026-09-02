<?php

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Contact & Devis Technique — SIBEA-CI Laboratoire')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $hero = Cache::remember(
            'contact.hero',
            300,
            fn() => \App\Models\SiteSetting::get('contact.hero', [
                'title' => 'DEMANDE D\'ÉTUDE ET DEVIS TECHNIQUE',
                'body' => 'Génie civil, VRD, aménagement foncier et construction. Transmettez vos besoins : nos ingénieurs traitent votre dossier sous 24h ouvrées.',
                'badge' => 'POSTE DE COMMANDEMENT • BINGERVILLE ABATTA',
                'image' => null,
            ]),
        );

        return view('pages.front.contact', [
            'hero' => $hero,
        ]);
    }
}; ?>

<section class="bg-zinc-100/70 min-h-screen pb-16">
    {{-- Hero Page Contact --}}
    <x-page-hero-simple 
        :title="$hero['title'] ?: 'DEMANDE D\'ÉTUDE ET DEVIS TECHNIQUE'" 
        :subtitle="$hero['body'] ?: 'Génie civil, VRD, aménagement foncier et construction. Transmettez vos besoins : nos ingénieurs traitent votre dossier sous 24h ouvrées.'" 
        :badge="$hero['badge'] ?: 'POSTE DE COMMANDEMENT • BINGERVILLE ABATTA'" 
        :image="$hero['image'] ?? null" 
        :image-alt="$hero['title'] ?? 'Contact SIBEA-CI'" 
        :breadcrumb="[['label'=>'Contact & Devis','url'=>route('front.contact')]]" 
    />

    <!-- Processus de Traitement du Dossier -->
    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="font-mono text-[10px] font-bold text-amber-600 uppercase tracking-widest bg-amber-500/10 px-3 py-1 rounded border border-amber-500/20">
                INSTRUCTION DES DOSSIERS
            </span>
            <h2 class="mt-3 text-2xl font-black text-zinc-900 uppercase tracking-tight">
                Processus d'Étude en <span class="text-amber-600">4 Étapes</span>
            </h2>
            <p class="mt-2 text-xs sm:text-sm text-zinc-600">
                Une prise en charge structurée par notre bureau d'études pour garantir une cotation exacte et conforme aux normes VRD/BTP en Côte d'Ivoire.
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            <!-- Étape 1 -->
            <div class="relative rounded-2xl border border-zinc-300/80 bg-white p-5 shadow-sm hover:border-amber-500 transition-all">
                <div class="flex size-9 items-center justify-center rounded-xl bg-zinc-900 font-mono text-sm font-black text-amber-500 shadow">
                    01
                </div>
                <h3 class="mt-3 text-xs font-black text-zinc-900 uppercase">Saisie du Besoin</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">
                    Complétez le formulaire à étapes (type d'ouvrage, surface, localisation et contraintes terrain).
                </p>
                <div class="absolute -right-2 top-1/2 hidden size-4 rotate-45 border-r border-t border-zinc-300 bg-white md:block z-10"></div>
            </div>

            <!-- Étape 2 -->
            <div class="relative rounded-2xl border border-zinc-300/80 bg-white p-5 shadow-sm hover:border-amber-500 transition-all">
                <div class="flex size-9 items-center justify-center rounded-xl bg-zinc-900 font-mono text-sm font-black text-amber-500 shadow">
                    02
                </div>
                <h3 class="mt-3 text-xs font-black text-zinc-900 uppercase">Analyse Géotechnique</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">
                    Nos ingénieurs examinent la faisabilité technique, le plan cadastral et les métrés demandés.
                </p>
                <div class="absolute -right-2 top-1/2 hidden size-4 rotate-45 border-r border-t border-zinc-300 bg-white md:block z-10"></div>
            </div>

            <!-- Étape 3 -->
            <div class="relative rounded-2xl border border-zinc-300/80 bg-white p-5 shadow-sm hover:border-amber-500 transition-all">
                <div class="flex size-9 items-center justify-center rounded-xl bg-zinc-900 font-mono text-sm font-black text-amber-500 shadow">
                    03
                </div>
                <h3 class="mt-3 text-xs font-black text-zinc-900 uppercase">Devis Estimatif (24h)</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">
                    Transmission d'une proposition chiffrée détaillée avec le découpage des corps d'état sous 24h ouvrées.
                </p>
                <div class="absolute -right-2 top-1/2 hidden size-4 rotate-45 border-r border-t border-zinc-300 bg-white md:block z-10"></div>
            </div>

            <!-- Étape 4 -->
            <div class="rounded-2xl border border-zinc-300/80 bg-white p-5 shadow-sm hover:border-amber-500 transition-all">
                <div class="flex size-9 items-center justify-center rounded-xl bg-zinc-900 font-mono text-sm font-black text-amber-500 shadow">
                    04
                </div>
                <h3 class="mt-3 text-xs font-black text-zinc-900 uppercase">Reconnaissance & Ordre</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">
                    Planification de la visite terrain avec un conducteur de travaux pour validation de l'ordre de service.
                </p>
            </div>
        </div>
    </div>

    <!-- Section Formulaire & Fiche Technique Siège -->
    <div class="mx-auto max-w-7xl px-4 py-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-12">
            
            <!-- Informations Foncier & Localisation Siège (4 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <div class="rounded-2xl border border-zinc-300/80 bg-zinc-900 p-6 text-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                        <span class="font-mono text-[10px] font-bold text-amber-400 uppercase tracking-widest">
                            FICHE CADASTRE SIÈGE
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded bg-emerald-500/20 px-2 py-0.5 font-mono text-[10px] font-bold text-emerald-400 border border-emerald-500/30">
                            <span class="size-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            OUVERT
                        </span>
                    </div>

                    <div class="mt-4 space-y-2">
                        <h3 class="text-lg font-black uppercase text-white">SIBEA-CI — SARL</h3>
                        <p class="text-xs text-zinc-300 leading-relaxed">
                            Abidjan, Bingerville, Abatta (Lot 935, Îlot 86)<br>
                            <span class="text-zinc-400">Repère : Près Hôtel Blanc Cerf & carrefour Pantchô</span>
                        </p>
                    </div>

                    <!-- Métadonnées Administratives & Foncier -->
                    <div class="mt-4 rounded-xl bg-zinc-950 p-3.5 border border-zinc-800 font-mono text-[11px] space-y-1.5 text-zinc-400">
                        <div class="flex justify-between">
                            <span>IDU OFFICIEL:</span>
                            <span class="text-amber-400 font-bold">CI-2022-0016466 Q</span>
                        </div>
                        <div class="flex justify-between">
                            <span>COORDONNÉES GPS:</span>
                            <span class="text-zinc-200">5.3600 N, -4.0083 W</span>
                        </div>
                        <div class="flex justify-between">
                            <span>DIRECTION GÉNÉRALE:</span>
                            <span class="text-zinc-200">Ouattara B. Ziegnougo</span>
                        </div>
                    </div>

                    <!-- Carte Stylisée -->
                    <div class="mt-4 relative h-44 rounded-xl overflow-hidden border border-zinc-800 bg-zinc-950">
                        <iframe 
                            class="w-full h-full opacity-70 grayscale contrast-125"
                            src="https://maps.google.com/maps?q=5.3600,-4.0083&hl=fr&z=14&output=embed"
                            loading="lazy" 
                            aria-label="Localisation GPS Siège SIBEA-CI">
                        </iframe>
                        <div class="absolute bottom-2 left-2 right-2 rounded bg-zinc-900/90 backdrop-blur-sm p-1.5 border border-zinc-800 text-center font-mono text-[10px] text-amber-400 font-bold">
                            📍 REPERAGE GPS : BINGERVILLE ABATTA
                        </div>
                    </div>

                    <!-- Contacts Directs -->
                    <div class="mt-5 space-y-2 pt-2 border-t border-zinc-800 text-xs font-mono">
                        <div class="flex items-center justify-between text-zinc-300">
                            <span>Ligne Directe:</span>
                            <a href="tel:+2250700000000" class="font-bold text-white hover:text-amber-400">+225 07 00 00 00 00</a>
                        </div>
                        <div class="flex items-center justify-between text-zinc-300">
                            <span>Courriel Officiel:</span>
                            <a href="mailto:contact@sibea-ci.ci" class="font-bold text-white hover:text-amber-400">contact@sibea-ci.ci</a>
                        </div>
                    </div>

                    <!-- CTA WhatsApp Direct -->
                    <a href="https://wa.me/2250700000000" 
                       target="_blank" 
                       class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-xs font-black uppercase text-white hover:bg-emerald-500 transition shadow-lg"
                       aria-label="Échanger directement sur WhatsApp">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/>
                        </svg>
                        ÉCHANGER AVEC UN INGENIEUR SUR WHATSAPP
                    </a>
                </div>
            </div>

            <!-- Assistant / Formulaire à étapes Livewire (7 cols) -->
            <div class="lg:col-span-7">
                <div class="rounded-2xl border border-zinc-300/80 bg-white p-6 sm:p-8 shadow-sm">
                    <livewire:front.inquiry-wizard />
                </div>
            </div>

        </div>
    </div>
</section>
