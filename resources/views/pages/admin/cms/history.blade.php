<?php

use App\Models\SiteSettingHistory;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('CMS — Historique')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $key = '';

    public function mount(): void
    {
        $this->authorize('cms.manage');
    }

    public function render(): \Illuminate\View\View
    {
        $histories = SiteSettingHistory::query()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('key', 'like', '%'.$s.'%');
            })
            ->when($this->key, fn ($q) => $q->where('key', $this->key))
            ->latest()
            ->paginate(20);

        $keys = SiteSettingHistory::select('key')->distinct()->orderBy('key')->pluck('key');

        return view('pages.admin.cms.history', [
            'histories' => $histories,
            'keys' => $keys,
        ]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="text-xs tracking-widest text-zinc-500"><a href="{{ route('admin.cms.index') }}" wire:navigate class="hover:text-primary">CMS</a> › Historique</div>
            <flux:heading size="xl">Historique des modifications CMS</div>
            <flux:text>Versionning automatique, diffs, restauration 1-clic, audit utilisateur. Purge 1 an.</flux:text>
        </div>
        <flux:button :href="route('admin.cms.index')" wire:navigate variant="ghost">← Retour CMS</flux:button>
    </div>

    @if(session('success')) <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    <div class="mt-6 flex flex-wrap gap-3">
        <form wire:submit.prevent="set('search', '')" class="flex gap-2">
            <flux:input wire:model="search" placeholder="Rechercher par clé..." style="width: 280px" />
            <flux:button variant="ghost" type="submit" icon="magnifying-glass">Filtrer</flux:button>
        </form>
        <form wire:submit.prevent="set('key', '')" class="flex gap-2">
            <select wire:model="key" class="border border-zinc-200 rounded-lg px-3 py-2 text-sm">
                <option value="">Toutes les clés</option>
                @foreach($keys as $k) <option value="{{ $k }}">{{ $k }}</option> @endforeach
            </select>
        </form>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-200">
                    <th class="text-left p-3 font-bold">Clé</th>
                    <th class="text-left p-3 font-bold">Action</th>
                    <th class="text-left p-3 font-bold">Utilisateur</th>
                    <th class="text-left p-3 font-bold">Date</th>
                    <th class="text-left p-3 font-bold">Diff</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $h)
                    <tr class="border-b border-zinc-100 hover:bg-zinc-50">
                        <td class="p-3 font-mono text-xs">{{ $h->key }}</td>
                        <td class="p-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $h->action === 'created' ? 'bg-emerald-100 text-emerald-700' : ($h->action === 'updated' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($h->action) }}
                            </span>
                        </td>
                        <td class="p-3 text-zinc-600">
                            {{ $h->user?->name ?? 'Système' }}
                        </td>
                        <td class="p-3 text-zinc-600">
                            {{ $h->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="p-3">
                            @if($h->old_value && $h->new_value)
                                <span class="text-primary text-xs">Diff disponible</span>
                            @else
                                <span class="text-zinc-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-zinc-500">Aucun historique</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $histories->links() }}</div>
</section>