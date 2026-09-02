<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Conditions Générales de Vente — SIBEA-CI')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $global = \App\Models\SiteSetting::get('global', []);
        return view('pages.front.legal.cgv', ['global' => $global]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        title="Conditions Générales <span class=&quot;font-black&quot;>de Vente</span>"
        subtitle="CGV applicables aux prestations BTP/VRD et à la réservation de lots viabilisés SIBEA-CI."
        badge="CGV — CONTRAT"
        :breadcrumb="[['label'=>'CGV','url'=>route('front.legal.cgv')]]"
    />
    <div class="mx-auto max-w-3xl px-4 lg:px-8 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium">
            @foreach([['Objet','objet'],['Prix','prix'],['Commande','commande'],['Paiement','paiement'],['Délais & livraison','delais'],['Garanties','garanties'],['Rétractation','retractation'],['Litiges','litiges']] as [$label,$anchor])
                <a href="#{{ $anchor }}" class="hover:text-[#003366] flex items-center gap-1.5 transition"><span class="w-1.5 h-1.5 bg-[#87CEEB]"></span> {{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="mx-auto max-w-3xl px-4 lg:px-8 pb-16 prose prose-zinc prose-sm ">
        @php
            $company = $global['company_name'] ?? 'SIBEA-CI';
            $address = $global['company_address'] ?? 'Abidjan, Bingerville, Abatta (Lot 935, Îlot 86)';
            $siret = $global['company_siret'] ?? 'CI-2022-0016466 Q';
            $email = $global['company_email'] ?? 'contact@sibea-ci.ci';
        @endphp
        <p class="text-xs text-zinc-500">Dernière mise à jour : {{ date('d/m/Y') }}</p>

        <section id="objet" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">01.</span> Objet & champ d'application</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Les présentes CGV régissent : (a) les marchés BTP / VRD / rénovation et (b) la réservation/achat de lots issus des programmes `Lot 935 Îlot 86`. Toute commande implique acceptation sans réserve. Conditions particulières du devis priment.</p>
        </section>

        <section id="prix" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">02.</span> Prix</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Prix en FCFA, HT/TTC précisés. Lots : prix au m², frais ACD et notaire non inclus sauf mention. BTP : prix ferme révisable selon index BT01 si délai &gt; 3 mois. Validité devis : 30 jours.</p>
        </section>

        <section id="commande" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">03.</span> Commande & formation du contrat</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Devis détaillé → signature + acompte (30% BTP, 20% lot) → contrat. Réservation lot : statut `Option` 7 jours, puis `Réservé` après acompte, `Vendu` après solde et acte. Gestion via `admin/programs` `plots`.</p>
        </section>

        <section id="paiement" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">04.</span> Paiement</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Virements NSIA/SIB/BOA/SGCI/BACI ou mobile money. Échéancier BTP : 30% commande, 40% mi-chantier (PV), 30% réception. Lots : échelonnement 3 à 12 mois, pénalités 1%/mois. Factures émises à {{ $company }}, {{ $address }} — RCCM {{ $siret }}.</p>
        </section>

        <section id="delais" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">05.</span> Délais & livraison</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Délais indicatifs hors force majeure. Livraison BTP : PV de réception + levée réserves 30 jours. Lot : remise ACD provisoire puis définitif après purge coutumière et VRD. Tolérance surface ±2%.</p>
        </section>

        <section id="garanties" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">06.</span> Garanties & assurances</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Parfait achèvement 1 an, biennale 2 ans, décennale 10 ans (RC Pro). ACD sécurisé : vérification chaîne de propriété, purge, enquête domaniale. Documents : plan de masse PDF, attestation villageoise → ACD.</p>
        </section>

        <section id="retractation" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">07.</span> Rétractation & résiliation</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Rétractation 7 jours hors lot borné. Résiliation pour faute : mise en demeure 15 jours, retenue 20% + frais. Acompte lot non remboursable après purge si désistement.</p>
        </section>

        <section id="litiges" class="mb-10">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">08.</span> Litiges & contact</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Réclamation à {{ $email }} — Réponse 15 jours. Médiation CMA Abidjan, puis tribunaux d'Abidjan. Droit OHADA / ivoirien. Objet [CGV].</p>
        </section>
    </div>
</section>
