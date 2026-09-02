<?php

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Actualités')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $view = 'grid';

    public function updatingSearch(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        $this->authorize('posts.delete');
        Post::findOrFail($id)->delete();
        session()->flash('success', 'Article supprimé.');
    }

    public function toggle(int $id): void
    {
        $this->authorize('posts.update');
        $p = Post::findOrFail($id);
        $p->update(['is_published' => ! $p->is_published, 'published_at' => ! $p->is_published ? now() : null]);
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('posts.view');
        $posts = Post::query()
            ->when($this->search, fn($q) => $q->where('title', 'like', '%'.$this->search.'%')->orWhere('slug', 'like', '%'.$this->search.'%'))
            ->latest()->paginate(12);
        return view('pages.admin.posts.index', ['posts' => $posts]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <flux:heading size="xl">Actualités — Blog SIBEA-CI</flux:heading>
            <flux:text>{{ $posts->total() }} article(s) · actus chantier & foncier</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button.group>
                <flux:button :variant="$view==='grid'?'primary':'ghost'" wire:click="$set('view','grid')" icon="squares-2x2" size="sm">Grille</flux:button>
                <flux:button :variant="$view==='list'?'primary':'ghost'" wire:click="$set('view','list')" icon="list-bullet" size="sm">Liste</flux:button>
            </flux:button.group>
            @can('posts.create')<flux:button :href="route('admin.posts.create')" wire:navigate variant="primary" icon="plus">Nouvel article</flux:button>@endcan
        </div>
    </div>

    <div class="flex gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher titre..." icon="magnifying-glass" class="max-w-xs" />
    </div>
    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif
    @if($view === 'grid')
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($posts as $post)
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 hover:shadow-lg transition">
                    <div class="aspect-[16/9] bg-zinc-100 relative">
                        @if($post->cover_path)<img src="{{ Storage::disk('public')->url($post->cover_path) }}" class="size-full object-cover" />@else<img src="https://images.unsplash.com/photo-1495020689067-958852a7765e?w=600&q=80&auto=format&fit=crop" class="size-full object-cover" />@endif
                        <div class="absolute left-3 top-3"><flux:badge :color="$post->is_published ? 'emerald' : 'zinc'" size="sm">{{ $post->is_published ? 'Publié' : 'Brouillon' }}</flux:badge></div>
                    </div>
                    <div class="p-4">
                        <div class="truncate text-sm font-bold">{{ $post->title }}</div>
                        <div class="truncate text-xs text-zinc-500">{{ $post->slug }} · {{ $post->created_at->format('d/m/Y') }}</div>
                        @if($post->excerpt)<div class="mt-1 line-clamp-2 text-xs text-zinc-600">{{ Str::limit($post->excerpt, 80) }}</div>@endif
                        <div class="mt-3 flex gap-1">
                            @can('posts.update')<flux:button size="xs" variant="ghost" :href="route('admin.posts.edit', $post)" wire:navigate>Éditer</flux:button><flux:button size="xs" variant="ghost" wire:click="toggle({{ $post->id }})">Toggle</flux:button>@endcan
                            @can('posts.delete')<flux:button size="xs" variant="ghost" wire:click="delete({{ $post->id }})" wire:confirm="Supprimer ?" class="text-red-600">Suppr</flux:button>@endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm text-zinc-500">Aucun article.</div>
            @endforelse
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50"><tr><th class="px-4 py-3">Article</th><th class="px-4 py-3">Publié</th><th class="px-4 py-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr class="border-t hover:bg-zinc-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 overflow-hidden rounded-lg bg-zinc-100 shrink-0">@if($post->cover_path)<img src="{{ Storage::disk('public')->url($post->cover_path) }}" class="size-full object-cover" />@else<div class="flex size-full items-center justify-center text-zinc-400"><flux:icon.photo class="size-5" /></div>@endif</div>
                                    <div class="min-w-0"><div class="truncate font-medium">{{ $post->title }}</div><div class="truncate text-xs text-zinc-500">{{ $post->slug }}</div></div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><flux:badge :color="$post->is_published ? 'emerald' : 'zinc'" size="sm">{{ $post->is_published ? 'Oui' : 'Non' }}</flux:badge></td>
                            <td class="px-4 py-3 flex gap-1">@can('posts.update')<flux:button size="xs" variant="ghost" :href="route('admin.posts.edit', $post)" wire:navigate>Éditer</flux:button>@endcan</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-zinc-500">Aucun article.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
    <div class="mt-4">{{ $posts->links() }}</div>
</section>
