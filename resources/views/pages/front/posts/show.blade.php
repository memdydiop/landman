<?php

use App\Models\Post;
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

<section class="bg-white">
    <div class="bg-zinc-50 py-8">
        <div class="mx-auto max-w-3xl px-4 lg:px-8">
            <a href="{{ route('front.posts.index') }}" class="text-xs tracking-widest text-zinc-500 hover:text-[#003366]">← RECHERCHE</a>
            <div class="mt-4 border-l-4 border-white/30 pl-6">
                <div class="text-xs tracking-[0.3em] text-zinc-500">{{ $post->published_at?->format('d M Y') }} — PUBLICATION</div>
                <h1 class="mt-3 text-3xl font-light leading-tight">{{ $post->title }}</h1>
                <p class="mt-3 text-sm leading-relaxed text-zinc-600">{{ $post->excerpt }}</p>
            </div>
        </div>
    </div>
    <div class="mx-auto max-w-3xl px-4 py-10 lg:px-8">
        @if($post->cover_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->cover_path) }}" alt="" class="w-full rounded-xl object-cover" loading="lazy" />
        @endif
        <div class="prose prose-zinc mt-8 max-w-none text-sm leading-relaxed">
            <p class="whitespace-pre-line">{{ $post->content }}</p>
        </div>
        <div class="mt-8 border-t border-zinc-200 pt-6">
            <a href="{{ route('front.contact') }}" class="text-xs font-bold tracking-widest text-[#003366] hover:underline">DEMANDER UNE ÉTUDE →</a>
        </div>
    </div>
</section>
