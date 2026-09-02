<?php

use App\Models\Subscriber;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Newsletter')] class extends Component {
    use WithPagination;

    public function delete(int $id): void
    {
        $this->authorize('subscribers.delete');
        Subscriber::findOrFail($id)->delete();
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('subscribers.view');
        return view('pages.admin.subscribers.index', ['subscribers' => Subscriber::latest()->paginate(20)]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex justify-between mb-4">
        <flux:heading size="xl">Newsletter — Abonnés</flux:heading>
        <flux:button :href="route('admin.subscribers.export')" variant="ghost" icon="arrow-down-tray">Exporter CSV</flux:button>
    </div>
    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50"><tr><th class="px-3 py-2">Email</th><th class="px-3 py-2">Nom</th><th class="px-3 py-2">Date</th><th class="px-3 py-2">Actions</th></tr></thead>
            <tbody>
                @foreach($subscribers as $s)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $s->email }}</td>
                        <td class="px-3 py-2">{{ $s->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs">{{ $s->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2"><flux:button size="xs" variant="ghost" wire:click="delete({{ $s->id }})" wire:confirm="Supprimer ?">Supprimer</flux:button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $subscribers->links() }}</div>
</section>
