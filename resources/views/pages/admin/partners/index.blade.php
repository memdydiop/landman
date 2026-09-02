<?php

use App\Models\Partner;
use App\Services\ImageService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Partenaires')] class extends Component {
    use WithPagination, WithFileUploads;

    public string $name = '';
    public string $url = '';
    public $logo;
    public bool $showCreate = false;
    public string $view = 'grid';

    public function save(): void
    {
        $this->authorize('partners.manage');
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = ['name' => $validated['name'], 'url' => $validated['url'] ?? null];
        if ($this->logo) {
            $data['logo_path'] = ImageService::storeOptimized($this->logo, 'partners');
        }

        Partner::create($data);
        $this->reset(['name', 'url', 'logo', 'showCreate']);
        session()->flash('success', 'Partenaire ajouté.');
    }

    public function delete(int $id): void
    {
        $this->authorize('partners.manage');
        Partner::findOrFail($id)->delete();
    }

    public function toggle(int $id): void
    {
        $this->authorize('partners.manage');
        $p = Partner::findOrFail($id);
        $p->update(['is_published' => ! $p->is_published]);
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('partners.manage');
        return view('pages.admin.partners.index', [
            'partners' => Partner::latest()->paginate(10),
        ]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <flux:heading size="xl">Partenaires — Logos</flux:heading>
            <flux:text>{{ $partners->total() }} partenaire(s) · vitrine Home</flux:text>
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
                <flux:heading size="lg">Nouveau partenaire</flux:heading>
                <flux:text>Logo 2Mo max — s'affiche en page d'accueil</flux:text>
            </div>
            <flux:input wire:model="name" label="Nom *" placeholder="NSIA Banque" required />
            <flux:input wire:model="url" label="URL" placeholder="https://..." />
            <flux:input type="file" wire:model="logo" label="Logo (2Mo)" accept="image/*" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Ajouter</flux:button>
            </div>
        </form>
    </flux:modal>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    @if($view === 'grid')
        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
            @forelse($partners as $p)
                <div class="group rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-4 hover:shadow transition">
                    <div class="aspect-[16/9] bg-zinc-50 rounded-xl flex items-center justify-center overflow-hidden">
                        @if($p->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->logo_path) }}" class="max-h-16 object-contain" />@else<flux:icon.building-storefront class="size-8 text-zinc-300" />@endif
                    </div>
                    <div class="mt-3">
                        <div class="truncate text-sm font-bold">{{ $p->name }}</div>
                        <div class="truncate text-xs text-zinc-500">{{ $p->url ?? '—' }}</div>
                        <div class="mt-2 flex items-center gap-1"><flux:badge :color="$p->is_published ? 'emerald' : 'zinc'" size="sm">{{ $p->is_published ? 'Publié' : 'Brouillon' }}</flux:badge></div>
                    </div>
                    <div class="mt-3 flex gap-1">
                        <flux:button size="xs" variant="ghost" wire:click="toggle({{ $p->id }})">Toggle</flux:button>
                        <flux:button size="xs" variant="ghost" wire:click="delete({{ $p->id }})" wire:confirm="Supprimer ?" class="text-red-600">Suppr</flux:button>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm text-zinc-500">Aucun partenaire.</div>
            @endforelse
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800"><tr><th class="px-3 py-2">Nom</th><th class="px-3 py-2">Logo</th><th class="px-3 py-2">Publié</th><th class="px-3 py-2">Actions</th></tr></thead>
                <tbody>
                    @foreach($partners as $p)
                        <tr class="border-t border-zinc-100 dark:border-zinc-800">
                            <td class="px-3 py-2">{{ $p->name }}<div class="text-xs text-zinc-500">{{ $p->url }}</div></td>
                            <td class="px-3 py-2">@if($p->logo_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->logo_path) }}" class="h-8" />@else — @endif</td>
                            <td class="px-3 py-2"><flux:badge :color="$p->is_published ? 'emerald' : 'zinc'" size="sm">{{ $p->is_published ? 'Oui' : 'Non' }}</flux:badge></td>
                            <td class="px-3 py-2 flex gap-1">
                                <flux:button size="xs" variant="ghost" wire:click="toggle({{ $p->id }})">Toggle</flux:button>
                                <flux:button size="xs" variant="ghost" wire:click="delete({{ $p->id }})" wire:confirm="Supprimer ?">Supprimer</flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <div class="mt-4">{{ $partners->links() }}</div>
</section>
