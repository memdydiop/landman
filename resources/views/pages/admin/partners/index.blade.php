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
    <div class="flex items-center justify-between mb-4">
        <div>
            <flux:heading size="xl">Partenaires</flux:heading>
            <flux:text>Logos vitrine — Home / Partenaires</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="$set('showCreate', true)">Nouveau partenaire</flux:button>
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

    <div class="overflow-x-auto rounded-2xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50"><tr><th class="px-3 py-2">Nom</th><th class="px-3 py-2">Logo</th><th class="px-3 py-2">Publié</th><th class="px-3 py-2">Actions</th></tr></thead>
            <tbody>
                @foreach($partners as $p)
                    <tr class="border-t">
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
    <div class="mt-4">{{ $partners->links() }}</div>
</section>
