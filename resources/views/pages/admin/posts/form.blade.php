<?php

use App\Models\Post;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public ?Post $post = null;
    public string $title = '';
    public string $slug = '';
    public string $excerpt = '';
    public string $content = '';
    public bool $is_published = false;
    public $cover;
    public ?string $cover_existing = null;

    public function mount(?Post $post = null): void
    {
        if ($post && $post->exists) {
            $this->authorize('posts.update');
            $this->post = $post;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->excerpt = $post->excerpt ?? '';
            $this->content = $post->content ?? '';
            $this->is_published = $post->is_published;
            $this->cover_existing = $post->cover_path;
        } else {
            $this->authorize('posts.create');
        }
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->post) $this->slug = Str::slug($value);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($this->post?->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:20000'],
            'is_published' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:5120'],
        ]);

        $data = [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'] ?? null,
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? ($this->post?->published_at ?? now()) : null,
        ];
        if ($this->cover) $data['cover_path'] = ImageService::storeOptimized($this->cover, 'posts');

        if ($this->post) {
            $this->post->update($data);
        } else {
            Post::create($data);
        }

        session()->flash('success', 'Article enregistré.');
        $this->redirect(route('admin.posts.index'), navigate: true);
    }
}; ?>

<section class="w-full p-6 max-w-3xl">
    <flux:heading size="xl" class="mb-4">{{ $post ? 'Éditer' : 'Nouvel article' }}</flux:heading>
    <form wire:submit="save" class="space-y-4">
        <flux:input wire:model="title" label="Titre *" />
        <flux:input wire:model="slug" label="Slug *" />
        <flux:input wire:model="excerpt" label="Extrait" />
        <flux:textarea wire:model="content" label="Contenu" rows="8" />
        <flux:checkbox wire:model="is_published" label="Publié" />
        <flux:input type="file" wire:model="cover" label="Couverture (5Mo)" accept="image/*" />
        @if($cover_existing) <div class="text-xs text-zinc-500">Actuel: {{ $cover_existing }}</div> @endif
        <div class="flex gap-2"><flux:button type="submit" variant="primary">Enregistrer</flux:button><flux:button :href="route('admin.posts.index')" wire:navigate variant="ghost">Annuler</flux:button></div>
    </form>
</section>
