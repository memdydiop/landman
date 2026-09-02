<?php

use App\Enums\InquiryStatus;
use App\Enums\PlotStatus;
use App\Models\Inquiry;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Backoffice')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        abort_unless(auth()->user()?->can('analytics.view'), 403);

        $stats = [
            'programs' => Program::count(),
            'plots_total' => Plot::count(),
            'plots_available' => Plot::where('status', PlotStatus::DISPONIBLE)->count(),
            'plots_sold' => Plot::where('status', PlotStatus::VENDU)->count(),
            'plots_reserved' => Plot::where('status', PlotStatus::RESERVE)->count(),
            'projects' => Project::count(),
            'inquiries_new' => Inquiry::where('status', InquiryStatus::NOUVEAU)->count(),
            'inquiries_total' => Inquiry::count(),
            'ca_total' => (float) Plot::whereIn('status', [PlotStatus::VENDU->value, PlotStatus::RESERVE->value])->sum('price'),
            'ca_month' => (float) Plot::whereIn('status', [PlotStatus::VENDU->value, PlotStatus::RESERVE->value])->whereMonth('updated_at', now()->month)->sum('price'),
        ];

        $recentInquiries = Inquiry::with(['program', 'plot'])->latest()->limit(5)->get();
        $recentPrograms = Program::withCount(['plots as available' => fn ($q) => $q->where('status', PlotStatus::DISPONIBLE)])->latest()->limit(4)->get();

        // Ventes par ville — agrégation correcte PG (GROUP BY city, pas programs.id)
        try {
            $salesByCity = \Illuminate\Support\Facades\DB::table('programs')
                ->leftJoin('plots', 'plots.program_id', '=', 'programs.id')
                ->select('programs.city', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT programs.id) as total_programs'), \Illuminate\Support\Facades\DB::raw('COUNT(plots.id) as total_plots'))
                ->groupBy('programs.city')
                ->orderByDesc('total_plots')
                ->limit(5)
                ->get()
                ->map(fn ($row) => (object) ['city' => $row->city, 'total_programs' => $row->total_programs, 'total_plots' => $row->total_plots]);
        } catch (\Throwable $e) {
            // Fallback SQLite / compat : withCount + collection groupBy
            $salesByCity = Program::withCount('plots')->get()->groupBy('city')->map(fn ($g, $city) => (object) ['city' => $city, 'total_programs' => $g->count(), 'total_plots' => $g->sum('plots_count')])->sortByDesc('total_plots')->take(5)->values();
        }

        // Chart data : 1 requête par dataset au lieu de 12
        $start = now()->subMonths(5)->startOfMonth();
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'))->toArray();
        $isSqlite = config('database.default') === 'sqlite' || \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        if ($isSqlite) {
            $inquiriesGrouped = Inquiry::where('created_at', '>=', $start)->selectRaw("strftime('%Y-%m', created_at) as ym, COUNT(*) as c")->groupBy('ym')->pluck('c', 'ym');
            $plotsGrouped = Plot::where('status', PlotStatus::VENDU)->where('updated_at', '>=', $start)->selectRaw("strftime('%Y-%m', updated_at) as ym, COUNT(*) as c")->groupBy('ym')->pluck('c', 'ym');
        } else {
            $inquiriesGrouped = Inquiry::where('created_at', '>=', $start)->selectRaw("to_char(created_at, 'YYYY-MM') as ym, COUNT(*) as c")->groupBy('ym')->pluck('c', 'ym');
            $plotsGrouped = Plot::where('status', PlotStatus::VENDU)->where('updated_at', '>=', $start)->selectRaw("to_char(updated_at, 'YYYY-MM') as ym, COUNT(*) as c")->groupBy('ym')->pluck('c', 'ym');
        }
        $inquiriesPerMonth = collect(range(5, 0))->map(function ($i) use ($inquiriesGrouped) {
            $ym = now()->subMonths($i)->format('Y-m');
            return (int) ($inquiriesGrouped[$ym] ?? 0);
        })->toArray();
        $salesPerMonth = collect(range(5, 0))->map(function ($i) use ($plotsGrouped) {
            $ym = now()->subMonths($i)->format('Y-m');
            return (int) ($plotsGrouped[$ym] ?? 0);
        })->toArray();

        return view('pages.admin.dashboard', [
            'stats' => $stats,
            'recentInquiries' => $recentInquiries,
            'recentPrograms' => $recentPrograms,
            'salesByCity' => $salesByCity,
            'months' => $months,
            'inquiriesPerMonth' => $inquiriesPerMonth,
            'salesPerMonth' => $salesPerMonth,
        ]);
    }
}; ?>

