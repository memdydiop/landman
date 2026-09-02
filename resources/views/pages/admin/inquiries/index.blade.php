<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Prospects')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $typeFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function updateStatus(int $id, string $status): void
    {
        $this->authorize('inquiries.update');
        $validated = validator(['status' => $status], ['status' => ['required', Rule::in(array_column(InquiryStatus::cases(), 'value'))]])->validate();
        Inquiry::findOrFail($id)->update(['status' => $validated['status']]);
        session()->flash('success', 'Statut mis à jour.');
    }

    public function delete(int $id): void
    {
        $this->authorize('inquiries.delete');
        Inquiry::findOrFail($id)->delete();
        session()->flash('success', 'Prospect supprimé.');
    }

    public function render(): \Illuminate\View\View
    {
        $this->authorize('inquiries.view');
        $inquiries = Inquiry::with(['program', 'plot'])
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where(fn ($qq) => $qq->where('name', 'like', '%'.$s.'%')->orWhere('email', 'like', '%'.$s.'%'));
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('inquiry_type', $this->typeFilter))
            ->latest()
            ->paginate(15);

        return view('pages.admin.inquiries.index', ['inquiries' => $inquiries]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Prospects — Demandes entrantes</flux:heading>
            <flux:text>Filtrables par statut (Nouveau / En cours / Traité) — {{ $inquiries->total() }} demande(s)</flux:text>
        </div>
        @can('inquiries.export')
            <flux:button :href="route('admin.inquiries.export', ['status' => $statusFilter, 'type' => $typeFilter])" icon="arrow-down-tray" variant="primary">Exporter CSV</flux:button>
        @endcan
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Nom ou email..." class="max-w-xs" />
        <flux:select wire:model.live="statusFilter" placeholder="Statut" class="max-w-[160px]">
            <flux:select.option value="">Tous statuts</flux:select.option>
            @foreach(InquiryStatus::cases() as $s) <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option> @endforeach
        </flux:select>
        <flux:select wire:model.live="typeFilter" placeholder="Type" class="max-w-[180px]">
            <flux:select.option value="">Tous types</flux:select.option>
            @foreach(InquiryType::cases() as $t) <flux:select.option value="{{ $t->value }}">{{ $t->label() }}</flux:select.option> @endforeach
        </flux:select>
    </div>

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50">
                <tr class="text-left">
                    <th class="px-3 py-3">Date</th>
                    <th class="px-3 py-3">Prospect</th>
                    <th class="px-3 py-3">Type</th>
                    <th class="px-3 py-3">Lot / Programme</th>
                    <th class="px-3 py-3">Message</th>
                    <th class="px-3 py-3">Statut</th>
                    <th class="px-3 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inquiries as $inq)
                    <tr class="border-t border-zinc-100">
                        <td class="px-3 py-2 text-xs text-zinc-500">{{ $inq->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2">
                            <div class="font-medium">{{ $inq->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $inq->email }} · {{ $inq->phone ?? '—' }}</div>
                            @if($inq->service_type) <div class="text-xs"><flux:badge size="sm" color="zinc">{{ $inq->service_type->label() }}</flux:badge></div> @endif
                        </td>
                        <td class="px-3 py-2"><flux:badge size="sm">{{ $inq->inquiry_type->label() }}</flux:badge></td>
                        <td class="px-3 py-2 text-xs">
                            {{ $inq->program?->title ?? '—' }}
                            @if($inq->plot) <div class="font-mono">{{ $inq->plot->reference }}</div> @endif
                        </td>
                        <td class="px-3 py-2 max-w-[280px]"><div class="truncate text-xs text-zinc-600" title="{{ $inq->message }}">{{ \Illuminate\Support\Str::limit($inq->message ?? '', 80) }}</div></td>
                        <td class="px-3 py-2"><flux:badge :color="$inq->status->badgeColor()" size="sm">{{ $inq->status->label() }}</flux:badge></td>
                        <td class="px-3 py-2">
                            <div class="flex flex-wrap gap-1">
                                @can('inquiries.update')
                                    <flux:select wire:change="updateStatus({{ $inq->id }}, $event.target.value)" class="w-28" size="sm">
                                        @foreach(InquiryStatus::cases() as $s)
                                            <flux:select.option value="{{ $s->value }}" :selected="$inq->status->value === $s->value">{{ $s->label() }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                @endcan
                                @can('inquiries.delete')
                                    <flux:button size="xs" variant="ghost" wire:click="delete({{ $inq->id }})" wire:confirm="Supprimer ce prospect ?">Supprimer</flux:button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-zinc-500">Aucune demande.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $inquiries->links() }}</div>
</section>
