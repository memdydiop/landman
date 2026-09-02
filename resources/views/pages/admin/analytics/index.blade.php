<?php

use App\Models\PageVisit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Analytics')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $this->authorize('analytics.view');
        $byRoute = PageVisit::select('route', DB::raw('count(*) as c'))->groupBy('route')->orderByDesc('c')->limit(10)->get();
        $byDay = PageVisit::select(DB::raw("DATE(created_at) as d"), DB::raw('count(*) as c'))->groupBy('d')->orderBy('d')->limit(7)->get();
        $total = PageVisit::count();

        return view('pages.admin.analytics.index', [
            'byRoute' => $byRoute,
            'byDay' => $byDay,
            'total' => $total,
        ]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <flux:heading size="xl">Analytics — Visites</flux:heading>
            <flux:text>{{ $total }} visites totales · TrackVisits middleware + PageVisit</flux:text>
        </div>
        <flux:badge color="sky" size="sm">{{ $byRoute->count() }} routes · {{ $byDay->count() }} jours</flux:badge>
    </div>

    <div class="grid gap-4 md:grid-cols-3 mb-6">
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-4 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><flux:icon.chart-bar class="size-5" /></div>
            <div><div class="text-xs text-zinc-500">Total visites</div><div class="text-xl font-black">{{ number_format($total,0,',',' ') }}</div></div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-4">
            <div class="text-xs text-zinc-500">Top route</div><div class="truncate text-sm font-bold">{{ $byRoute->first()?->route ?? $byRoute->first()?->path ?? '—' }}</div><div class="text-xs text-emerald-600">{{ $byRoute->first()?->c ?? 0 }} visites</div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-4">
            <div class="text-xs text-zinc-500">Aujourd'hui</div><div class="text-xl font-black">{{ $byDay->last()?->c ?? 0 }}</div><div class="text-xs text-zinc-500">{{ $byDay->last()?->d ?? today()->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-4">
            <flux:heading size="sm" class="mb-3">Top routes</flux:heading>
            <div class="space-y-2">
                @forelse($byRoute as $r)
                    <div class="flex items-center justify-between rounded-xl bg-zinc-50 px-3 py-2">
                        <span class="truncate text-sm font-medium">{{ $r->route ?? $r->path ?? '—' }}</span>
                        <flux:badge size="sm" color="sky">{{ $r->c }}</flux:badge>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed p-6 text-center text-sm text-zinc-500">Aucune visite.</div>
                @endforelse
            </div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 p-4">
            <flux:heading size="sm" class="mb-3">Par jour (7j)</flux:heading>
            <div class="h-48"><canvas id="analyticsChart"></canvas></div>
            <div class="mt-3 space-y-1">
                @foreach($byDay as $d)
                    <div class="flex justify-between text-xs"><span class="text-zinc-500">{{ $d->d }}</span><span class="font-bold">{{ $d->c }}</span></div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const C = window.Chart; if (!C) return;
            const el = document.getElementById('analyticsChart'); if (!el) return;
            const labels = @json($byDay->pluck('d'));
            const data = @json($byDay->pluck('c'));
            new C(el, { type: 'bar', data: { labels, datasets: [{ label: 'Visites', data, backgroundColor: '#0ea5e9' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
        });
    </script>
</section>
