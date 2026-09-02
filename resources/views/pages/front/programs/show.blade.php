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

<section class="bg-zinc-950 text-white min-h-screen pb-20">
    <!-- Fil d'Ariane & Navigation Foncier -->
    <div class="border-b border-zinc-800/80 bg-zinc-900/60 py-3">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-xs font-mono lg:px-8">
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-zinc-400 hover:text-amber-400 transition">ACCUEIL</a> 
                <span class="text-zinc-600">/</span>
                <a href="{{ route('front.programs.index') }}" class="text-zinc-400 hover:text-amber-400 transition">LOTISSEMENTS</a> 
                <span class="text-zinc-600">/</span>
                <span class="font-bold text-amber-400 uppercase truncate max-w-[200px] sm:max-w-none">{{ $program->title }}</span>
            </div>
            <a href="{{ route('front.programs.index') }}" class="hidden sm:inline-flex text-xs font-bold text-zinc-400 hover:text-amber-400 transition">
                ← RETOUR CATALOGUE
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        
        <!-- En-tête Fiche Technique du Lotissement -->
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded bg-amber-500 px-2 py-0.5 font-mono text-[10px] font-black text-zinc-950 uppercase">
                            📍 {{ strtoupper($program->city) }}
                        </span>
                        <span class="rounded bg-emerald-600/90 px-2.5 py-0.5 font-mono text-[10px] font-bold text-white uppercase">
                            ● {{ $counts['dispo'] }} LOTS DISPONIBLES
                        </span>
                    </div>
                    <h1 class="mt-3 text-2xl font-black uppercase text-white sm:text-3xl tracking-tight">{{ $program->title }}</h1>
                    <p class="mt-1 font-mono text-xs text-zinc-400">
                        {{ $program->address }} @if($program->total_area) · Superficie totale : {{ number_format((float)$program->total_area, 0, ',', ' ') }} m² @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('front.contact', ['program' => $program->id]) }}" 
                       class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-xs font-mono font-black tracking-wider text-zinc-950 hover:bg-amber-400 transition">
                        RESERVER UN LOT DANS CE PROGRAMME
                    </a>
                    <a href="https://wa.me/2250700000000?text=Bonjour%20SIBEA-CI,%20je%20souhaite%20des%20informations%20sur%20le%20programme%20{{ urlencode($program->title) }}" 
                       target="_blank" 
                       class="rounded-xl border border-zinc-700 bg-zinc-800 p-3 text-emerald-400 hover:bg-emerald-600 hover:text-white transition" 
                       aria-label="Contact WhatsApp Foncier">
                        <svg class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            <!-- Sidebar : Visuel & Statistiques -->
            <div class="lg:col-span-2 space-y-6">
                <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-md">
                    @if($program->cover_path && Storage::disk('public')->exists($program->cover_path))
                        <img src="{{ Storage::disk('public')->url($program->cover_path) }}" alt="{{ $program->title }}" class="w-full object-cover max-h-[360px]" loading="eager" />
                    @else
                        <div class="flex aspect-[16/10] items-center justify-center bg-zinc-950 font-mono text-xs text-zinc-600">
                            PLAN DE MASSE NON DISPONIBLE
                        </div>
                    @endif
                </div>

                <!-- Métriques Récapitulatives du Cadastre -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-sm">
                    <h3 class="font-mono font-black text-white text-sm uppercase tracking-wider">État des Lots Foncier</h3>
                    <div class="mt-1 h-0.5 w-8 bg-amber-500"></div>

                    <div class="mt-4 grid grid-cols-2 gap-3 font-mono text-center">
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-zinc-500 uppercase">Total Lots</div>
                            <div class="mt-1 font-black text-lg text-zinc-100">{{ $counts['total'] }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-emerald-500 uppercase">Disponibles</div>
                            <div class="mt-1 font-black text-lg text-emerald-400">{{ $counts['dispo'] }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-amber-500 uppercase">Réservés</div>
                            <div class="mt-1 font-black text-lg text-amber-400">{{ $counts['reserve'] }}</div>
                        </div>
                        <div class="rounded-xl bg-zinc-950 p-3 border border-zinc-800">
                            <div class="text-[10px] text-red-500 uppercase">Vendus</div>
                            <div class="mt-1 font-black text-lg text-red-400">{{ $counts['vendu'] }}</div>
                        </div>
                    </div>

                    @if($program->description)
                        <div class="mt-6 border-t border-zinc-800/80 pt-4">
                            <h4 class="font-mono font-bold text-xs uppercase text-amber-400">Présentation du Programme</h4>
                            <p class="mt-2 whitespace-pre-line text-xs text-zinc-300 leading-relaxed">{{ $program->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Grille / Tableau Interactive des Lots -->
            <div class="lg:col-span-3 space-y-4">
                <!-- Filtres sur les Lots -->
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 shadow-sm flex flex-col sm:flex-row gap-3 justify-between items-center">
                    <input wire:model.live.debounce.300ms="search" 
                           type="text" 
                           placeholder="Filtrer par réf. lot (ex: LOT-01)..." 
                           class="w-full sm:w-64 rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-xs font-mono text-zinc-100 placeholder-zinc-500 focus:border-amber-500 focus:outline-none" />

                    <select wire:model.live="status" class="w-full sm:w-auto rounded-xl border border-zinc-800 bg-zinc-950 px-3 py-2 text-xs font-mono font-bold text-zinc-200 focus:border-amber-500 focus:outline-none">
                        <option value="">TOUS LES STATUTS</option>
                        @foreach(PlotStatus::cases() as $s)
                            <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Table Cadastrale Style Fiche BTP -->
                <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left font-mono text-xs">
                            <thead class="border-b border-zinc-800 bg-zinc-950/80 text-[11px] font-bold text-amber-400 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Lot</th>
                                    <th class="py-3.5 px-4">Superficie</th>
                                    <th class="py-3.5 px-4">Prix (FCFA)</th>
                                    <th class="py-3.5 px-4">Statut</th>
                                    <th class="py-3.5 px-4">Plan PDF</th>
                                    <th class="py-3.5 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60 text-zinc-300">
                                @forelse($plots as $plot)
                                    @php
                                        $valStatus = $plot->status instanceof PlotStatus ? $plot->status->value : $plot->status;
                                        $labelStatus = $plot->status instanceof PlotStatus ? $plot->status->label() : $plot->status;
                                    @endphp
                                    <tr class="hover:bg-zinc-800/40 transition">
                                        <td class="py-3 px-4 font-black text-white">{{ $plot->reference }}</td>
                                        <td class="py-3 px-4">
                                            {{ $plot->surface_m2 }} m²
                                            <span class="block text-[9px] text-zinc-500 uppercase">
                                                {{ $plot->is_viabilise ? '● Viabilisé' : '○ Non viabilisé' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-amber-400">
                                            {{ $plot->price ? number_format((float)$plot->price, 0, ',', ' ').' FCFA' : 'Sur devis' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-block rounded px-2 py-0.5 text-[10px] font-bold uppercase
                                                @if($valStatus === 'disponible') bg-emerald-600/90 text-white
                                                @elseif($valStatus === 'reserve') bg-amber-600/90 text-white
                                                @elseif($valStatus === 'vendu') bg-red-600/90 text-white
                                                @else bg-zinc-700 text-zinc-200 @endif">
                                                {{ $labelStatus }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($plot->plan_pdf_path)
                                                <a href="{{ route('plots.plan', $plot) }}" target="_blank" class="text-amber-400 hover:underline flex items-center gap-1 font-bold">
                                                    📄 PDF
                                                </a>
                                            @else
                                                <span class="text-zinc-600">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($valStatus === PlotStatus::DISPONIBLE->value)
                                                <a href="{{ route('front.contact', ['plot' => $plot->id, 'program' => $program->id]) }}" 
                                                   class="inline-block rounded-lg bg-amber-500 px-3 py-1.5 font-bold text-zinc-950 hover:bg-amber-400 transition uppercase text-[10px]">
                                                    RÉSERVER
                                                </a>
                                            @else
                                                <span class="text-[10px] text-zinc-500 uppercase">{{ $plot->juridical_status ?? '—' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center font-mono text-xs text-zinc-500 uppercase">
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
                
                <p class="font-mono text-[10px] text-zinc-500 text-right uppercase">
                    * Mises à jour en temps réel selon la disponibilité au cadastre
                </p>
            </div>
        </div>
    </div>
</section>
