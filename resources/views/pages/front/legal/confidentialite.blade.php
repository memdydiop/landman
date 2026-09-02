<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Politique de confidentialité — SIBEA-CI')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $global = \App\Models\SiteSetting::get('global', []);
        return view('pages.front.legal.confidentialite', ['global' => $global]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        title="Politique de <span class=&quot;font-black&quot;>confidentialité</span>"
        subtitle="Comment SIBEA-CI collecte, utilise et protège vos données — transparence totale."
        badge="DONNÉES — RGPD OHADA"
        :breadcrumb="[['label'=>'Confidentialité','url'=>route('front.legal.confidentialite')]]"
    />
    <div class="mx-auto max-w-3xl px-4 lg:px-8 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium">
            @foreach([['Responsable','responsable'],['Données collectées','donnees'],['Finalités','finalites'],['Durées','durees'],['Destinataires','destinataires'],['Vos droits','droits'],['Sécurité','securite'],['Cookies','cookies']] as [$label,$anchor])
                <a href="#{{ $anchor }}" class="hover:text-[#003366] flex items-center gap-1.5 transition"><span class="w-1.5 h-1.5 bg-[#87CEEB]"></span> {{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="mx-auto max-w-3xl px-4 lg:px-8 pb-16 prose prose-zinc prose-sm">
        @php
            $company = $global['company_name'] ?? 'SIBEA-CI';
            $address = $global['company_address'] ?? 'Abidjan, Bingerville, Abatta (Lot 935, Îlot 86)';
            $email = $global['company_email'] ?? 'contact@sibea-ci.ci';
            $phone = $global['company_phone'] ?? '+225 07 00 00 00 00';
        @endphp
        <p class="text-xs text-zinc-500">Dernière mise à jour : {{ date('d/m/Y') }} — Conforme loi ivoirienne n°2013-450 et principe RGPD.</p>

        <section id="responsable" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">01.</span> Responsable de traitement</h2>
            <p class="text-sm leading-relaxed text-zinc-600"><strong>{{ $company }}</strong>, {{ $address }} — RCCM {{ $global['company_siret'] ?? 'CI-2022-0016466 Q' }} — Contact DPO : <a href="mailto:{{ $email }}">{{ $email }}</a> — {{ $phone }}. Délégué : Gérant Ouattara Bassoma Ziegnougo.</p>
        </section>

        <section id="donnees" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">02.</span> Données collectées</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Via `InquiryWizard` (3 étapes), `newsletter-form` et `admin/inquiries` : identité, contact (email, téléphone, WhatsApp), projet (`DEVIS_BTP/ACHAT_LOT`, `program/plot`, `surface/budget`), message, consentement `rgpd`, métadonnées `ip`.</p>
        </section>

        <section id="finalites" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">03.</span> Finalités & bases légales</h2>
            <ul class="text-sm leading-relaxed text-zinc-600 list-disc pl-5 space-y-1">
                <li><strong>Devis & réservation</strong> : exécution précontrat — base contrat.</li>
                <li><strong>Newsletter</strong> : envoi publications — base consentement.</li>
                <li><strong>Analytics</strong> `PageVisit` 30j — base intérêt légitime.</li>
                <li><strong>Obligation légale</strong> : ACD, facturation, décennale 10 ans.</li>
            </ul>
        </section>

        <section id="durees" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">04.</span> Durées de conservation</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Prospects non convertis : 3 ans. Clients : 10 ans (décennale) + 5 ans compta. Newsletter : jusqu'à désinscription. Logs 30 jours.</p>
        </section>

        <section id="destinataires" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">05.</span> Destinataires & sous-traitants</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Équipe `Super Admin/Éditeur/Commercial` `Spatie`. Sous-traitants : hébergeur, mailer, WhatsApp. Aucune vente à tiers.</p>
        </section>

        <section id="droits" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">06.</span> Vos droits & Contact DPO</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Accès, rectification, effacement, opposition, portabilité, retrait consentement. Exercer à {{ $email }} — Réponse 30 jours. Réclamation ARTCI / CNIL.</p>
        </section>

        <section id="securite" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">07.</span> Sécurité</h2>
            <p class="text-sm leading-relaxed text-zinc-600">TLS, hash, `permission:cms.manage`, `throttle:5,1` export, `Storage private` plans PDF, sauvegardes chiffrées.</p>
        </section>

        <section id="cookies" class="mb-10">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">08.</span> Cookies & mise à jour</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Essentiels : `XSRF-TOKEN`, `landman_session`. Analytics : `PageVisit` anonymisé. Mise à jour affichée ici et via `admin/cms History`.</p>
        </section>
    </div>
</section>
