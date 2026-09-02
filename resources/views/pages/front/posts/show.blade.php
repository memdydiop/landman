<?php

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.front')] class extends Component {
    public Post $post;

    public function mount(Post $post): void
    {
        abort_unless($post->is_published, 404);
        $this->post = $post;
    }
}; ?>

<section class="bg-zinc-100/60 min-h-screen pb-16">
    <!-- Fil d'Ariane Technique -->
    <div class="border-b border-zinc-200 bg-white py-3">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-xs font-mono lg:px-8">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-zinc-500 hover:text-amber-600 transition">ACCUEIL</a> 
                <span class="text-zinc-300">/</span>
                <a href="{{ route('front.posts.index') }}" class="text-zinc-500 hover:text-amber-600 transition">ACTUALITÉS</a> 
                <span class="text-zinc-300">/</span>
                <span class="font-bold text-zinc-900 uppercase truncate max-w-[200px] sm:max-w-none">{{ $post->title }}</span>
            </div>
            <a href="{{ route('front.posts.index') }}" class="hidden sm:inline-flex text-xs font-bold text-zinc-600 hover:text-amber-600 transition">
                ← RETOUR AUX ACTUALITÉS
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-8 lg:px-8">
        
        <!-- En-tête Fiche d'Information / Article -->
        <div class="rounded-2xl bg-zinc-900 p-6 sm:p-8 text-white shadow-xl">
            <div class="flex flex-wrap items-center gap-2 font-mono text-xs">
                <span class="rounded bg-amber-500 px-2 py-0.5 font-black text-zinc-950 uppercase">
                    BULLETIN TECHNIQUE
                </span>
                <span class="text-zinc-400">
                    PUBLIÉ LE {{ $post->published_at?->format('d/m/Y') }}
                </span>
            </div>

            <h1 class="mt-4 text-2xl font-black uppercase text-white sm:text-4xl leading-tight tracking-tight">
                {{ $post->title }}
            </h1>

            @if($post->excerpt)
                <p class="mt-4 text-sm sm:text-base leading-relaxed text-zinc-300 border-l-2 border-amber-500 pl-4">
                    {{ $post->excerpt }}
                </p>
            @endif
        </div>

        <!-- Visuel Principal & Contenu Article -->
        <div class="mt-8 space-y-8">
            @if($post->cover_path && Storage::disk('public')->exists($post->cover_path))
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-900 shadow-md">
                    <img src="{{ Storage::disk('public')->url($post->cover_path) }}" alt="{{ $post->title }}" class="w-full object-cover max-h-[480px]" loading="eager" />
                </div>
            @endif

            <!-- Corps du Texte -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-10 shadow-sm">
                <div class="prose prose-zinc max-w-none text-zinc-800 text-sm sm:text-base leading-relaxed">
                    <p class="whitespace-pre-line">{{ $post->content }}</p>
                </div>

                <!-- Call To Action Bas de Page -->
                <div class="mt-10 border-t border-zinc-200 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="font-bold text-xs uppercase text-zinc-900">Vous avez un projet immobilier ou foncier ?</div>
                        <div class="text-xs text-zinc-500">Nos équipes techniques sont à votre disposition pour vous accompagner.</div>
                    </div>
                    <a href="{{ route('front.contact') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-xs font-black tracking-wider text-zinc-950 hover:bg-amber-400 transition uppercase">
                        DEMANDER UNE ÉTUDE TECHNIQUE →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
