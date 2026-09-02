<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')
        @php
            $seo = \App\Models\SiteSetting::get('seo', []);
            $theme = \App\Models\SiteSetting::get('theme', []);
            $header = \App\Models\SiteSetting::get('header', []);
            $global = \App\Models\SiteSetting::get('global', []);
            $headerMenu = $header['menu_items'] ?? null;
            $headerLogo = $header['logo'] ?? null;
            $globalPhone = $global['company_phone'] ?? null;
            $globalEmail = $global['company_email'] ?? null;
            $globalWhatsapp = $global['company_whatsapp'] ?? $globalPhone;
            $headerCtaText = $header['header_cta_text'] ?? 'Demander un devis';
            $headerCtaUrl = $header['header_cta_url'] ?? route('front.contact');
            $footerPartners = \Illuminate\Support\Facades\Cache::remember('footer.partners', 300, fn () => \App\Models\Partner::published()->orderBy('position')->limit(6)->get());
            $footerLinks = \App\Models\SiteSetting::get('footer.legal', $global['footer_links'] ?? [
                ['label'=>'Accueil','url'=>'/'],
                ['label'=>'À propos','url'=>'/a-propos'],
                ['label'=>'Services','url'=>'/services'],
                ['label'=>'Lotissements','url'=>'/lotissements'],
                ['label'=>'Réalisations','url'=>'/realisations'],
                ['label'=>'Actualités','url'=>'/actualites'],
                ['label'=>'Contact','url'=>'/contact'],
            ]);
        @endphp
        <meta name="description" content="{{ $metaDescription ?? $seo['home_desc'] ?? 'SIBEA-CI — BTP, Aménagement Urbain (VRD) et Lotissement. Terrains viabilisés, constructions, énergie et agro-industrie en Côte d\'Ivoire.' }}">
        <meta property="og:title" content="{{ $title ?? $seo['home_title'] ?? config('app.name', 'SIBEA-CI') }}">
        <meta property="og:description" content="{{ $metaDescription ?? $seo['home_desc'] ?? '' }}">
        <meta property="og:type" content="website">
        @if(!empty($seo['og_image']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($seo['og_image']))
            <meta property="og:image" content="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($seo['og_image']) }}">
        @endif
        <meta name="theme-color" content="{{ $theme['primary'] ?? '#003366' }}">
        @if(!empty($theme['primary']))
            <style>:root{--color-primary:{{ $theme['primary'] }};--color-accent:{{ $theme['accent'] ?? $theme['primary'] }};--color-secondary:{{ $theme['accent'] ?? $theme['primary'] }};}</style>
        @endif
    </head>
    <body class="bg-white text-gray-900 antialiased">
        <header class="sticky top-0 z-40 border-b border-zinc-200 bg-white backdrop-blur h-16">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 ">
                
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 font-semibold tracking-tight">
                    @if(!empty($headerLogo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($headerLogo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($headerLogo) }}" 
                        alt="{{ $global['company_name'] ?? 'SIBEA-CI' }}" 
                        class="w-30 object-cover" />
                    @else
                        <x-app-logo-icon class="size-7 text-primary" />
                    @endif
                    <div class="flex flex-col">
                        <span class="hidden text-xs font-normal text-zinc-500 lg:inline">BTP · Électricité · Pétrole · Agro</span>
                    </div>
                </a>

                @if(!empty($headerMenu))
                    <nav class="hidden items-center gap-6 text-sm lg:flex">
                        @foreach(collect($headerMenu)->sortBy('order') as $item)
                            @php
                                $itemPath = trim(parse_url($item['url'], PHP_URL_PATH) ?? '/', '/');
                                $isActive = $itemPath === '' ? request()->is('/') : request()->is($itemPath) || request()->is($itemPath.'/*');
                                // Ne pas marquer actif si URL externe ou ancre
                                if (str_starts_with($item['url'], 'http') || $item['url'] === '#') $isActive = false;
                            @endphp
                            <a href="{{ $item['url'] }}" @class(['hover:text-primary', 'text-primary font-medium' => $isActive])>{{ $item['label'] }}</a>
                        @endforeach
                    </nav>
                @else
                    <nav class="hidden items-center gap-6 text-sm font-medium lg:flex ">
                        <a href="{{ route('home') }}" @class([ 'hover:text-primary', 'text-primary font-medium' => request()->routeIs('home') ])>Accueil</a>
                        <a href="{{ route('front.services.index') }}" @class([ 'hover:text-primary', 'text-primary font-medium' => request()->routeIs('front.services.*') ])>Services</a>
                        <a href="{{ route('front.programs.index') }}" @class([ 'hover:text-primary', 'text-primary font-medium' => request()->routeIs('front.programs.*') ])>Lotissements</a>
                        <a href="{{ route('front.projects.index') }}" @class([ 'hover:text-primary', 'text-primary font-medium' => request()->routeIs('front.projects.*') ])>Réalisations</a>
                        <a href="{{ route('front.posts.index') }}" @class([ 'hover:text-primary', 'text-primary font-medium' => request()->routeIs('front.post.*') ])>Actualités</a>
                        <a href="{{ route('front.about') }}" @class([ 'hover:text-primary', 'text-primary font-medium' => request()->routeIs('front.about') ])>À propos</a>
                        <a href="{{ route('front.contact') }}" @class([ 'hover:text-primary', 'text-primary font-medium' => request()->routeIs('front.contact') ])>Contact</a>
                    </nav>
                @endif
                <div class="flex items-center gap-2">
                    <a href="{{ $headerCtaUrl }}" class="hidden rounded-full bg-primary px-5 py-2 text-sm font-medium text-white hover:bg-[#002244] lg:inline-flex">{{ $headerCtaText }}</a>
                    <button class="lg:hidden" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')">
                        <flux:icon.bars-2 class="size-6" />
                    </button>
                </div>
            </div>
            <div id="mobile-nav" class="hidden border-t border-zinc-200 bg-white px-4 py-3 lg:hidden">
                <nav class="flex flex-col gap-3 text-sm">
                    @if(!empty($headerMenu))
                        @foreach(collect($headerMenu)->sortBy('order') as $item)
                            @php
                                $mPath = trim(parse_url($item['url'], PHP_URL_PATH) ?? '/', '/');
                                $mActive = $mPath === '' ? request()->is('/') : request()->is($mPath) || request()->is($mPath.'/*');
                                if (str_starts_with($item['url'], 'http') || $item['url'] === '#') $mActive = false;
                            @endphp
                            <a href="{{ $item['url'] }}" @class(['hover:text-primary', 'text-primary font-medium' => $mActive])>{{ $item['label'] }}</a>
                        @endforeach
                    @else
                        <a href="{{ route('home') }}">Accueil</a>
                        <a href="{{ route('front.services.index') }}">Services</a>
                        <a href="{{ route('front.programs.index') }}">Lotissements</a>
                        <a href="{{ route('front.projects.index') }}">Réalisations</a>
                        <a href="{{ route('front.posts.index') }}">Actualités</a>
                        <a href="{{ route('front.about') }}">À propos</a>
                        <a href="{{ route('front.contact') }}">Contact</a>
                    @endif
                </nav>
            </div>
        </header>

        <main class="min-h-[60vh]">
            {{ $slot }}
        </main>

        <footer class="bg-[#0B2240] text-slate-300">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Style AfricaSpace : h4 text-[#87CEEB] uppercase, text-xs text-slate-400 -->
                    <div class="flex flex-col space-y-4">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                            @if(!empty($headerLogo) && \Illuminate\Support\Facades\Storage::disk('public')->exists($headerLogo))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($headerLogo) }}" alt="{{ $global['company_name'] ?? 'SIBEA-CI' }}" class="size-8 rounded object-cover" />
                            @else
                                <x-app-logo-icon class="size-8 text-white group-hover:text-[#87CEEB] transition" />
                            @endif
                            <span class="text-lg font-bold tracking-tight text-white">{{ $global['company_name'] ?? 'SIBEA-CI' }}</span>
                        </a>
                        <p class="text-xs leading-relaxed text-slate-400">{{ $global['company_siret'] ?? 'SARL depuis 2022' }} — BTP, Électricité, Pétrole, Agro-industrie. Terrains viabilisés, garanties décennales, agréments ministériels.</p>
                        <p class="text-xs text-slate-500">{{ $global['company_address'] ?? 'Abidjan, Bingerville, Abatta (Lot 935, Îlot 86)' }}</p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="rounded bg-white/10 px-2 py-1 text-xs text-slate-300 border border-white/10">Garantie décennale</span>
                            <span class="rounded bg-white/10 px-2 py-1 text-xs text-slate-300 border border-white/10">ACD</span>
                            <span class="rounded bg-white/10 px-2 py-1 text-xs text-slate-300 border border-white/10">RSE</span>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-4">
                        <h4 class="text-sm font-bold text-[#87CEEB] uppercase tracking-wider">Services</h4>
                        <ul class="space-y-2 text-xs text-slate-400">
                            @foreach(\App\Enums\ServiceType::cases() as $s)
                                <li><a href="{{ route('front.services.show', $s->value) }}" class="hover:text-[#87CEEB] transition">{{ $s->label() }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="flex flex-col space-y-4">
                        <h4 class="text-sm font-bold text-[#87CEEB] uppercase tracking-wider">Légal & liens</h4>
                        <ul class="space-y-2 text-xs text-slate-400">
                            @foreach($footerLinks as $link)
                                <li><a href="{{ $link['url'] }}" class="hover:text-[#87CEEB] transition">{{ $link['label'] }}</a></li>
                            @endforeach
                            @if(!empty($global['footer_mentions_legales']))
                                <li class="pt-2 border-t border-slate-800/60"><a href="{{ route('front.legal.mentions') }}" class="hover:text-white transition">{{ $global['footer_mentions_legales'] }}</a></li>
                            @endif
                            @if(!empty($global['footer_cgv']))
                                <li><a href="{{ route('front.legal.cgv') }}" class="hover:text-white transition">{{ $global['footer_cgv'] }}</a></li>
                            @endif
                            @if(!empty($global['footer_confidentialite']))
                                <li><a href="{{ route('front.legal.confidentialite') }}" class="hover:text-white transition">{{ $global['footer_confidentialite'] }}</a></li>
                            @endif
                            <li><a href="{{ route('front.legal.securite') }}" class="hover:text-white transition">Sécurité</a></li>
                        </ul>
                    </div>
                    <div class="flex flex-col space-y-4">
                        <h4 class="text-sm font-bold text-[#87CEEB] uppercase tracking-wider">Contact</h4>
                        <ul class="space-y-2 text-xs text-slate-400 leading-relaxed">
                            <li>{{ $global['company_address'] ?? 'Abidjan, Bingerville, Abatta (Lot 935, Îlot 86)' }}</li>
                            <li>{{ $globalPhone ?? '+225 07 00 00 00 00' }}</li>
                            <li>{{ $globalEmail ?? 'contact@sibea-ci.ci' }}</li>
                            @if(!empty($global['company_hours']))<li class="text-xs text-slate-500">{{ $global['company_hours'] }}</li>@endif
                        </ul>
                        <p class="text-xs text-slate-400 leading-relaxed">Discutez directement en temps réel avec un conseiller.</p>
                        @php
                            $waRaw2 = $globalWhatsapp ?? $globalPhone ?? '2250700000000';
                            $waClean = preg_replace('/[^0-9]/', '', $waRaw2);
                            if (strlen($waClean) === 10 && str_starts_with($waClean, '07')) $waClean = '225'.ltrim($waClean, '0');
                            if (strlen($waClean) === 10 && !str_starts_with($waClean, '225')) $waClean = '225'.$waClean;
                        @endphp
                        <a href="https://wa.me/{{ $waClean }}?text={{ urlencode('Bonjour SIBEA-CI, je souhaite des infos.') }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#87CEEB] px-4 py-3 text-xs font-bold uppercase tracking-wider text-[#0B2240] hover:bg-white transition w-full" aria-label="WhatsApp"><svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg> WhatsApp consultant</a>
                        <span class="text-[10px] text-slate-500 italic leading-tight">Service disponible 7j/7 — Côte d'Ivoire.</span>
                    </div>
                </div>
                <div class="pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-slate-500 border-t border-slate-800 mt-10">
                    <p class="text-center md:text-left mb-4 md:mb-0">{{ $global['footer_copyright'] ?? '© '.date('Y').' '.($global['company_name'] ?? 'SIBEA-CI').'. Tous droits réservés.' }}</p>
                    <div class="flex items-center gap-4">
                        @if(!empty($global['social_networks']))
                            @foreach(array_slice($global['social_networks'],0,3) as $sn)
                                @php $iconKey2 = strtolower($sn['icon'] ?? $sn['name'] ?? ''); @endphp
                                <a href="{{ $sn['url'] }}" target="_blank" class="hover:text-[#87CEEB] transition p-1" aria-label="{{ $sn['name'] }}" title="{{ $sn['name'] }}">
                                    @if(str_contains($iconKey2,'facebook'))
                                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                                    @elseif(str_contains($iconKey2,'linkedin'))
                                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 0h-14c-2.76 0-5 2.24-5 5v14c0 2.76 2.24 5 5 5h14c2.76 0 5-2.24 5-5v-14c0-2.76-2.24-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.27c-.97 0-1.75-.79-1.75-1.75s.78-1.75 1.75-1.75 1.75.79 1.75 1.75-.78 1.75-1.75 1.75zm13.5 12.27h-3v-5.6c0-1.34-.48-2.25-1.67-2.25-.91 0-1.45.61-1.69 1.2-.09.21-.11.51-.11.81v5.84h-3s.04-9.5 0-11h3v1.56c.4-.62 1.12-1.5 2.73-1.5 1.99 0 3.49 1.3 3.49 4.09v6.85z"/></svg>
                                    @elseif(str_contains($iconKey2,'instagram'))
                                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.8 2h8.4A5.2 5.2 0 0121 7.8v8.4a5.2 5.2 0 01-5.2 5.2H7.8A5.2 5.2 0 012.6 16.2V7.8A5.2 5.2 0 017.8 2m-.2 2A3.6 3.6 0 004 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 003.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 011.25 1.25A1.25 1.25 0 0117.25 8 1.25 1.25 0 0116 6.75a1.25 1.25 0 011.25-1.25M12 7a5 5 0 015 5 5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5m0 2a3 3 0 100 6 3 3 0 000-6z"/></svg>
                                    @elseif(str_contains($iconKey2,'youtube'))
                                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 00-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 00.5 6.2 31.5 31.5 0 000 12a31.5 31.5 0 00.5 5.8 3 3 0 002.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 002.1-2.1A31.5 31.5 0 0024 12a31.5 31.5 0 00-.5-5.8zM9.75 15.5v-7L15.5 12l-5.75 3.5z"/></svg>
                                    @elseif(str_contains($iconKey2,'x') || str_contains($iconKey2,'twitter'))
                                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.55 7.48L23 22h-5.6l-4.39-5.74L7.98 22H4.88l7-8.02L5 2h5.74l3.97 5.25L18.9 2zm-1.1 18h1.7L8.1 3.85H6.2L17.8 20z"/></svg>
                                    @else
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                                    @endif
                                </a>
                            @endforeach
                        @else
                            <a href="#" class="hover:text-[#87CEEB] transition p-1" aria-label="LinkedIn"><flux:icon name="link" class="size-4" /></a>
                            <a href="#" class="hover:text-[#87CEEB] transition p-1" aria-label="Instagram"><flux:icon name="camera" class="size-4" /></a>
                            <a href="#" class="hover:text-[#87CEEB] transition p-1" aria-label="X"><flux:icon name="x-mark" class="size-4" /></a>
                        @endif
                    </div>
                </div>
            </div>
        </footer>

        <x-front.whatsapp-float />

        @persist('toast')
            <flux:toast.group><flux:toast /></flux:toast.group>
        @endpersist
        @fluxScripts
    </body>
</html>
