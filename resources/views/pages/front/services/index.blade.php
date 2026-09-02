<?php

use App\Enums\ServiceType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Nos Services — SIBEA-CI Laboratoire')] class extends Component {
    #[Url]
    public int $page = 1;
    public int $perPage = 6;

    public function render(): \Illuminate\View\View
    {
        $all = Cache::remember('services.list', 300, fn () => \App\Models\SiteSetting::get('services.list', []));
        if (empty($all)) {
            $all = array_map(fn($c) => ['key' => $c->value, 'title' => $c->label(), 'desc' => '', 'image' => null], \App\Enums\ServiceType::cases());
        }
        $total = count($all);
        $offset = ($this->page - 1) * $this->perPage;
        $items = array_slice($all, $offset, $this->perPage);
        $paginator = new LengthAwarePaginator($items, $total, $this->perPage, $this->page, ['path' => request()->url(), 'query' => request()->query()]);
        $hero = Cache::remember('services.hero', 300, fn () => \App\Models\SiteSetting::get('services.hero', [
            'title' => '',
            'body' => '',
            'badge' => '',
            'image' => null,
        ]));

        return view('pages.front.services.index', [
            'services' => $items,
            'paginator' => $paginator,
            'allServices' => $all,
            'offset' => $offset,
            'hero' => $hero,
        ]);
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, min($page, max(1, (int) ceil(count(Cache::remember('services.list', 300, fn () => \App\Models\SiteSetting::get('services.list', []))) / $this->perPage))));
    }
}; ?>

<section class="bg-white ">

    {{-- Page Hero - Services — aligné sur À propos (h-[450px] centré) --}}
    <x-page-hero-simple
        :title="$hero['title'] ?: 'Nos Services — <span class=&quot;font-black&quot;>6 expertises</span>'"
        :subtitle="$hero['body'] ?: 'BTP, VRD, lotissement, rénovation, architecture, électricité — réponses concrètes et contextualisées à Abidjan et Bingerville.'"
        :badge="$hero['badge'] ?: 'SERVICES — 6 EXPERTISES'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Services SIBEA-CI'"
        :breadcrumb="[['label'=>'Services','url'=>route('front.services.index')]]"
    />

    {{--  Page Contente  --}}
    <div class="bg-white mx-auto max-w-7xl px-4 py-12 lg:px-8 ">
        <div class="flex items-center justify-between">
            <div class="text-xs tracking-widest text-zinc-500">{{ count($allServices) }} service(s) • page
                {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}</div>
            @if ($paginator->hasPages())
                <div class="flex gap-1">
                    <flux:button size="xs" variant="ghost" :disabled="$paginator->onFirstPage()"
                        wire:click="gotoPage({{ $paginator->currentPage() - 1 }})">← Précédent</flux:button>
                    <flux:button size="xs" variant="ghost" :disabled="!$paginator->hasMorePages()"
                        wire:click="gotoPage({{ $paginator->currentPage() + 1 }})">Suivant →</flux:button>
                </div>
            @endif
        </div>
        @php
            $fallbacks = [
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop',
            ];
        @endphp
        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $i => $service)
                @php
                    $globalIndex = $offset + $loop->index;
                    $title = $service['title'] ?? $service['key'];
                    $desc = $service['desc'] ?? '';
                    $key = $service['key'];
                    $hasImage = !empty($service['image']) && Storage::disk('public')->exists($service['image']);
                    $fallback = $fallbacks[$globalIndex % count($fallbacks)];
                @endphp
                <a href="{{ route('front.services.show', $key) }}"
                    class="group relative overflow-hidden rounded-2xl min-h-[360px] flex flex-col justify-between p-7 shadow-sm hover:shadow-2xl transition-all duration-500 border border-zinc-200">
                    @if($hasImage)
                        <img src="{{ Storage::disk('public')->url($service['image']) }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/60 to-zinc-900/10"></div>
                        <div class="absolute inset-0 bg-primary/20 mix-blend-multiply opacity-60 group-hover:opacity-30 transition duration-500"></div>
                    @else
                        <img src="{{ $fallback }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/70 to-zinc-900/20"></div>
                        <div class="absolute inset-0 bg-primary/10 group-hover:bg-primary/0 transition duration-500"></div>
                    @endif
                    <div class="relative">
                        <div class="text-xs tracking-widest text-white/60">0{{ $globalIndex + 1 }} — {{ strtoupper($key) }}</div>
                        <h3 class="mt-3 text-xl font-black leading-tight text-white drop-shadow">{{ $title }}</h3>
                    </div>
                    <div class="relative">
                        <p class="text-sm leading-relaxed text-zinc-200 line-clamp-3">{{ $desc ?: 'Expertise SIBEA-CI — '.$title.' — réponses concrètes et contextualisées.' }}</p>
                        <div class="mt-4 inline-flex items-center gap-2 text-xs font-bold tracking-widest text-white group-hover:gap-3 transition-all">DÉCOUVRIR <span class="transition-transform group-hover:translate-x-1">→</span></div>
                    </div>
                </a>
            @endforeach
        </div>
        @if ($paginator->hasPages())
            <div class="mt-8 flex justify-center">{{ $paginator->links() }}</div>
        @endif
    </div>
</section>
