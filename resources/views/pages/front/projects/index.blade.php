<?php

use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.front')] #[Title('Réalisations — SIBEA-CI')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $service = '';

    #[Url]
    public string $status = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingService(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $projects = Project::published()
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('title', 'like', '%'.$s.'%');
            })
            ->when($this->service, fn ($q) => $q->where('service_type', $this->service))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('published_at')
            ->paginate(12);

        $hero = Cache::remember(
            'projects.hero', 
            300, 
            fn () => \App\Models\SiteSetting::get('projects.hero', [
                'title' => '',
                'body' => '',
                'badge' => '',
                'image' => null,
            ]),
        );

        return view('pages.front.projects.index', [
            'projects' => $projects, 
            'hero' => $hero
        ]);
    }
}; ?>
<section class="bg-white">
    <x-page-hero-simple
        :title="$hero['title'] ?: 'Réalisations <span class=&quot;font-black&quot;>contextualisées</span>'"
        :subtitle="$hero['body'] ?: 'Chaque projet est une réponse concrète — BTP, Électricité, Pétrole, Agro-industrie à Abidjan et Bingerville. Filtrez par expertise et avancement.'"
        :badge="$hero['badge'] ?: 'PORTFOLIO — 4 PÔLES'"
        :image="$hero['image'] ?? null"
        :image-alt="$hero['title'] ?? 'Réalisations SIBEA-CI'"
        :breadcrumb="[['label'=>'Réalisations','url'=>route('front.projects.index')]]"
    />

    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
        <div class="flex flex-wrap gap-3 border-y border-zinc-200 py-4">
            <input wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="w-full border-0 border-b border-zinc-300 bg-transparent px-0 py-2 text-sm focus:border-primary focus:ring-0 lg:w-64" />
            <select wire:model.live="service" class="border-0 border-b border-zinc-300 bg-transparent px-0 py-2 text-xs font-bold tracking-widest focus:border-primary focus:ring-0">
                <option value="">TOUS SERVICES</option>
                @foreach(ServiceType::cases() as $s) <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option> @endforeach
            </select>
            <select wire:model.live="status" class="border-0 border-b border-zinc-300 bg-transparent px-0 py-2 text-xs font-bold tracking-widest focus:border-primary focus:ring-0">
                <option value="">TOUS STATUTS</option>
                @foreach(ProjectStatus::cases() as $s) <option value="{{ $s->value }}">{{ strtoupper($s->label()) }}</option> @endforeach
            </select>
            @if($search || $service || $status)
                <button wire:click="$set('search',''); $set('service',''); $set('status','')" class="text-xs tracking-widest text-zinc-500 hover:text-[#003366]">RÉINITIALISER</button>
            @endif
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
            @forelse($projects as $project)
                @php
                    $hasImage = !empty($project->cover_path) && Storage::disk('public')->exists($project->cover_path);
                    $fallback = $fallbacks[$loop->index % count($fallbacks)];
                    $subtitle = \Illuminate\Support\Str::limit($project->description ?? '', 90);
                    if ($project->location || $project->year) {
                        $subtitle = ($project->location ? $project->location.' · ' : '').($project->year ?? '').($subtitle ? ' — '.$subtitle : '');
                    }
                @endphp
                <a href="{{ route('front.projects.show', $project) }}" class="group relative overflow-hidden rounded-2xl min-h-[360px] flex flex-col justify-between p-7 shadow-sm hover:shadow-2xl transition-all duration-500 border border-zinc-200">
                    @if($hasImage)
                        <img src="{{ Storage::disk('public')->url($project->cover_path) }}" alt="{{ $project->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/60 to-zinc-900/10"></div>
                        <div class="absolute inset-0 bg-primary/20 mix-blend-multiply opacity-60 group-hover:opacity-30 transition duration-500"></div>
                    @else
                        <img src="{{ $fallback }}" alt="{{ $project->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/70 to-zinc-900/20"></div>
                        <div class="absolute inset-0 bg-primary/10 group-hover:bg-primary/0 transition duration-500"></div>
                    @endif
                    <div class="relative">
                        <div class="text-xs tracking-widest text-white/60">0{{ $loop->iteration }} — {{ strtoupper($project->service_type->label()) }} · {{ strtoupper($project->status->label()) }}</div>
                        <h3 class="mt-3 text-xl font-black leading-tight text-white drop-shadow line-clamp-2">{{ $project->title }}</h3>
                    </div>
                    <div class="relative">
                        <p class="text-sm leading-relaxed text-zinc-200 line-clamp-3">{{ $subtitle ?: $project->service_type->label().' — '.$project->status->label() }}</p>
                        <div class="mt-4 inline-flex items-center gap-2 text-xs font-bold tracking-widest text-white group-hover:gap-3 transition-all">DÉCOUVRIR <span class="transition-transform group-hover:translate-x-1">→</span></div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 border border-dashed p-10 text-center text-sm tracking-widest text-zinc-500">AUCUN PROJET — FILTRES</div>
            @endforelse
        </div>

        <div class="mt-8">{{ $projects->links() }}</div>
    </div>
</section>
