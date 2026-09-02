<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Sécurité & protection des données — SIBEA-CI')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $global = \App\Models\SiteSetting::get('global', []);
        return view('pages.front.legal.securite', ['global' => $global]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        title="Sécurité &amp; <span class=&quot;font-black&quot;>protection</span>"
        subtitle="Engagements techniques SIBEA-CI : chiffrement, contrôle d'accès, sauvegardes et traçabilité."
        badge="SÉCURITÉ — ENGAGEMENTS"
        :breadcrumb="[['label'=>'Sécurité','url'=>route('front.legal.securite')]]"
    />
    <div class="mx-auto max-w-3xl px-4 lg:px-8 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium">
            @foreach([['Chiffrement & TLS','chiffrement'],['Contrôle d\'accès','acces'],['Sauvegardes','sauvegardes'],['Traçabilité','tracabilite']] as [$label,$anchor])
                <a href="#{{ $anchor }}" class="hover:text-[#003366] flex items-center gap-1.5 transition"><span class="w-1.5 h-1.5 bg-[#87CEEB]"></span> {{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="mx-auto max-w-3xl px-4 lg:px-8 pb-16 prose prose-zinc prose-sm ">
        @php
            $company = $global['company_name'] ?? 'SIBEA-CI';
            $email = $global['company_email'] ?? 'contact@sibea-ci.ci';
        @endphp
        <p class="text-xs text-zinc-500">Dernière mise à jour : {{ date('d/m/Y') }}</p>

        <section id="chiffrement" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">01.</span> Chiffrement & TLS</h2>
            <div class="space-y-3 text-sm leading-relaxed text-zinc-600">
                <p>Tout échange `front/contact` `InquiryWizard` et `admin` est chiffré TLS 1.3. Mots de passe `bcrypt`, sessions `landman_session` `XSRF-TOKEN` httpOnly, CSRF Livewire.</p>
                <p>Plans PDF `plots/plans` et images `cms/*` stockés `Storage::disk('public')` avec variantes `WebP/AVIF` `ImageService::storeOptimized` — accès direct `Storage::url` mais dossiers `storage/logs` non exposés.</p>
            </div>
        </section>

        <section id="acces" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">02.</span> Contrôle d'accès</h2>
            <div class="space-y-3 text-sm leading-relaxed text-zinc-600">
                <p>Rôles `Spatie` : `Super Admin` `cms.*` `media.manage` `inquiries.*` , `Éditeur BTP` `projects.*` , `Commercial Lotissement` `programs.*` . Middleware `role:Super Admin` `routes/admin.php:12` et `permission:inquiries.export` `throttle:5,1` sur exports CSV.</p>
                <p>Back-office `admin/cms` `authorize('cms.manage')` systématique. `admin/users` réservé Super Admin.</p>
            </div>
        </section>

        <section id="sauvegardes" class="mb-10 pb-8 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">03.</span> Sauvegardes & disponibilité</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Sauvegardes quotidiennes chiffrées (DB + `storage/app/public`), rétention 30 jours, test de restauration mensuel. Hébergement `OVH/AWS` `ISO 27001`, monitoring `PageVisit` 30j, logs `storage/logs/laravel.log` rotation.</p>
        </section>

        <section id="tracabilite" class="mb-10">
            <h2 class="text-lg font-bold text-[#003366] mb-4 flex items-center gap-2"><span class="font-mono text-sm text-[#87CEEB]">04.</span> Traçabilité & incident</h2>
            <p class="text-sm leading-relaxed text-zinc-600">Toute modification CMS est historisée `SiteSettingHistory` `diff()` `admin/cms/history`. Incident : notification `{{ $email }}` sous 72h, journalisation, correctif et information ARTCI/CNIL si données personnelles impactées. Contact sécurité : {{ $email }} — Objet [Sécurité].</p>
        </section>
    </div>
</section>
