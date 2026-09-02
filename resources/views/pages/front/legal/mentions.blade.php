<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Mentions légales — SIBEA-CI')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $global = \App\Models\SiteSetting::get('global', []);
        $legal = \App\Models\SiteSetting::get('legal', ['mentions' => '', 'cgv' => '', 'confidentialite' => '', 'securite' => '']);
        return view('pages.front.legal.mentions', ['global' => $global, 'legal' => $legal]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        title="Mentions <span class="font-black">légales</span>"
        subtitle="Informations légales relatives à l'éditeur, l'hébergement et l'utilisation du site SIBEA-CI."
        badge="LÉGAL — TRANSPARENCE"
        :breadcrumb="[['label'=>'Mentions légales','url'=>route('front.legal.mentions')]]"
    />
    <div class="mx-auto max-w-3xl px-4 lg:px-8 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium">
            @foreach([['Éditeur du site','editeur'],['Hébergement','hebergement'],['Activité','activite'],['Propriété intellectuelle','propriete'],['Responsabilité','responsabilite'],['Droit applicable','droit']] as [$label,$anchor])
                <a href="#{{ $anchor }}" class="hover:text-[#003366] flex items-center gap-1.5 transition"><span class="w-1.5 h-1.5 bg-[#87CEEB]"></span> {{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="mx-auto max-w-3xl px-4 lg:px-8 pb-16 prose prose-zinc prose-sm max-w-none">
        @php
            $company = $global['company_name'] ?? 'SIBEA-CI';
            $siret = $global['company_siret'] ?? 'CI-2022-0016466 Q';
            $capital = $global['company_capital'] ?? '100 000 000 FCFA';
            $tva = $global['company_tva'] ?? 'CI00123456789';
            $address = $global['company_address'] ?? 'Abidjan, Bingerville, Abatta (Lot 935, Îlot 86)';
            $phone = $global['company_phone'] ?? '+225 07 00 00 00 00';
            $email = $global['company_email'] ?? 'contact@sibea-ci.ci';
        @endphp
        <p class="text-xs text-zinc-500">Dernière mise à jour : {{ date('d/m/Y') }} — Applicable au site {{ request()->getHost() }}</p>

        @if(!empty($legal['mentions']))
            {!! $legal['mentions'] !!}
        @else
            <section id="editeur" class="mb-10 pb-8 border-b border-zinc-100">
                <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">01.</span> Éditeur du site</h2>
                <div class="space-y-3 text-sm leading-relaxed text-zinc-600">
                    <p><strong>{{ $company }}</strong> — SARL au capital de {{ $capital }}<br>Immatriculée au RCCM sous le numéro {{ $siret }} — TVA : {{ $tva }}<br>Siège social : {{ $address }} — Près Hôtel Blanc Cerf, carrefour Pantchô, Côte d'Ivoire<br>Directeur de la publication : Ouattara Bassoma Ziegnougo, Gérant<br>Contact : <a href="mailto:{{ $email }}">{{ $email }}</a> — {{ $phone }}</p>
                </div>
            </section>

            <section id="hebergement" class="mb-10 pb-8 border-b border-zinc-100">
                <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">02.</span> Hébergement</h2>
                <p class="text-sm leading-relaxed text-zinc-600">Site hébergé par prestataire certifié ISO 27001 (OVH / AWS / LWS selon environnement) — données stockées en Côte d'Ivoire et/ou UE. Serveurs sécurisés, sauvegardes quotidiennes chiffrées, journalisation d'accès. Hébergeur : à compléter selon contrat (adresse, RCS, téléphone) — sur demande à {{ $email }}.</p>
            </section>

            <section id="activite" class="mb-10 pb-8 border-b border-zinc-100">
                <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">03.</span> Activité</h2>
                <p class="text-sm leading-relaxed text-zinc-600">SIBEA-CI exerce : BTP & Génie civil, Aménagement urbain (VRD), Lotissement & Foncier viabilisé, Électricité, Pétrole & Énergie, Agro-industrie. Agréments ministériels VRD/BTP Côte d'Ivoire, assurance Responsabilité Civile Professionnelle et Garantie Décennale.</p>
            </section>

            <section id="propriete" class="mb-10 pb-8 border-b border-zinc-100">
                <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">04.</span> Propriété intellectuelle</h2>
                <p class="text-sm leading-relaxed text-zinc-600">L'ensemble du site (textes, images, plans, logos, base programmes/lots) est protégé par le droit d'auteur OHADA et le Code de la propriété intellectuelle ivoirien. Toute reproduction sans autorisation écrite est interdite. Marques et logos partenaires restent propriété de leurs titulaires.</p>
            </section>

            <section id="responsabilite" class="mb-10 pb-8 border-b border-zinc-100">
                <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">05.</span> Responsabilité</h2>
                <p class="text-sm leading-relaxed text-zinc-600">Informations (disponibilité lots, prix, surfaces, visuels) données à titre indicatif et mises à jour via back-office `admin/programs` et `admin/projects`. SIBEA-CI ne saurait être tenue responsable d'erreurs typographiques ou d'indisponibilité temporaire. Confirmation par devis signé et ACD requise.</p>
            </section>

            <section id="droit" class="mb-10">
                <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">06.</span> Droit applicable & contact</h2>
                <p class="text-sm leading-relaxed text-zinc-600">Droit ivoirien — OHADA. Litiges soumis aux tribunaux compétents d'Abidjan, après tentative amiable à {{ $address }}. Contact légal : {{ $email }} — Objet [Légal] — Réponse sous 15 jours ouvrés.</p>
            </section>
        @endif
    </div>
</section>