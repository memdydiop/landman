<?php

use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.front')] #[Title('Actualités & Journal de Chantier — SIBEA-CI')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $posts = Post::published()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where(function ($sub) use ($s) {
                    $sub->where('title', 'like', '%'.$s.'%')
                        ->orWhere('excerpt', 'like', '%'.$s.'%')
                        ->orWhere('content', 'like', '%'.$s.'%');
                });
            })
            ->latest('published_at')
            ->paginate(9);

        $hero = Cache::remember(
            'posts.hero',
            300,
            fn () => SiteSetting::get('posts.hero', [
                'title' => 'ACTUALITÉS & JOURNAL DE CHANTIER',
                'body' => 'Rapports d\'avancement, normes BTP, suivi de voiries et publications techniques du laboratoire SIBEA-CI.',
                'badge' => 'BULLETIN TECHNIQUE & SUIVI DE CHANTIER',
                'image' => null,
            ]),
        );

        return view('pages.front.posts.index', [
            'posts' => $posts,
            'hero' => $hero,
        ]);
    }
}; ?>

<section class="bg-zinc-100/70 min-h-screen pb-12">
    {{-- Hero Page Actualités --}}
    <x-page-hero-simple
        :title="$hero['title'] ?: 'ACTUALITÉS & JOURNAL DE CHANTIER'"
        :subtitle="$hero['body'] ?: 'Rapports d\'avancement, normes BTP, suivi de voiries et publications techniques du laboratoire SIBEA-CI.'"
        :badge="$hero['badge'] ?: 'BULLETIN TECHNIQUE & SUIVI DE CHANTIER'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Actualités SIBEA-CI'"
        :breadcrumb="[['label'=>'Actualités','url'=>route('front.posts.index')]]"
    />

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <!-- Barre de Recherche Technique -->
        <div class="rounded-2xl border border-zinc-300/80 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="relative flex-1">
                    <input wire:model.live.debounce.300ms="search" 
                           type="text"
                           placeholder="Rechercher un rapport de chantier, une norme BTP, un article..." 
                           class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-xs font-medium text-zinc-900 placeholder-zinc-400 focus:border-amber-500 focus:bg-white focus:ring-0" />
                </div>
                
                @if($search)
                    <button wire:click="$set('search','')" 
                            class="rounded-xl bg-zinc-200 px-4 py-2.5 text-xs font-bold tracking-wider text-zinc-700 hover:bg-zinc-300 transition">
                        EFFACER
                    </button>
                @endif
            </div>
        </div>

        @php
            $fallbacks = [
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop',
            ];
        @endphp

        <!-- Grille des Publications (Format Cartes Immersives Sombres) -->
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($posts as $post)
                @php
                    $hasImage = !empty($post->cover_path) && Storage::disk('public')->exists($post->cover_path);
                    $coverUrl = $hasImage ? Storage::disk('public')->url($post->cover_path) : $fallbacks[$loop->index % count($fallbacks)];
                    $itemIndex = ($posts->currentPage() - 1) * $posts->perPage() + $loop->iteration;
                @endphp
                
                <a href="{{ route('front.posts.show', $post) }}" 
                   class="group relative flex min-h-[380px] flex-col justify-between overflow-hidden rounded-2xl border border-zinc-300/80 bg-zinc-900 p-6 shadow-md transition-all duration-500 hover:border-amber-500 hover:shadow-2xl">
                    
                    <img src="{{ $coverUrl }}" alt="{{ $post->title }}" class="absolute inset-0 size-full object-cover opacity-60 transition duration-700 group-hover:scale-105" loading="lazy" />
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/60 to-transparent"></div>

                    <!-- En-tête Carte -->
                    <div class="relative flex items-center justify-between">
                        <span class="rounded bg-amber-500/90 px-2 py-0.5 font-mono text-[11px] font-black text-zinc-950">
                            REP-{{ sprintf('%02d', $itemIndex) }}
                        </span>
                        <span class="rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md bg-zinc-800/80 border border-white/10">
                            📅 {{ $post->published_at?->format('d/m/Y') ?? 'NON PUBLIÉ' }}
                        </span>
                    </div>

                    <!-- Pied de Carte -->
                    <div class="relative">
                        <div class="font-mono text-[11px] font-bold text-amber-400 uppercase tracking-widest">
                            PUBLICATION TECHNIQUE SIBEA-CI
                        </div>
                        <h3 class="mt-2 text-xl font-black leading-tight text-white uppercase group-hover:text-amber-400 transition-colors line-clamp-2">
                            {{ $post->title }}
                        </h3>

                        <p class="mt-3 text-xs leading-relaxed text-zinc-300 line-clamp-2">
                            {{ $post->excerpt ?: 'Consultez ce rapport pour découvrir les détails techniques et les avancées de nos projets d\'aménagement.' }}
                        </p>

                        <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white/10 backdrop-blur-sm px-3.5 py-1.5 text-xs font-black tracking-wider text-white group-hover:bg-amber-500 group-hover:text-zinc-950 transition-all">
                            CONSULTER L'ARTICLE <span>→</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 bg-white p-12 text-center">
                    <div class="font-mono text-xs font-bold text-zinc-400">AUCUN ARTICLE OU BULLETIN NE CORRESPOND À VOTRE RECHERCHE</div>
                    @if($search)
                        <button wire:click="$set('search','')" 
                                class="mt-4 inline-flex items-center rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-zinc-950 hover:bg-amber-400">
                            RÉINITIALISER LA RECHERCHE
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    </div>
</section>
