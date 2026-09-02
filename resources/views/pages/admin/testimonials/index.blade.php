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
        $this->reset(['name', 'role', 'content', 'rating']);
        $this->rating = 5;
        session()->flash('success', 'Témoignage ajouté.');
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
    <flux:heading size="xl">Témoignages</flux:heading>
    <flux:text class="mb-4">Gérez les avis affichés en vitrine — Home / TÉMOIGNAGES CLIENTS</flux:text>

    <form wire:submit="save" class="mb-6 grid gap-3 md:grid-cols-4 rounded-xl border border-zinc-200 p-4">
        <flux:input wire:model="name" label="Nom *" placeholder="Kouadio Jean" />
        <flux:input wire:model="role" label="Rôle" placeholder="Client, Cocody" />
        <flux:input wire:model="rating" label="Note 1-5" type="number" />
        <flux:input wire:model="content" label="Contenu *" placeholder="Excellent..." />
        <div class="flex items-end"><flux:button type="submit" variant="primary">Ajouter</flux:button></div>
    </form>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50"><tr><th class="px-3 py-2">Nom</th><th class="px-3 py-2">Avis</th><th class="px-3 py-2">Note</th><th class="px-3 py-2">Publié</th><th class="px-3 py-2">Actions</th></tr></thead>
            <tbody>
                @foreach($testimonials as $t)
                    <tr class="border-t">
                        <td class="px-3 py-2"><div class="font-medium">{{ $t->name }}</div><div class="text-xs text-zinc-500">{{ $t->role }}</div></td>
                        <td class="px-3 py-2 max-w-xs truncate">{{ $t->content }}</td>
                        <td class="px-3 py-2">{{ $t->rating }}/5</td>
                        <td class="px-3 py-2"><flux:badge :color="$t->is_published ? 'emerald' : 'zinc'" size="sm">{{ $t->is_published ? 'Oui' : 'Non' }}</flux:badge></td>
                        <td class="px-3 py-2 flex gap-1">
                            <flux:button size="xs" variant="ghost" wire:click="toggle({{ $t->id }})">Toggle</flux:button>
                            <flux:button size="xs" variant="ghost" wire:click="delete({{ $t->id }})" wire:confirm="Supprimer ?">Supprimer</flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $testimonials->links() }}</div>
</section>
