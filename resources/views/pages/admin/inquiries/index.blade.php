<?php

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use App\Models\User;
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
    public string $viewMode = 'kanban'; // kanban | list
    public ?int $editingId = null;
    public string $editingNotes = '';
    public ?string $editingNextAction = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function updateStatus(int $id, string $status): void
    {
        $this->authorize('inquiries.update');
        $validated = validator(['status' => $status], ['status' => ['required', Rule::in(array_column(InquiryStatus::cases(), 'value'))]])->validate();
        Inquiry::findOrFail($id)->update(['status' => $validated['status']]);
        session()->flash('success', 'Statut mis à jour.');
    }

    public function moveStatus(int $id, string $status): void
    {
        $this->updateStatus($id, $status);
    }

    public function assignTo(int $id, ?int $userId): void
    {
        $this->authorize('inquiries.update');
        $inquiry = Inquiry::findOrFail($id);
        $inquiry->update(['assigned_to' => $userId]);
        session()->flash('success', $userId ? 'Assigné.' : 'Désassigné.');
    }

    public function startEditNotes(int $id): void
    {
        $inq = Inquiry::findOrFail($id);
        $this->editingId = $id;
        $this->editingNotes = $inq->notes ?? '';
        $this->editingNextAction = $inq->next_action_at?->format('Y-m-d\TH:i');
    }

    public function saveNotes(): void
    {
        $this->authorize('inquiries.update');
        if (!$this->editingId) return;
        $inq = Inquiry::findOrFail($this->editingId);
        $inq->update([
            'notes' => $this->editingNotes ?: null,
            'next_action_at' => $this->editingNextAction ? \Carbon\Carbon::parse($this->editingNextAction) : null,
        ]);
        $this->editingId = null;
        $this->editingNotes = '';
        $this->editingNextAction = null;
        session()->flash('success', 'Notes enregistrées.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingNotes = '';
        $this->editingNextAction = null;
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
        $baseQuery = Inquiry::with(['program', 'plot', 'assignee'])
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where(fn ($qq) => $qq->where('name', 'like', '%'.$s.'%')->orWhere('email', 'like', '%'.$s.'%')->orWhere('phone', 'like', '%'.$s.'%'));
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('inquiry_type', $this->typeFilter));

        if ($this->viewMode === 'kanban') {
            $grouped = [];
            foreach (InquiryStatus::cases() as $status) {
                $grouped[$status->value] = (clone $baseQuery)->where('status', $status->value)->latest()->limit(50)->get();
            }
            $users = User::orderBy('name')->get(['id','name','email']);
            return view('pages.admin.inquiries.index', ['grouped' => $grouped, 'users' => $users, 'inquiries' => null]);
        }

        $inquiries = $baseQuery->latest()->paginate(15);
        $users = User::orderBy('name')->get(['id','name','email']);
        return view('pages.admin.inquiries.index', ['inquiries' => $inquiries, 'users' => $users, 'grouped' => null]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <flux:heading size="xl">Prospects — CRM</flux:heading>
            <flux:text>Filtrables par statut · Kanban par statut · Assignation & relance</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button.group>
                <flux:button :variant="$viewMode==='kanban'?'primary':'ghost'" wire:click="$set('viewMode','kanban')" icon="view-columns">Kanban</flux:button>
                <flux:button :variant="$viewMode==='list'?'primary':'ghost'" wire:click="$set('viewMode','list')" icon="list-bullet">Liste</flux:button>
            </flux:button.group>
            @can('inquiries.export')
                <flux:button :href="route('admin.inquiries.export', ['status' => $statusFilter, 'type' => $typeFilter])" icon="arrow-down-tray" variant="primary">Exporter CSV</flux:button>
            @endcan
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Nom, email ou téléphone..." class="max-w-xs" icon="magnifying-glass" />
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

    @if($viewMode === 'kanban' && $grouped)
        <div class="grid gap-4 md:grid-cols-4">
            @foreach(InquiryStatus::cases() as $colStatus)
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50/50 flex flex-col min-h-[400px]">
                    <div class="sticky top-0 z-10 rounded-t-xl border-b bg-white px-3 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <flux:badge :color="$colStatus->badgeColor()" size="sm">{{ $colStatus->label() }}</flux:badge>
                            <span class="text-xs font-semibold text-zinc-600">{{ $grouped[$colStatus->value]->count() }}</span>
                        </div>
                        <flux:badge size="sm" color="zinc">{{ $colStatus->value }}</flux:badge>
                    </div>
                    <div class="flex-1 space-y-3 p-3 overflow-y-auto">
                        @forelse($grouped[$colStatus->value] as $inq)
                            <div class="rounded-xl border bg-white p-3 shadow-sm hover:shadow transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold">{{ $inq->name }}</div>
                                        <div class="truncate text-xs text-zinc-500">{{ $inq->email }}</div>
                                        @if($inq->phone)<div class="text-xs text-zinc-500">{{ $inq->phone }}</div>@endif
                                    </div>
                                    <flux:badge size="sm">{{ $inq->inquiry_type->label() }}</flux:badge>
                                </div>
                                @if($inq->program || $inq->plot)
                                    <div class="mt-2 text-xs text-zinc-600">
                                        @if($inq->program) <div class="truncate">{{ $inq->program->title }}</div> @endif
                                        @if($inq->plot) <div class="font-mono text-[11px]">{{ $inq->plot->reference }} · {{ $inq->plot->surface_m2 }}m²</div> @endif
                                    </div>
                                @endif
                                @if($inq->message)
                                    <div class="mt-2 rounded-lg bg-zinc-50 p-2 text-xs text-zinc-600 line-clamp-2" title="{{ $inq->message }}">{{ \Illuminate\Support\Str::limit($inq->message, 100) }}</div>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-1 text-[11px] text-zinc-500">
                                    @if(!empty($inq->meta['budget_range']))<span class="rounded bg-amber-50 px-1.5 py-0.5">{{ $inq->meta['budget_range'] }}</span>@endif
                                    @if(!empty($inq->meta['location']))<span class="rounded bg-sky-50 px-1.5 py-0.5">{{ $inq->meta['location'] }}</span>@endif
                                    <span class="rounded bg-zinc-100 px-1.5 py-0.5">{{ $inq->created_at->format('d/m H:i') }}</span>
                                </div>

                                {{-- Assignation --}}
                                <div class="mt-3">
                                    <label class="text-[11px] font-medium text-zinc-600">Assigné à</label>
                                    <flux:select size="sm" wire:change="assignTo({{ $inq->id }}, $event.target.value ? parseInt($event.target.value) : null)" class="mt-1">
                                        <flux:select.option value="">— Non assigné —</flux:select.option>
                                        @foreach($users as $u)
                                            <flux:select.option value="{{ $u->id }}" :selected="$inq->assigned_to === $u->id">{{ $u->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    @if($inq->assignee)<div class="mt-1 text-xs text-emerald-700">→ {{ $inq->assignee->name }}</div>@endif
                                </div>

                                {{-- Relance --}}
                                @if($inq->next_action_at)
                                    <div class="mt-2 flex items-center gap-1 text-xs {{ $inq->next_action_at->isPast() ? 'text-red-600 font-semibold' : 'text-amber-700' }}">
                                        <flux:icon.clock class="size-3" /> Relance : {{ $inq->next_action_at->format('d/m/Y H:i') }}
                                        @if($inq->next_action_at->isPast()) <span class="rounded bg-red-100 px-1">en retard</span> @endif
                                    </div>
                                @endif
                                @if($inq->notes)
                                    <div class="mt-2 rounded bg-amber-50 p-2 text-xs text-zinc-700" title="{{ $inq->notes }}">{{ \Illuminate\Support\Str::limit($inq->notes, 80) }}</div>
                                @endif

                                {{-- Actions move --}}
                                <div class="mt-3 grid grid-cols-2 gap-1">
                                    @foreach(InquiryStatus::cases() as $s)
                                        @if($s->value !== $inq->status->value)
                                            <flux:button size="xs" variant="ghost" wire:click="moveStatus({{ $inq->id }}, '{{ $s->value }}')" class="text-[11px]">→ {{ $s->label() }}</flux:button>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-2 flex gap-1">
                                    <flux:button size="xs" variant="ghost" wire:click="startEditNotes({{ $inq->id }})" icon="pencil-square">Notes</flux:button>
                                    @can('inquiries.delete')
                                        <flux:button size="xs" variant="ghost" wire:click="delete({{ $inq->id }})" wire:confirm="Supprimer ce prospect ?" icon="trash" class="text-red-600">Suppr</flux:button>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed bg-white p-6 text-center text-xs text-zinc-400">Aucun prospect</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal notes --}}
        @if($editingId)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="cancelEdit">
                <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                    <flux:heading>Notes & Relance #{{ $editingId }}</flux:heading>
                    <flux:text class="mb-4">Visible uniquement en backoffice.</flux:text>
                    <flux:textarea wire:model="editingNotes" label="Notes internes" rows="4" placeholder="Appelé le 02/09, intéressé par LOT-A12, rappelle jeudi..." />
                    <flux:input type="datetime-local" wire:model="editingNextAction" label="Prochaine action" class="mt-3" />
                    <div class="mt-6 flex justify-end gap-2">
                        <flux:button variant="ghost" wire:click="cancelEdit">Annuler</flux:button>
                        <flux:button variant="primary" wire:click="saveNotes">Enregistrer</flux:button>
                    </div>
                </div>
            </div>
        @endif
    @else
        {{-- Vue liste conservée --}}
        <div class="overflow-x-auto rounded-2xl border border-zinc-200">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50">
                    <tr class="text-left">
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">Prospect</th>
                        <th class="px-3 py-3">Type</th>
                        <th class="px-3 py-3">Assigné</th>
                        <th class="px-3 py-3">Relance</th>
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
                                @if($inq->notes)<div class="mt-1 max-w-[200px] truncate rounded bg-amber-50 px-1 py-0.5 text-xs">{{ Str::limit($inq->notes, 40) }}</div>@endif
                            </td>
                            <td class="px-3 py-2"><flux:badge size="sm">{{ $inq->inquiry_type->label() }}</flux:badge></td>
                            <td class="px-3 py-2 text-xs">
                                @if($inq->assignee)<flux:badge color="emerald" size="sm">{{ $inq->assignee->name }}</flux:badge>@else<span class="text-zinc-400">—</span>@endif
                                <flux:select size="sm" wire:change="assignTo({{ $inq->id }}, $event.target.value ? parseInt($event.target.value) : null)" class="mt-1 w-32">
                                    <flux:select.option value="">—</flux:select.option>
                                    @foreach($users as $u)<flux:select.option value="{{ $u->id }}" :selected="$inq->assigned_to===$u->id">{{ $u->name }}</flux:select.option>@endforeach
                                </flux:select>
                            </td>
                            <td class="px-3 py-2 text-xs">
                                @if($inq->next_action_at)
                                    <span class="{{ $inq->next_action_at->isPast() ? 'text-red-600 font-bold' : 'text-zinc-600' }}">{{ $inq->next_action_at->format('d/m H:i') }}</span>
                                @else <span class="text-zinc-400">—</span> @endif
                                <flux:button size="xs" variant="ghost" wire:click="startEditNotes({{ $inq->id }})" icon="pencil-square" class="ml-1" />
                            </td>
                            <td class="px-3 py-2"><flux:badge :color="$inq->status->badgeColor()" size="sm">{{ $inq->status->label() }}</flux:badge></td>
                            <td class="px-3 py-2">
                                <div class="flex gap-1">
                                    @can('inquiries.update')
                                        <flux:select wire:change="updateStatus({{ $inq->id }}, $event.target.value)" class="w-28" size="sm">
                                            @foreach(InquiryStatus::cases() as $s)<flux:select.option value="{{ $s->value }}" :selected="$inq->status->value===$s->value">{{ $s->label() }}</flux:select.option>@endforeach
                                        </flux:select>
                                    @endcan
                                    @can('inquiries.delete')<flux:button size="xs" variant="ghost" wire:click="delete({{ $inq->id }})" wire:confirm="Supprimer ?">Suppr</flux:button>@endcan
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
        @if($editingId)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="cancelEdit">
                <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                    <flux:heading>Notes #{{ $editingId }}</flux:heading>
                    <flux:textarea wire:model="editingNotes" label="Notes" rows="4" />
                    <flux:input type="datetime-local" wire:model="editingNextAction" label="Relance" class="mt-3" />
                    <div class="mt-4 flex justify-end gap-2"><flux:button variant="ghost" wire:click="cancelEdit">Annuler</flux:button><flux:button variant="primary" wire:click="saveNotes">Enregistrer</flux:button></div>
                </div>
            </div>
        @endif
    @endif
</section>
