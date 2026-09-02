<?php

use App\Enums\PlotStatus;
use App\Models\Program;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.front')] #[Title('Lotissements — SIBEA-CI')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $city = '';

    #[Url]
    public string $availability = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $programs = Program::published()
            ->withCount([
                'plots as plots_total',
                'plots as plots_available' => fn ($q) => $q->where('status', PlotStatus::DISPONIBLE),
                'plots as plots_reserved' => fn ($q) => $q->where('status', PlotStatus::RESERVE),
                'plots as plots_sold' => fn ($q) => $q->where('status', PlotStatus::VENDU),
            ])
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('title', 'like', '%'.$s.'%');
            })
            ->when($this->city, fn ($q) => $q->where('city', $this->city))
            ->when($this->availability === 'available', fn ($q) => $q->whereHas('plots', fn ($qq) => $qq->where('status', PlotStatus::DISPONIBLE)))
            ->latest('published_at')
            ->paginate(9);

        $cities = Program::published()->select('city')->distinct()->pluck('city');
        $hero = Cache::remember('programs.hero', 300, fn () => \App\Models\SiteSetting::get('programs.hero', [
            'title' => '',
            'body' => '',
            'badge' => '',
            'image' => null,
        ]));

        return view('pages.front.programs.index', [
            'programs' => $programs,
            'cities' => $cities,
            'hero' => $hero,
        ]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        :title="$hero['title'] ?: 'FONCIER — <span class=&quot;font-black&quot;>Viabilisation</span>'"
        :subtitle="$hero['body'] ?: 'Catalogue en temps réel — Disponible, Réservé, Vendu. Plans de masse PDF, ACD, viabilisation Bingerville Abatta. Lotissements sécurisés.'"
        :badge="$hero['badge'] ?: 'FONCIER — VIABILISATION'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Lotissements SIBEA-CI'"
        :breadcrumb="[['label'=>'Lotissements','url'=>route('front.programs.index')]]"
    />

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <div class="flex flex-wrap gap-3 border-y border-zinc-200 py-4">
            <input wire:model.live.debounce.300ms="search" placeholder="Rechercher programme..." class="w-full border-0 border-b border-zinc-300 bg-transparent px-0 py-2 text-sm focus:border-primary focus:ring-0 lg:w-64" />
            <select wire:model.live="city" class="border-0 border-b border-zinc-300 bg-transparent px-0 py-2 text-xs font-bold tracking-widest focus:border-primary focus:ring-0">
                <option value="">TOUTES VILLES</option>
                @foreach($cities as $c) <option value="{{ $c }}">{{ strtoupper($c) }}</option> @endforeach
            </select>
            <select wire:model.live="availability" class="border-0 border-b border-zinc-300 bg-transparent px-0 py-2 text-xs font-bold tracking-widest focus:border-primary focus:ring-0">
                <option value="">TOUS PROGRAMMES</option>
                <option value="available">AVEC LOTS DISPONIBLES</option>
            </select>
        </div>

        @php
            $fallbacks = [
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&q=80&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&q=80&auto=format&fit=crop',
            ];
        @endphp
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @forelse($programs as $program)
                @php
                    $hasImage = !empty($program->cover_path) && Storage::disk('public')->exists($program->cover_path);
                    $fallback = $fallbacks[$loop->index % count($fallbacks)];
                    $desc = ($program->address ? $program->address.' · ' : '').($program->total_area ? number_format((float)$program->total_area,0,',',' ').' m² · ' : '').$program->plots_available.' dispo · '.$program->plots_reserved.' réservés · '.$program->plots_total.' total';
                @endphp
                <a href="{{ route('front.programs.show', $program) }}" class="group relative overflow-hidden rounded-2xl min-h-[360px] flex flex-col justify-between p-7 shadow-sm hover:shadow-2xl transition-all duration-500 border border-zinc-200">
                    @if($hasImage)
                        <img src="{{ Storage::disk('public')->url($program->cover_path) }}" alt="{{ $program->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/60 to-zinc-900/10"></div>
                        <div class="absolute inset-0 bg-primary/20 mix-blend-multiply opacity-60 group-hover:opacity-30 transition duration-500"></div>
                    @else
                        <img src="{{ $fallback }}" alt="{{ $program->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/70 to-zinc-900/20"></div>
                        <div class="absolute inset-0 bg-primary/10 group-hover:bg-primary/0 transition duration-500"></div>
                    @endif
                    <div class="relative">
                        <div class="text-xs tracking-widest text-white/60">0{{ $loop->iteration }} — {{ strtoupper($program->city) }} · {{ $program->plots_available }} DISPO</div>
                        <h3 class="mt-3 text-xl font-black leading-tight text-white drop-shadow line-clamp-2">{{ $program->title }}</h3>
                    </div>
                    <div class="relative">
                        <p class="text-sm leading-relaxed text-zinc-200 line-clamp-3">{{ $desc }}</p>
                        <div class="mt-4 inline-flex items-center gap-2 text-xs font-bold tracking-widest text-white group-hover:gap-3 transition-all">DÉCOUVRIR <span class="transition-transform group-hover:translate-x-1">→</span></div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 border border-dashed p-10 text-center text-xs tracking-widest text-zinc-500">AUCUN PROGRAMME PUBLIÉ</div>
            @endforelse
        </div>

        <div class="mt-8">{{ $programs->links() }}</div>
    </div>
</section>
