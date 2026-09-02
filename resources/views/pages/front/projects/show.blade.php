<?php

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.front')] class extends Component {
    public Project $project;

    public function mount(Project $project): void
    {
        abort_unless($project->is_published, 404);
        $this->project = $project->load('media');
    }
}; ?>

<section class="mx-auto max-w-7xl px-4 py-8 lg:px-8">
    <a href="{{ route('front.projects.index') }}" class="text-sm text-zinc-600 hover:underline">← Retour aux réalisations</a>

    <div class="mt-6 grid gap-8 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100">
                @if($project->cover_path)
                    <img src="{{ Storage::disk('public')->url($project->cover_path) }}" alt="{{ $project->title }}" class="w-full object-cover" loading="lazy" />
                @else
                    <div class="flex aspect-[16/10] items-center justify-center text-zinc-400">Aucune couverture</div>
                @endif
            </div>

            @if($project->media->isNotEmpty())
                <div class="mt-4 grid grid-cols-3 gap-3">
                    @foreach($project->media as $media)
                        <div class="overflow-hidden rounded-xl border border-zinc-200">
                            <img src="{{ Storage::disk($media->disk)->url($media->path) }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-zinc-900 px-3 py-1 text-xs font-medium text-white">{{ $project->service_type->label() }}</span>
                <span class="rounded-full px-3 py-1 text-xs font-medium @if($project->status->value === 'livre') bg-emerald-100 text-emerald-700 @elseif($project->status->value === 'en_cours') bg-[#e6ecf2] text-[#002244] @else bg-zinc-100 text-zinc-700 @endif">{{ $project->status->label() }}</span>
                @if($project->is_featured) <span class="rounded-full bg-[#e6ecf2] px-3 py-1 text-xs text-[#002244]">À la une</span> @endif
            </div>

            <h1 class="mt-4 text-3xl font-bold leading-tight">{{ $project->title }}</h1>

            <div class="mt-4 grid grid-cols-2 gap-3 rounded-2xl border border-zinc-200 p-4 text-sm">
                <div><div class="text-xs text-zinc-500">Localisation</div><div class="font-medium">{{ $project->location ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-500">Surface</div><div class="font-medium">{{ $project->surface_m2 ? $project->surface_m2.' m²' : '—' }}</div></div>
                <div><div class="text-xs text-zinc-500">Durée</div><div class="font-medium">{{ $project->duration_months ? $project->duration_months.' mois' : '—' }}</div></div>
                <div><div class="text-xs text-zinc-500">Année</div><div class="font-medium">{{ $project->year ?? '—' }}</div></div>
            </div>

            @if($project->description)
                <div class="prose prose-zinc mt-6 max-w-none text-sm leading-relaxed">
                    <h3 class="font-semibold">Description</h3>
                    <p class="whitespace-pre-line text-zinc-700">{{ $project->description }}</p>
                </div>
            @endif

            @if($project->technical_sheet)
                <div class="mt-6 rounded-2xl border border-zinc-200 p-4">
                    <h3 class="text-sm font-semibold">Fiche technique</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        @foreach($project->technical_sheet as $key => $value)
                            <div class="flex justify-between gap-4 border-b border-zinc-100 py-1 last:border-0">
                                <dt class="text-zinc-500">{{ Str::headline($key) }}</dt>
                                <dd class="font-medium">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            <div class="mt-8 flex gap-3">
                <a href="{{ route('front.contact', ['project' => $project->id]) }}" class="rounded-full bg-[#003366] px-6 py-3 text-sm font-semibold text-white hover:bg-[#002244]">Demander un devis similaire</a>
                <a href="{{ route('front.programs.index') }}" class="rounded-full border border-zinc-300 px-6 py-3 text-sm font-medium hover:bg-zinc-50">Voir nos lotissements</a>
            </div>
        </div>
    </div>
</section>
