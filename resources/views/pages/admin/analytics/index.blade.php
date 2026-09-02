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
    <flux:heading size="xl">Analytics — Visites vitrine</flux:heading>
    <flux:text class="mb-4">Total {{ $total }} visites — suivi via middleware `TrackVisits`</flux:text>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 p-4">
            <flux:heading size="sm" class="mb-3">Top routes (7j)</flux:heading>
            @forelse($byRoute as $r)
                <div class="flex justify-between py-1 text-sm"><span>{{ $r->route ?? $r->path }}</span><span class="font-bold">{{ $r->c }}</span></div>
            @empty
                <div class="text-sm text-zinc-500">Aucune visite.</div>
            @endforelse
        </div>
        <div class="rounded-xl border border-zinc-200 p-4">
            <flux:heading size="sm" class="mb-3">Par jour</flux:heading>
            @foreach($byDay as $d)
                <div class="flex justify-between py-1 text-sm"><span>{{ $d->d }}</span><span>{{ $d->c }}</span></div>
            @endforeach
        </div>
    </div>
</section>
