<?php

use App\Enums\PlotStatus;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
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
    public function updatingSearch(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $plots = $this->program->plots()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('reference', 'like', '%'.$s.'%');
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('reference')
            ->paginate(15);

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
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

<section class="bg-zinc-100/60 min-h-screen pb-16">
    <!-- Fil d'Ariane Technique -->
    <div class="border-b border-zinc-200 bg-white py-3">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-xs font-mono lg:px-8">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-zinc-500 hover:text-amber-600 transition">ACCUEIL</a> 
                <span class="text-zinc-300">/</span>
                <a href="{{ route('front.programs.index') }}" class="text-zinc-500 hover:text-amber-600 transition">LOTISSEMENTS</a> 
                <span class="text-zinc-300">/</span>
                <span class="font-bold text-zinc-900 uppercase truncate max-w-[200px] sm:max-w-none">{{ $program->title }}</span>
            </div>
            <a href="{{ route('front.programs.index') }}" class="hidden sm:inline-flex text-xs font-bold text-zinc-600 hover:text-amber-600 transition">
                ← RETOUR CATALOGUE
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        
        <!-- En-tête Fiche Technique du Lotissement -->
        <div class="rounded-2xl bg-zinc-900 p-6 text-white shadow-xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded bg-amber-500 px-2 py-0.5 font-mono text-[10px] font-black text-zinc-950 uppercase">
                            📍 {{ strtoupper($program->city) }}
                        </span>
                        <span class="rounded bg-emerald-600 px-2.5 py-0.5 font-mono text-[10px] font-bold text-white uppercase">
                            ● {{ $counts['dispo'] }} LOTS DISPONIBLES
                        </span>
                    </div>
                    <h1 class="mt-3 text-2xl font-black uppercase text-white sm:text-3xl tracking-tight">{{ $program->title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('front.contact', ['program' => $program->id]) }}" 
                       class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-xs font-mono font-black tracking-wider text-zinc-950 hover:bg-amber-400 transition">
                        RÉSERVER UN LOT DANS CE PROGRAMME
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            <!-- Visualisation Plan / Cover -->
            <div class="lg:col-span-2 space-y-6">
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-900 shadow-md">
                    @if($program->cover_path && Storage::disk('public')->exists($program->cover_path))
                        <img src="{{ Storage::disk('public')->url($program->cover_path) }}" alt="{{ $program->title }}" class="w-full object-cover max-h-[360px]" loading="eager" />
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center font-mono text-xs text-zinc-500">
                            PLAN DE MASSE NON DISPONIBLE
                        </div>
                    @endif
                </div>

                <!-- Métriques Fond blanc -->
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h3 class="font-black text-zinc-900 text-sm uppercase tracking-wider">Données Cadastrales</h3>
                    <div class="mt-1 h-0.5 w-8 bg-amber-500"></div>

                    <div class="mt-4 grid grid-cols-2 gap-3 font-mono text-xs">
                        <div class="rounded-xl bg-zinc-50 p-3 border border-zinc-200/60">
                            <div class="text-[10px] text-zinc-500 uppercase">Localisation</div>
                            <div class="mt-1 font-bold text-zinc-900">{{ $program->address ?: $program->city }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 border border-zinc-200/60">
                            <div class="text-[10px] text-zinc-500 uppercase">Superficie Totale</div>
                            <div class="mt-1 font-bold text-zinc-900">{{ $program->total_area ? number_format((float)$program->total_area, 0, ',', ' ').' m²' : '—' }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 border border-zinc-200/60">
                            <div class="text-[10px] text-zinc-500 uppercase">Total Lots</div>
                            <div class="mt-1 font-bold text-zinc-900">{{ $counts['total'] }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-50 p-3 border border-zinc-200/60">
                            <div class="text-[10px] text-emerald-600 uppercase">Disponibles</div>
                            <div class="mt-1 font-bold text-emerald-700">{{ $counts['dispo'] }}</div>
                        </div>
                    </div>

                    @if($program->description)
                        <div class="mt-6 border-t border-zinc-100 pt-4">
                            <h4 class="font-bold text-xs uppercase text-zinc-900">Description du programme</h4>
                            <p class="mt-2 whitespace-pre-line text-xs text-zinc-700 leading-relaxed">{{ $program->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Grille / Tableau des Lots -->
            <div class="lg:col-span-3 space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm flex flex-col sm:flex-row gap-3 justify-between items-center">
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           placeholder="Rechercher par référence lot..." 
                           class="w-full sm:w-64 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-mono text-zinc-900 focus:border-amber-500 focus:bg-white focus:ring-0" />

                    <select wire:model.live="status" class="w-full sm:w-auto rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-mono font-bold text-zinc-800 focus:border-amber-500 focus:ring-0">
                        <option value="">TOUS LES STATUTS</option>
                        @foreach(PlotStatus::cases() as $s)
                            <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left font-mono text-xs">
                            <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-bold text-zinc-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Lot</th>
                                    <th class="py-3.5 px-4">Surface</th>
                                    <th class="py-3.5 px-4">Prix</th>
                                    <th class="py-3.5 px-4">Statut</th>
                                    <th class="py-3.5 px-4">Plan PDF</th>
                                    <th class="py-3.5 px-4 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 text-zinc-800">
                                @forelse($plots as $plot)
                                    @php
                                        $valStatus = $plot->status instanceof PlotStatus ? $plot->status->value : $plot->status;
                                        $labelStatus = $plot->status instanceof PlotStatus ? $plot->status->label() : $plot->status;
                                    @endphp
                                    <tr class="hover:bg-zinc-50 transition">
                                        <td class="py-3 px-4 font-black text-zinc-900">{{ $plot->reference }}</td>
                                        <td class="py-3 px-4">
                                            {{ $plot->surface_m2 }} m²
                                            <span class="block text-[9px] text-zinc-400 uppercase">
                                                {{ $plot->is_viabilise ? '● Viabilisé' : '○ Non viabilisé' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-amber-600">
                                            {{ $plot->price ? number_format((float)$plot->price, 0, ',', ' ').' FCFA' : 'Sur devis' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-block rounded px-2 py-0.5 text-[10px] font-bold uppercase
                                                @if($valStatus === 'disponible') bg-emerald-600 text-white
                                                @elseif($valStatus === 'reserve') bg-amber-600 text-white
                                                @elseif($valStatus === 'vendu') bg-red-600 text-white
                                                @else bg-zinc-200 text-zinc-700 @endif">
                                                {{ $labelStatus }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($plot->plan_pdf_path)
                                                <a href="{{ route('plots.plan', $plot) }}" target="_blank" class="text-amber-600 hover:underline font-bold">
                                                    📄 PDF
                                                </a>
                                            @else
                                                <span class="text-zinc-400">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($valStatus === PlotStatus::DISPONIBLE->value)
                                                <a href="{{ route('front.contact', ['plot' => $plot->id, 'program' => $program->id]) }}" 
                                                   class="inline-block rounded-lg bg-amber-500 px-3 py-1.5 font-bold text-zinc-950 hover:bg-amber-400 transition uppercase text-[10px]">
                                                    RÉSERVER
                                                </a>
                                            @else
                                                <span class="text-[10px] text-zinc-400 uppercase">{{ $plot->juridical_status ?? '—' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center font-mono text-xs text-zinc-400 uppercase">
                                            AUCUN LOT NE CORRESPOND À CE FILTRE DE RECHERCHE
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $plots->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
