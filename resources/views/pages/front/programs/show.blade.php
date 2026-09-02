<?php

use App\Enums\PlotStatus;
use App\Models\Program;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.front')] class extends Component {
    use WithPagination;

    public Program $program;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    public function mount(Program $program): void
    {
        abort_unless($program->is_published, 404);
        $this->program = $program;
    }

    public function updatingStatus(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $plots = $this->program->plots()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('reference', 'like', '%'.$s.'%');
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('reference')
            ->paginate(12);

        // Single query counts with FILTER (PG) / CASE (SQLite)
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $countsRow = $this->program->plots()
            ->selectRaw($isSqlite
                ? "COUNT(*) as total, COUNT(CASE WHEN status = '".PlotStatus::DISPONIBLE->value."' THEN 1 END) as dispo, COUNT(CASE WHEN status = '".PlotStatus::RESERVE->value."' THEN 1 END) as reserve, COUNT(CASE WHEN status = '".PlotStatus::VENDU->value."' THEN 1 END) as vendu"
                : "COUNT(*) as total, COUNT(*) FILTER (WHERE status = '".PlotStatus::DISPONIBLE->value."') as dispo, COUNT(*) FILTER (WHERE status = '".PlotStatus::RESERVE->value."') as reserve, COUNT(*) FILTER (WHERE status = '".PlotStatus::VENDU->value."') as vendu"
            )->first();

        $counts = [
            'total' => (int) ($countsRow->total ?? 0),
            'dispo' => (int) ($countsRow->dispo ?? 0),
            'reserve' => (int) ($countsRow->reserve ?? 0),
            'vendu' => (int) ($countsRow->vendu ?? 0),
        ];

        return view('pages.front.programs.show', [
            'plots' => $plots,
            'counts' => $counts,
        ]);
    }
}; ?>

<section class="bg-white">
    <div class="bg-zinc-50 py-8">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <a href="{{ route('front.programs.index') }}" class="text-xs tracking-widest text-zinc-500 hover:text-[#003366]">← LOTISSEMENTS</a>
            <div class="mt-4 max-w-3xl border-l-4 border-white/30 pl-6">
                <div class="text-xs tracking-[0.3em] text-zinc-500">{{ strtoupper($program->city) }} — {{ $counts['dispo'] }} DISPO</div>
                <h1 class="mt-2 text-3xl font-light tracking-tight">{{ $program->title }}</h1>
                <p class="mt-2 text-sm text-zinc-600">{{ $program->address }} @if($program->total_area) · {{ number_format((float)$program->total_area,0,',',' ') }} m² · Viabilisé Bingerville Abatta @endif</p>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <div class="overflow-hidden bg-zinc-100">
                    @if($program->cover_path)
                        <img src="{{ Storage::disk('public')->url($program->cover_path) }}" alt="{{ $program->title }}" class="w-full object-cover" loading="lazy" />
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center bg-zinc-50 text-xs tracking-widest text-zinc-500">SIBEA-CI — {{ strtoupper($program->city) }}</div>
                    @endif
                </div>
                @if($program->description)
                    <div class="mt-6 border-t-2 border-zinc-900 pt-4">
                        <h3 class="text-xs font-bold tracking-widest">CONTEXTE</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-zinc-600">{{ $program->description }}</p>
                    </div>
                @endif
                <div class="mt-6 grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="border-t-2 border-zinc-900 pt-2"><div class="text-lg font-light">{{ $counts['total'] }}</div><div class="tracking-widest text-zinc-500">LOTS</div></div>
                    <div class="border-t-2 border-emerald-500 pt-2"><div class="text-lg font-light text-emerald-600">{{ $counts['dispo'] }}</div><div class="tracking-widest text-emerald-600">DISPO</div></div>
                    <div class="border-t-2 border-white/30 pt-2"><div class="text-lg font-light text-[#003366]">{{ $counts['reserve'] }}</div><div class="tracking-widest text-[#003366]">RÉSERVÉS</div></div>
                    <div class="border-t pt-2"><div class="text-lg font-light">{{ $counts['vendu'] }}</div><div class="tracking-widest text-zinc-500">VENDUS</div></div>
                </div>
                <div class="mt-6 flex gap-2">
                    <a href="{{ route('front.contact', ['program' => $program->id]) }}" class="flex-1 border border-zinc-900 px-4 py-2 text-center text-xs font-bold tracking-widest hover:bg-zinc-900 hover:text-white">RÉSERVER UN LOT</a>
                    <a href="https://wa.me/2250700000000?text=Bonjour SIBEA-CI, infos {{ urlencode($program->title) }}" target="_blank" class="border border-zinc-300 px-3 py-2 inline-flex items-center justify-center hover:bg-zinc-100" aria-label="WhatsApp"><svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg></a>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="flex flex-wrap gap-3 border-y border-zinc-200 py-4">
                    <input wire:model.live.debounce.300ms="search" placeholder="Référence..." class="border-0 border-b border-zinc-300 bg-transparent px-0 py-1 text-sm focus:border-primary focus:ring-0" />
                    <select wire:model.live="status" class="border-0 border-b border-zinc-300 bg-transparent px-0 py-1 text-xs font-bold tracking-widest focus:border-primary focus:ring-0">
                        <option value="">TOUS STATUTS</option>
                        @foreach(\App\Enums\PlotStatus::cases() as $s) <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option> @endforeach
                    </select>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b-2 border-zinc-900 text-left text-xs tracking-widest">
                            <tr>
                                <th class="pb-2">LOT</th>
                                <th class="pb-2">SURFACE</th>
                                <th class="pb-2">PRIX</th>
                                <th class="pb-2">STATUT</th>
                                <th class="pb-2">PLAN</th>
                                <th class="pb-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($plots as $plot)
                                <tr>
                                    <td class="py-3 font-mono text-xs font-bold">{{ $plot->reference }}</td>
                                    <td class="py-3">{{ $plot->surface_m2 }} m² <span class="text-xs tracking-widest text-zinc-400">@if($plot->is_viabilise) VIABILISÉ @else NON @endif</span></td>
                                    <td class="py-3 font-light">{{ $plot->price ? number_format((float)$plot->price, 0, ',', ' ').' FCFA' : 'Sur devis' }}</td>
                                    <td class="py-3"><span class="text-xs font-bold tracking-widest @if($plot->status->value==='disponible') text-emerald-600 @elseif($plot->status->value==='reserve') text-[#003366] @elseif($plot->status->value==='vendu') text-red-600 @else text-sky-600 @endif">{{ strtoupper($plot->status->label()) }}</span></td>
                                    <td class="py-3">
                                        @if($plot->plan_pdf_path)
                                            <a href="{{ route('plots.plan', $plot) }}" target="_blank" class="text-xs tracking-widest text-[#003366] hover:underline">PDF</a>
                                        @else
                                            <span class="text-xs text-zinc-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($plot->status === \App\Enums\PlotStatus::DISPONIBLE)
                                            <a href="{{ route('front.contact', ['plot' => $plot->id, 'program' => $program->id]) }}" class="border border-zinc-900 px-3 py-1 text-xs font-bold tracking-widest hover:bg-zinc-900 hover:text-white">RÉSERVER</a>
                                        @else
                                            <span class="text-xs text-zinc-400">{{ $plot->juridical_status ?? '' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-10 text-center text-xs tracking-widest text-zinc-500">AUCUN LOT — FILTRE</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $plots->links() }}</div>
                <p class="mt-4 text-xs tracking-widest text-zinc-400">* STATUTS TEMPS RÉEL — PRIX MIS À JOUR BACKOFFICE</p>
            </div>
        </div>
    </div>
</section>
