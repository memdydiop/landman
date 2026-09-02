<?php

use App\Enums\ServiceType;
use App\Models\SiteSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('Nos Pôles BTP & VRD — SIBEA-CI')] class extends Component {
    #[Url]
    public int $page = 1;
    public int $perPage = 6;

    private function getServicesList(): array
    {
        $all = Cache::remember('services.list', 300, fn () => SiteSetting::get('services.list', []));
        if (empty($all)) {
            return array_map(fn($c) => [
                'key' => $c->value, 
                'title' => $c->label(), 
                'desc' => '', 
                'image' => null
            ], ServiceType::cases());
        }
        return $all;
    }

    public function render(): \Illuminate\View\View
    {
        $all = $this->getServicesList();
        $total = count($all);
        $offset = max(0, ($this->page - 1) * $this->perPage);
        $items = array_slice($all, $offset, $this->perPage);
        
        $paginator = new LengthAwarePaginator(
            $items, 
            $total, 
            $this->perPage, 
            $this->page, 
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $hero = Cache::remember('services.hero', 300, fn () => SiteSetting::get('services.hero', [
            'title' => 'Pôles d\'intervention BTP & Aménagement',
            'body' => 'De la viabilisation foncière à la livraison gros œuvre — nos moyens techniques et humains sur le terrain en Côte d\'Ivoire.',
            'badge' => 'EXÉCUTION & CHANTIERS',
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
        $total = count($this->getServicesList());
        $maxPage = max(1, (int) ceil($total / $this->perPage));
        $this->page = max(1, min($page, $maxPage));
    }
}; ?>

<section class="bg-zinc-100/60 min-h-screen">

    {{-- Hero Page --}}
    <x-page-hero-simple
        :title="$hero['title'] ?? 'Pôles d\'intervention BTP & Aménagement'"
        :subtitle="$hero['body'] ?? 'De la viabilisation foncière à la livraison gros œuvre — nos moyens techniques et humains sur le terrain.'"
        :badge="$hero['badge'] ?? 'EXÉCUTION & CHANTIERS'"
        :image="$hero['image'] ?? null"
        :image-alt="'Pôles d\'intervention SIBEA-CI'"
        :breadcrumb="[['label'=>'Services','url'=>route('front.services.index')]]"
    />

    {{-- Grille des Pôles --}}
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-4">
            <div class="text-xs font-mono font-bold tracking-widest text-zinc-600 uppercase">
                {{ count($allServices) }} PÔLE(S) TECHNIQUE(S) DISPONIBLE(S)
            </div>
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

        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                @php
                    $globalIndex = $offset + $loop->index;
                    $title = $service['title'] ?? $service['key'];
                    $desc = $service['desc'] ?? '';
                    $key = $service['key'];
                    $hasImage = !empty($service['image']) && Storage::disk('public')->exists($service['image']);
                    $fallback = $fallbacks[$globalIndex % count($fallbacks)];
                    $validKey = ServiceType::tryFrom($key) ? $key : 'btp';
                @endphp

                <a href="{{ route('front.services.show', $validKey) }}"
                    class="group relative overflow-hidden rounded-2xl min-h-[380px] flex flex-col justify-between p-6 shadow-md hover:shadow-2xl transition-all duration-500 border border-zinc-300/80 bg-zinc-900">
                    
                    <!-- Fond Image avec Overlays Chantiers -->
                    @if($hasImage)
                        <img src="{{ Storage::disk('public')->url($service['image']) }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover opacity-60 transition duration-700 group-hover:scale-105" loading="lazy" />
                    @else
                        <img src="{{ $fallback }}" alt="{{ $title }}" class="absolute inset-0 size-full object-cover opacity-60 transition duration-700 group-hover:scale-105" loading="lazy" />
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/60 to-transparent"></div>

                    <!-- Header de la Carte : Numéro de Pôle & Badge -->
                    <div class="relative flex items-center justify-between">
                        <span class="rounded bg-amber-500/90 px-2 py-0.5 text-[11px] font-mono font-black text-zinc-950">
                            PÔLE 0{{ $globalIndex + 1 }}
                        </span>
                        <span class="text-[10px] font-mono tracking-widest text-zinc-300 uppercase">
                            {{ strtoupper($key) }}
                        </span>
                    </div>

                    <!-- Footer de la Carte : Titre & Action -->
                    <div class="relative">
                        <h3 class="text-2xl font-black leading-tight text-white uppercase group-hover:text-amber-400 transition-colors">
                            {{ $title }}
                        </h3>
                        <p class="mt-2 text-xs leading-relaxed text-zinc-300 line-clamp-3">
                            {{ $desc ?: 'Moyens matériels et équipes d\'exécution SIBEA-CI dédiés aux travaux de '.$title.'.' }}
                        </p>
                        
                        <div class="mt-5 inline-flex items-center gap-2 rounded-lg bg-white/10 backdrop-blur-sm px-4 py-2 text-xs font-black tracking-wider text-white group-hover:bg-amber-500 group-hover:text-zinc-950 transition-all">
                            DÉTAILS TECHNIQUE & CHANTIERS <span>→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($paginator->hasPages())
            <div class="mt-10 flex justify-center">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>
</section>
