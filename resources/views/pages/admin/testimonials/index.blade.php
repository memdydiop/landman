<?php

use App\Models\Testimonial;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Témoignages')] class extends Component {
    use WithPagination;

    public string $name = '';
    public string $role = '';
    public string $content = '';
    public int $rating = 5;
    public bool $showCreate = false;
    public bool $showEdit = false;
    public ?int $editingId = null;
    public string $view = 'grid';

    public function save(): void
    {
        $this->authorize('testimonials.manage');
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        Testimonial::create($validated);
        $this->reset(['name', 'role', 'content', 'rating', 'showCreate']);
        $this->rating = 5;
        session()->flash('success', 'Témoignage ajouté.');
    }

    public function startEdit(int $id): void
    {
        $this->authorize('testimonials.manage');
        $t = Testimonial::findOrFail($id);
        $this->editingId = $t->id;
        $this->name = $t->name;
        $this->role = $t->role ?? '';
        $this->content = $t->content;
        $this->rating = $t->rating;
        $this->showEdit = true;
        $this->showCreate = false;
    }

    public function update(): void
    {
        $this->authorize('testimonials.manage');
        if (!$this->editingId) return;
        $t = Testimonial::findOrFail($this->editingId);
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);
        $t->update($validated);
        $this->reset(['name', 'role', 'content', 'rating', 'showEdit', 'editingId']);
        $this->rating = 5;
        session()->flash('success', 'Témoignage mis à jour.');
    }

    public function delete(int $id): void
    {
        $this->authorize('testimonials.manage');
        Testimonial::findOrFail($id)->delete();
    }

    public function toggle(int $id): void
    {
        $this->authorize('testimonials.manage');
        $t = Testimonial::findOrFail($id);
        $t->update(['is_published' => ! $t->is_published]);
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('testimonials.manage');
        return view('pages.admin.testimonials.index', [
            'testimonials' => Testimonial::latest()->paginate(10),
        ]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <flux:heading size="xl">Témoignages — Avis</flux:heading>
            <flux:text>{{ $testimonials->total() }} témoignage(s) · Home</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button.group>
                <flux:button :variant="$view==='grid'?'primary':'ghost'" wire:click="$set('view','grid')" icon="squares-2x2" size="sm">Grille</flux:button>
                <flux:button :variant="$view==='list'?'primary':'ghost'" wire:click="$set('view','list')" icon="list-bullet" size="sm">Liste</flux:button>
            </flux:button.group>
            <flux:button variant="primary" icon="plus" wire:click="$set('showCreate', true)">Nouveau</flux:button>
        </div>
    </div>

    <flux:modal wire:model="showCreate" class="md:w-[560px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Nouveau témoignage</flux:heading>
                <flux:text>5 étoiles max — affiché en page d'accueil</flux:text>
            </div>
            <flux:input wire:model="name" label="Nom *" placeholder="Kouadio Jean" required />
            <flux:input wire:model="role" label="Rôle" placeholder="Client, Cocody" />
            <flux:select wire:model="rating" label="Note *">
                @for($i=1;$i<=5;$i++)<flux:select.option value="{{ $i }}">{{ $i }}/5</flux:select.option>@endfor
            </flux:select>
            <flux:textarea wire:model="content" label="Contenu *" rows="4" placeholder="Excellent service SIBEA-CI..." required />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Ajouter</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showEdit" class="md:w-[560px]">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">Éditer témoignage</flux:heading>
                <flux:text>Modifie nom, rôle ou avis</flux:text>
            </div>
            <flux:input wire:model="name" label="Nom *" required />
            <flux:input wire:model="role" label="Rôle" />
            <flux:select wire:model="rating" label="Note *">
                @for($i=1;$i<=5;$i++)<flux:select.option value="{{ $i }}">{{ $i }}/5</flux:select.option>@endfor
            </flux:select>
            <flux:textarea wire:model="content" label="Contenu *" rows="4" required />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Mettre à jour</flux:button>
            </div>
        </form>
    </flux:modal>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    @if($view === 'grid')
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($testimonials as $t)
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 hover:shadow transition">
                    <div class="flex items-center gap-3">
                        <flux:avatar :name="$t->name" size="lg" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-bold">{{ $t->name }}</div>
                            <div class="truncate text-xs text-zinc-500">{{ $t->role ?? '—' }}</div>
                            <div class="text-xs text-amber-600">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5-$t->rating) }} {{ $t->rating }}/5</div>
                        </div>
                        <flux:badge :color="$t->is_published ? 'emerald' : 'zinc'" size="sm">{{ $t->is_published ? 'Publié' : 'Brouillon' }}</flux:badge>
                    </div>
                    <div class="mt-3 rounded-xl bg-zinc-50 p-3 text-sm text-zinc-700 line-clamp-3">{{ $t->content }}</div>
                    <div class="mt-3 flex gap-1">
                        <flux:button size="xs" variant="ghost" wire:click="startEdit({{ $t->id }})">Éditer</flux:button>
                        <flux:button size="xs" variant="ghost" wire:click="toggle({{ $t->id }})">Toggle</flux:button>
                        <flux:button size="xs" variant="ghost" wire:click="delete({{ $t->id }})" wire:confirm="Supprimer ?" class="text-red-600">Suppr</flux:button>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm text-zinc-500">Aucun témoignage.</div>
            @endforelse
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50"><tr><th class="px-3 py-2">Nom</th><th class="px-3 py-2">Avis</th><th class="px-3 py-2">Note</th><th class="px-3 py-2">Publié</th><th class="px-3 py-2">Actions</th></tr></thead>
                <tbody>
                    @foreach($testimonials as $t)
                        <tr class="border-t border-zinc-100">
                            <td class="px-3 py-2"><div class="font-medium">{{ $t->name }}</div><div class="text-xs text-zinc-500">{{ $t->role }}</div></td>
                            <td class="px-3 py-2 max-w-xs truncate">{{ $t->content }}</td>
                            <td class="px-3 py-2">{{ $t->rating }}/5</td>
                            <td class="px-3 py-2"><flux:badge :color="$t->is_published ? 'emerald' : 'zinc'" size="sm">{{ $t->is_published ? 'Oui' : 'Non' }}</flux:badge></td>
                            <td class="px-3 py-2 flex gap-1">
                                <flux:button size="xs" variant="ghost" wire:click="startEdit({{ $t->id }})">Éditer</flux:button>
                                <flux:button size="xs" variant="ghost" wire:click="toggle({{ $t->id }})">Toggle</flux:button>
                                <flux:button size="xs" variant="ghost" wire:click="delete({{ $t->id }})" wire:confirm="Supprimer ?">Supprimer</flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <div class="mt-4">{{ $testimonials->links() }}</div>
</section>
