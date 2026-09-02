<?php

use App\Models\Subscriber;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Newsletter')] class extends Component {
    use WithPagination;

    public bool $showSend = false;
    public string $subject = '';
    public string $body = '';

    public function delete(int $id): void
    {
        $this->authorize('subscribers.delete');
        Subscriber::findOrFail($id)->delete();
    }

    public function sendNewsletter(): void
    {
        $this->authorize('subscribers.view');
        $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);
        $count = Subscriber::count();
        if ($count === 0) {
            session()->flash('error', 'Aucun abonné.');
            return;
        }
        // Queue 1 mail par abonné (Managed Queues scale-to-zero)
        Subscriber::chunkById(100, function ($chunk) {
            foreach ($chunk as $sub) {
                \Illuminate\Support\Facades\Mail::to($sub->email)->queue(new \App\Mail\NewsletterCampaign($this->subject, $this->body));
            }
        });
        $this->reset(['showSend', 'subject', 'body']);
        session()->flash('success', "Campagne en file d'attente : {$count} email(s) en cours d'envoi via Managed Queues.");
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('subscribers.view');
        return view('pages.admin.subscribers.index', ['subscribers' => Subscriber::latest()->paginate(20)]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap justify-between gap-3 mb-4">
        <div>
            <flux:heading size="xl">Newsletter — Abonnés</flux:heading>
            <flux:text>{{ \App\Models\Subscriber::count() }} abonné(s) · envoi masse via SIBEA-CI</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button variant="primary" icon="paper-airplane" wire:click="$set('showSend', true)">Nouvelle campagne</flux:button>
            <flux:button :href="route('admin.subscribers.export')" variant="ghost" icon="arrow-down-tray">Exporter CSV</flux:button>
        </div>
    </div>

    <flux:modal wire:model="showSend" class="md:w-[640px]">
        <form wire:submit="sendNewsletter" class="space-y-6">
            <div>
                <flux:heading size="lg">Campagne newsletter</flux:heading>
                <flux:text>Envoi via Managed Queues (scale-to-zero, 0€ vide) — {{ \App\Models\Subscriber::count() }} destinataires</flux:text>
            </div>
            <flux:input wire:model="subject" label="Objet *" placeholder="Ex: Nouveau lotissement Bingerville Abatta" required />
            <flux:textarea wire:model="body" label="Message (Markdown) *" rows="8" placeholder="Bonjour,\nDécouvrez notre nouveau lotissement..." required />
            @error('subject') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
            @error('body') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
            <div class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800">L'envoi est mis en file d'attente (queue database) — Cloud l'exécute en arrière-plan. Vérifiez `MAIL_MAILER` en prod (`smtp` dans Cloud).</div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="paper-airplane">Envoyer à {{ \App\Models\Subscriber::count() }} abonnés</flux:button>
            </div>
        </form>
    </flux:modal>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div> @endif
    <div class="overflow-x-auto rounded-2xl border border-zinc-200">
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
