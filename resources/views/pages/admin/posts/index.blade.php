<?php

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Actualités')] class extends Component {
    use WithPagination;

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
        return view('pages.admin.posts.index', ['posts' => Post::latest()->paginate(10)]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex justify-between mb-4">
        <flux:heading size="xl">Actualités / Blog</flux:heading>
        @can('posts.create')<flux:button :href="route('admin.posts.create')" wire:navigate variant="primary" icon="plus">Nouvel article</flux:button>@endcan
    </div>
    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif
    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50"><tr><th class="px-3 py-2">Titre</th><th class="px-3 py-2">Publié</th><th class="px-3 py-2">Actions</th></tr></thead>
            <tbody>
                @foreach($posts as $post)
                    <tr class="border-t">
                        <td class="px-3 py-2"><div class="font-medium">{{ $post->title }}</div><div class="text-xs text-zinc-500">{{ $post->slug }}</div></td>
                        <td class="px-3 py-2"><flux:badge :color="$post->is_published ? 'emerald' : 'zinc'" size="sm">{{ $post->is_published ? 'Oui' : 'Non' }}</flux:badge></td>
                        <td class="px-3 py-2 flex gap-1">
                            @can('posts.update')<flux:button size="xs" variant="ghost" :href="route('admin.posts.edit', $post)" wire:navigate>Éditer</flux:button><flux:button size="xs" variant="ghost" wire:click="toggle({{ $post->id }})">Toggle</flux:button>@endcan
                            @can('posts.delete')<flux:button size="xs" variant="ghost" wire:click="delete({{ $post->id }})" wire:confirm="Supprimer ?">Supprimer</flux:button>@endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</section>
