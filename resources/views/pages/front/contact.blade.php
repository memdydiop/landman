<?php

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Contact — SIBEA-CI Laboratoire')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $hero = Cache::remember(
            'contact.hero',
            300,
            fn() => \App\Models\SiteSetting::get('contact.hero', [
                'title' => '',
                'body' => '',
                'badge' => '',
                'image' => null,
            ]),
        );

        return view('pages.front.contact', [
            'hero' => $hero,
        ]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple :title="$hero['title'] ?: 'Contact &amp; <span class=&quot;font-black&quot;>Devis</span>'" :subtitle="$hero['body'] ?: 'Formulaire à étapes — BTP vs Foncier — validation Livewire, réponse sous 24h. Siège : Abidjan Bingerville Abatta Lot 935 Îlot 86.' " :badge="$hero['badge'] ?: 'LABORATOIRE — CONTACT ÉTUDE'" :image="$hero['image'] ?? null" :image-alt="$hero['title'] ?? 'Contact SIBEA-CI'" :breadcrumb="[['label'=>'Contact','url'=>route('front.contact')]]" />

    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <div class="text-center">
            <div class="text-xs tracking-[0.3em] text-zinc-500">PROCESSUS — 4 ÉTAPES</div>
            <h2 class="mt-2 text-2xl font-black tracking-tight">Obtenez Votre Devis <span
                    class="font-light">Personnalisé</span></h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-zinc-600">Notre équipe d'experts analysera
                votre projet et vous fournira un devis détaillé et transparent dans les plus brefs délais.</p>
            <div class="mx-auto mt-2 h-1 w-12 bg-[#003366]"></div>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-4">
            <div class="relative rounded-2xl border border-zinc-200 bg-white p-6">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-[#003366] text-sm font-black text-white">
                    1</div>
                <h3 class="mt-3 text-sm font-bold">Remplissez le formulaire</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">Décrivez votre projet en détail
                    pour nous permettre de mieux comprendre vos besoins.</p>
                <div
                    class="absolute -right-2 top-1/2 hidden size-4 rotate-45 border-r border-t border-zinc-200 bg-white md:block">
                </div>
            </div>
            <div class="relative rounded-2xl border border-zinc-200 bg-white p-6">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-[#003366] text-sm font-black text-white">
                    2</div>
                <h3 class="mt-3 text-sm font-bold">Analyse par nos experts</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">Nos ingénieurs étudient votre
                    projet et établissent un devis personnalisé.</p>
                <div
                    class="absolute -right-2 top-1/2 hidden size-4 rotate-45 border-r border-t border-zinc-200 bg-white md:block">
                </div>
            </div>
            <div class="relative rounded-2xl border border-zinc-200 bg-white p-6">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-[#003366] text-sm font-black text-white">
                    3</div>
                <h3 class="mt-3 text-sm font-bold">Réception du devis</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">Vous recevez un devis détaillé
                    par email dans un délai de 48 à 72 heures.</p>
                <div
                    class="absolute -right-2 top-1/2 hidden size-4 rotate-45 border-r border-t border-zinc-200 bg-white md:block">
                </div>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div
                    class="flex size-10 items-center justify-center rounded-full bg-[#003366] text-sm font-black text-white">
                    4</div>
                <h3 class="mt-3 text-sm font-bold">Échange avec notre équipe</h3>
                <p class="mt-1 text-xs leading-relaxed text-zinc-600">Nous restons à disposition pour
                    discuter et ajuster le devis selon vos besoins.</p>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-10 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <div class="border-t-2 border-[#003366] bg-zinc-50 p-6">
                    <div class="text-xs tracking-widest text-zinc-500">SIÈGE</div>
                    <div class="mt-2 font-bold">SIBEA-CI — SARL 2022</div>
                    <p class="mt-1 text-sm text-zinc-600">Abidjan, Bingerville, Abatta (Lot 935, Îlot
                        86)<br>Près Hôtel Blanc Cerf et carrefour Pantchô<br>IDU CI-2022-0016466 Q<br>Dir. Ouattara
                        Bassoma Ziegnougo</p>
                    <div
                        class="mt-4 h-40 rounded-xl bg-zinc-100 flex items-center justify-center text-xs tracking-widest text-zinc-500">
                        CARTE — 5.3600, -4.0083</div>
                    <div class="mt-4 text-sm">
                        <div>+225 07 00 00 00 00</div>
                        <div>contact@sibea-ci.ci</div>
                    </div>
                    <a href="https://wa.me/2250700000000" target="_blank" class="mt-4 inline-flex items-center gap-1.5 border border-zinc-900 px-4 py-2 text-xs font-bold tracking-widest hover:bg-zinc-900 hover:text-white" aria-label="WhatsApp Direct"><svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg> DIRECT</a>
                </div>
            </div>

            <div class="lg:col-span-3">
                <livewire:front.inquiry-wizard />
            </div>
        </div>
    </div>
</section>
