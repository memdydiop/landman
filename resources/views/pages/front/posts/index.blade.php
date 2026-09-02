<?php

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.front')] #[Title('Recherche — Actualités SIBEA-CI')] class extends Component {
    use WithPagination;

    public function render(): \Illuminate\View\View
    {
        $hero = Cache::remember(
            'posts.hero',
            300,
            fn() => \App\Models\SiteSetting::get('posts.hero', [
                'title' => '',
                'body' => '',
                'badge' => '',
                'image' => null,
            ]),
        );

        return view('pages.front.posts.index', [
            'posts' => Post::published()->latest('published_at')->paginate(9),
            'hero' => $hero,
        ]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        :title="$hero['title'] ?: 'Actualités &amp; <span class=&quot;font-black&quot;>Recherche</span>'"
        :subtitle="$hero['body'] ?: 'Conseils foncier, normes BTP, suivi chantiers Bingerville — publications contextualisées du laboratoire SIBEA-CI.'"
        :badge="$hero['badge'] ?: 'RECHERCHE — PUBLICATIONS'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Actualités SIBEA-CI'"
        :breadcrumb="[['label'=>'Actualités','url'=>route('front.posts.index')]]"
    />

    @php
        $fallbacks = [
            'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop',
        ];
    @endphp
    <div class="mx-auto max-w-7xl px-4 py-10 lg:px-8">
        <div class="grid gap-6 md:grid-cols-3">
            @forelse($posts as $post)
                @php
                    $hasImage = !empty($post->cover_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($post->cover_path);
                    $fallback = $fallbacks[$loop->index % count($fallbacks)];
                @endphp
                <a href="{{ route('front.posts.show', $post) }}" class="group relative overflow-hidden rounded-2xl min-h-[360px] flex flex-col justify-between p-7 shadow-sm hover:shadow-2xl transition-all duration-500 border border-zinc-200">
                    @if($hasImage)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->cover_path) }}" alt="{{ $post->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/60 to-zinc-900/10"></div>
                        <div class="absolute inset-0 bg-primary/20 mix-blend-multiply opacity-60 group-hover:opacity-30 transition duration-500"></div>
                    @else
                        <img src="{{ $fallback }}" alt="{{ $post->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/70 to-zinc-900/20"></div>
                        <div class="absolute inset-0 bg-primary/10 group-hover:bg-primary/0 transition duration-500"></div>
                    @endif
                    <div class="relative">
                        <div class="text-xs tracking-widest text-white/60">0{{ $loop->iteration }} — {{ strtoupper($post->published_at?->format('d M Y') ?? 'RECHERCHE') }}</div>
                        <h3 class="mt-3 text-xl font-black leading-tight text-white drop-shadow line-clamp-2">{{ $post->title }}</h3>
                    </div>
                    <div class="relative">
                        <p class="text-sm leading-relaxed text-zinc-200 line-clamp-3">{{ $post->excerpt ?: 'Publication SIBEA-CI — recherche et actualités.' }}</p>
                        <div class="mt-4 inline-flex items-center gap-2 text-xs font-bold tracking-widest text-white group-hover:gap-3 transition-all">DÉCOUVRIR <span class="transition-transform group-hover:translate-x-1">→</span></div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 border border-dashed p-10 text-center text-sm tracking-widest text-zinc-500">AUCUNE PUBLICATION</div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </div>
</section>
