@props([
    'title' => '',
    'subtitle' => '',
    'badge' => '',
    'image' => null,
    'imageAlt' => '',
    'height' => 'h-[250px]  lg:h-[300px] py-12 sm:py-16',
    'overlay' => 'bg-primary/50',
    'ctaButtons' => [],
    'align' => 'center',
    'variant' => 'primary',
    'breadcrumb' => null,
])

@php
    $hasImage = !empty($image) && \Illuminate\Support\Facades\Storage::disk('public')->exists($image);
    $ctaPrimary = $ctaButtons[0] ?? null;
    $ctaSecondary = $ctaButtons[1] ?? null;
    // Cohérence avec À propos : fond slate-900 + gradient, badge cyan, centré
    // Image partagée prioritaire (CMS > Hero Global) — une seule image pour tous les heroes
    $sharedHero = \App\Models\SiteSetting::get('hero.shared', []);
    $sharedImage = $sharedHero['image'] ?? null;
    $hasShared = !empty($sharedImage) && \Illuminate\Support\Facades\Storage::disk('public')->exists($sharedImage);
    $effectiveImage = $hasShared ? $sharedImage : ($hasImage ? $image : null);
    $hasEffective = !empty($effectiveImage) && \Illuminate\Support\Facades\Storage::disk('public')->exists($effectiveImage);
    $variantBg = 'bg-slate-900';
    $allowedTags = '<span><br><strong><em><b><i><u>';
    $safeTitle = strip_tags($title ?? '', $allowedTags);
    $safeSubtitle = strip_tags($subtitle ?? '', $allowedTags . '<a><p>');
    $safeBadge = e($badge ?? '');
    $fallbackHero = 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80';
@endphp

<div class="relative {{ $height }} w-full bg-slate-900 flex items-center justify-center overflow-hidden">
    
    @if($hasEffective)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($effectiveImage) }}" alt="{{ $safeTitle ? strip_tags($safeTitle) : 'Hero' }}" 
        class="absolute inset-0 size-full object-cover " loading="eager" />
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/60 to-zinc-900/10"></div>
        <div class="absolute inset-0 bg-primary/20 mix-blend-multiply opacity-60"></div>
    @else
        <img src="{{ $fallbackHero }}" alt="Hero" class="absolute inset-0 size-full object-cover " loading="eager" />
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/70 to-zinc-900/20"></div>
        <div class="absolute inset-0 bg-primary/10"></div>
    @endif

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white z-10 space-y-4">
        @if($badge)
            <span class="text-xs font-bold uppercase tracking-[0.25em] text-[#87CEEB] bg-[#87CEEB]/15 px-4 py-1.5 border border-[#87CEEB]/20 inline-block">{!! $safeBadge !!}</span>
        @endif

        @if($safeTitle)
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight leading-tight">{!! $safeTitle !!}</h1>
        @endif

        @if($safeSubtitle)
            <p class="text-xs sm:text-sm md:text-base text-slate-300 leading-relaxed max-w-2xl mx-auto font-light">{!! $safeSubtitle !!}</p>
        @endif

        @if($ctaPrimary || $ctaSecondary)
            <div class="flex justify-center gap-3 pt-2">
                @if($ctaPrimary)
                    <a href="{{ $ctaPrimary['url'] }}" class="bg-white px-6 py-3 text-xs font-bold tracking-widest text-zinc-900 hover:bg-zinc-100 {{ $ctaPrimary['class'] ?? '' }}">{{ $ctaPrimary['label'] }}</a>
                @endif
                @if($ctaSecondary)
                    <a href="{{ $ctaSecondary['url'] }}" class="border border-white px-6 py-3 text-xs font-bold tracking-widest text-white hover:bg-white hover:text-zinc-900 {{ $ctaSecondary['class'] ?? '' }}">{{ $ctaSecondary['label'] }}</a>
                @endif
            </div>
        @endif

        @if($breadcrumb !== false)
            <div class="flex items-center justify-center gap-2 text-xs text-slate-400 pt-2">
                <a href="{{ route('home') }}" class="hover:text-white">Accueil</a><span>›</span>
                @if(is_array($breadcrumb))
                    @foreach($breadcrumb as $crumb)
                        @if(!$loop->last)
                            <a href="{{ $crumb['url'] ?? '#' }}" class="hover:text-white">{{ $crumb['label'] }}</a><span>›</span>
                        @else
                            <span class="text-white">{{ $crumb['label'] }}</span>
                        @endif
                    @endforeach
                @elseif($breadcrumb)
                    <span class="text-white">{{ $breadcrumb }}</span>
                @elseif($safeTitle)
                    <span class="text-white line-clamp-1">{{ Str::limit(strip_tags($safeTitle), 40) }}</span>
                @endif
            </div>
        @endif
    </div>
</div>