<section class="w-full p-6" x-data>
    <!-- Header UrbanHub -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <img src="https://i.pravatar.cc/100?img=12" alt="" class="size-10 rounded-full" />
                <div>
                    <flux:heading size="xl">Bonjour, {{ auth()->user()->name }}</flux:heading>
                    <flux:text>Suivez la performance de vos programmes, disponibilités et prospects en temps réel</flux:text>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            @can('programs.create')
                <flux:button :href="route('admin.programs.create')" wire:navigate variant="primary" icon="plus">Ajouter Programme</flux:button>
            @endcan
            @can('projects.create')
                <flux:button :href="route('admin.projects.create')" wire:navigate variant="ghost">Ajouter Projet</flux:button>
            @endcan
        </div>
    </div>

    <!-- 6 Cards UrbanHub -->
    <div class="mt-6 grid gap-4 md:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="text-xs text-zinc-500">Total Programmes</div>
            <div class="mt-1 text-2xl font-black">{{ $stats['programs'] }}</div>
            <div class="text-xs text-emerald-600">+8% vs mois dernier</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="text-xs text-zinc-500">Lots Disponibles</div>
            <div class="mt-1 text-2xl font-black text-emerald-600">{{ $stats['plots_available'] }}</div>
            <div class="text-xs text-zinc-500">{{ $stats['plots_total'] }} au total</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="text-xs text-zinc-500">Lots Vendus</div>
            <div class="mt-1 text-2xl font-black">{{ $stats['plots_sold'] }}</div>
            <div class="text-xs text-emerald-600">+2.3%</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="text-xs text-zinc-500">Total Prospects</div>
            <div class="mt-1 text-2xl font-black">{{ $stats['inquiries_total'] }}</div>
            <div class="text-xs text-zinc-500">{{ $stats['inquiries_new'] }} nouveaux</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="text-xs text-zinc-500">Projets BTP</div>
            <div class="mt-1 text-2xl font-black">{{ $stats['projects'] }}</div>
            <div class="text-xs text-[#003366]">VRD inclus</div>
        </div>
        <div class="rounded-2xl border border-[#99b3cc] bg-[#f0f4f8] p-4">
            <div class="text-xs text-[#001a33]">CA estimé (vendus)</div>
            <div class="mt-1 text-lg font-black text-[#002244]">{{ number_format($stats['ca_total'], 0, ',', ' ') }} FCFA</div>
            <div class="text-xs text-[#002244]/70">{{ number_format($stats['ca_month'], 0, ',', ' ') }} ce mois</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <flux:heading>Prospects / Mois</flux:heading>
                <span class="text-xs text-zinc-500">6 derniers mois</span>
            </div>
            <div class="mt-4 h-48">
                <canvas id="inquiriesChart"></canvas>
            </div>
            <div class="mt-2 text-xs text-zinc-500">Total {{ array_sum($inquiriesPerMonth) }} prospects sur 6 mois</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <flux:heading>Ventes Lots / Mois</flux:heading>
                <span class="text-xs text-zinc-500">Vendus</span>
            </div>
            <div class="mt-4 h-48">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="mt-2 text-xs text-zinc-500">Suivi commercial Bingerville, Abatta & régions</div>
        </div>
    </div>

    <!-- Most Sales Location + Customer Review -->
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:heading class="mb-4">Ventes par Ville</flux:heading>
            <div class="space-y-3">
                @forelse($salesByCity as $row)
                    <div class="flex items-center justify-between rounded-xl bg-zinc-50 p-3">
                        <div>
                            <div class="text-sm font-medium">{{ $row->city }}</div>
                            <div class="text-xs text-zinc-500">{{ $row->total_programs }} programmes</div>
                        </div>
                        <div class="text-sm font-bold">{{ $row->total_plots }} lots</div>
                    </div>
                @empty
                    <div class="text-sm text-zinc-500">Aucune donnée ville.</div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <flux:heading>Avis Prospects</flux:heading>
                <flux:button :href="route('admin.inquiries.index')" wire:navigate variant="ghost" size="sm">Voir tous</flux:button>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($recentInquiries as $inq)
                    <div class="flex gap-3 rounded-xl border border-zinc-100 p-3">
                        <img src="https://i.pravatar.cc/100?img={{ $inq->id % 70 + 1 }}" alt="" class="size-8 rounded-full" />
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">{{ $inq->name }}</span>
                                <span class="text-xs text-zinc-500">{{ $inq->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-xs text-zinc-500">{{ $inq->email }} — {{ $inq->inquiry_type->label() }}</div>
                            <div class="mt-1 text-sm text-zinc-700 line-clamp-2">{{ \Illuminate\Support\Str::limit($inq->message ?? '—', 90) }}</div>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-xs text-[#f0f4f8]0">★★★★☆ 4.{{ $inq->id % 4 + 5 }}</span>
                                <flux:badge :color="$inq->status->badgeColor()" size="sm">{{ $inq->status->label() }}</flux:badge>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-zinc-500">Aucun prospect.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- New Property Listings + Quick Add -->
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <flux:heading>Nouveaux Programmes</flux:heading>
                <flux:button :href="route('admin.programs.index')" wire:navigate variant="ghost" size="sm">Voir tout</flux:button>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @forelse($recentPrograms as $prog)
                    <a href="{{ route('admin.plots.index', $prog) }}" wire:navigate class="overflow-hidden rounded-xl border border-zinc-200 hover:shadow">
                        <div class="aspect-[16/10] bg-zinc-100">
                            @if($prog->cover_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($prog->cover_path) }}" alt="" class="size-full object-cover" loading="lazy" />
                            @else
                                <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop" alt="" class="size-full object-cover" loading="lazy" />
                            @endif
                        </div>
                        <div class="p-3">
                            <div class="text-sm font-bold">{{ $prog->title }}</div>
                            <div class="text-xs text-zinc-500">{{ $prog->city }} — {{ $prog->available }} dispo</div>
                            <div class="mt-1 text-xs">{{ $prog->total_area ? number_format((float)$prog->total_area,0,',',' ').' m²' : '' }}</div>
                        </div>
                    </a>
                @empty
                    <div class="text-sm text-zinc-500">Aucun programme.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:heading>Ajout Rapide Lot</flux:heading>
            <p class="text-xs text-zinc-500">Raccourci Commercial — création express</p>
            <div class="mt-4 space-y-3">
                <div class="rounded-xl bg-zinc-50 p-3">
                    <div class="text-sm font-medium">Programme</div>
                    <select class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-2 py-1 text-sm" onchange="window.location.href='/admin/programs/'+this.value+'/plots'">
                        <option value="">— Choisir —</option>
                        @foreach($recentPrograms as $p)
                            <option value="{{ $p->slug }}">{{ $p->title }}</option>
                        @endforeach
                    </select>
                    <div class="mt-2 text-xs text-zinc-500">Sélectionnez pour gérer les lots</div>
                </div>
                <div class="rounded-xl bg-[#f0f4f8] p-3">
                    <div class="text-sm font-medium text-[#001a33]">Besoin d'aide ?</div>
                    <a href="{{ route('front.contact') }}" class="mt-1 inline-block text-xs font-medium text-[#002244] underline">Guide Commercial PDF</a>
                </div>
                @can('inquiries.export')
                    <flux:button :href="route('admin.inquiries.export')" icon="arrow-down-tray" class="w-full">Exporter prospects CSV</flux:button>
                @endcan
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined' && typeof window.Chart !== 'undefined') window.Chart = window.Chart;
            const C = window.Chart;
            if (!C) return;
            const months = @json($months);
            const inquiries = @json($inquiriesPerMonth);
            const sales = @json($salesPerMonth);
            const ctx1 = document.getElementById('inquiriesChart');
            const ctx2 = document.getElementById('salesChart');
            if (ctx1) new C(ctx1, { type: 'bar', data: { labels: months, datasets: [{ label: 'Prospects', data: inquiries, backgroundColor: '#f59e0b' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
            if (ctx2) new C(ctx2, { type: 'line', data: { labels: months, datasets: [{ label: 'Vendus', data: sales, borderColor: '#10b981', tension: 0.3, fill: false }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
        });
    </script>
</section>
