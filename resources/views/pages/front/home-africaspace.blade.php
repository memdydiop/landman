<?php

use App\Enums\PlotStatus;
use App\Enums\ProjectStatus;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.front')] #[Title('SIBEA-CI — Laboratoire urbain')] class extends Component {
    public function render(): \Illuminate\View\View
    {
        $hero = SiteSetting::get('home.hero', []);
        $recentWorks = Project::published()->latest('published_at')->limit(6)->get();
        $stats = SiteSetting::get('home.stats', []);
        $stats = [
            'projects' => $stats['projects_completed'] ?? Project::where('status', ProjectStatus::LIVRE)->count(),
            'surface' => $stats['surface_total'] ?? Program::published()->sum('total_area'),
            'plots' => Plot::available()->count(),
        ];
        return view('pages.front.home-africaspace', [
            'hero' => $hero,
            'recentWorks' => $recentWorks,
            'stats' => $stats,
        ]);
    }
}; ?>

<section class="bg-white">
    <!-- Hero épuré AfricaSpace — vidéo drone — responsive -->
    <div class="relative flex min-h-[480px] h-[64svh] sm:min-h-[520px] sm:h-[68svh] lg:min-h-[640px] lg:h-[78vh] xl:min-h-[700px] max-h-[900px] items-center overflow-hidden bg-zinc-900">
        <video autoplay muted loop playsinline poster="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80&auto=format&fit=crop" class="absolute inset-0 size-full object-cover opacity-50">
            <source src="https://videos.pexels.com/video-files/18069234/18069234-uhd_1440_1440_24fps.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-zinc-900/60"></div>
        <div class="relative mx-auto flex w-full max-w-7xl items-center px-4 lg:px-8 py-10 sm:py-12">
            <div class="max-w-3xl border-l-4 border-white/30 pl-6">
                <div class="text-xs tracking-[0.3em] text-[#4d7aa3]">SARL • 2022 • IDU CI-2022-0016466 Q</div>
                <h1 class="mt-3 text-5xl font-light leading-none tracking-tight text-white lg:text-6xl">SIBEA-CI<br><span class="font-black">Laboratoire urbain</span></h1>
                <p class="mt-4 max-w-xl text-sm leading-relaxed text-zinc-200">Recherche, conseil et études en développement urbain. BTP, Électricité, Pétrole et Agro-industrie — réponses concrètes et contextualisées aux enjeux africains. Siège : Abidjan Bingerville, Abatta Lot 935 Îlot 86 — Dir. Ouattara Bassoma Ziegnougo.</p>
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('front.projects.index') }}" class="bg-white px-6 py-3 text-xs font-bold tracking-widest text-zinc-900 hover:bg-zinc-100">EXPLORER LES RÉALISATIONS</a>
                    <a href="{{ route('front.contact') }}" class="border border-white px-6 py-3 text-xs font-bold tracking-widest text-white hover:bg-white hover:text-zinc-900">DEMANDER UNE ÉTUDE</a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-6 right-6 hidden text-xs tracking-widest text-white/60 lg:block">ABATTA — BINGERVILLE • CÔTE D'IVOIRE</div>
    </div>

    <!-- 4 pôles transversaux -->
    <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-light">4 pôles <span class="font-black">transversaux</span></h2>
            <a href="{{ route('front.services.index') }}" class="text-xs font-bold tracking-widest text-[#003366] hover:underline">TOUS LES SERVICES →</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-4">
            @foreach([
                ['BTP & Génie Civil','Construction bâtiments, TP','btp'],
                ['Électricité','Réseaux industriels, CIE','electricite'],
                ['Pétrole & Énergie','Logistique pétrolière','petrole'],
                ['Agro-industrie','Transformation, négoce biens','agro'],
            ] as [$title,$desc,$key])
                <div class="border-t-2 border-white/30 bg-zinc-50 p-6">
                    <div class="text-xs tracking-widest text-zinc-500">0{{ $loop->index + 1 }} — {{ $key }}</div>
                    <h3 class="mt-2 font-bold">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-zinc-600">{{ $desc }}</p>
                    <a href="{{ route('front.services.show', $key === 'petrole' ? 'lotissement' : ($key==='agro'?'renovation':$key)) }}" class="mt-4 inline-block text-xs font-bold tracking-widest text-[#003366]">→</a>
                </div>
            @endforeach
        </div>
        <p class="mx-auto mt-6 max-w-3xl text-center text-xs leading-relaxed text-zinc-500">Grâce à la transversalité de ces thématiques, AfricaSpace — et SIBEA-CI — offrent des réponses concrètes et contextualisées aux enjeux urbains africains.</p>
    </div>

    <!-- Stats épurés -->
    <div class="border-y border-zinc-200 bg-white py-10">
        <div class="mx-auto grid max-w-7xl grid-cols-3 gap-6 px-4 text-center lg:px-8">
            <div><div class="text-3xl font-light">{{ $stats['projects'] }}</div><div class="text-xs tracking-widest text-zinc-500">PROJETS LIVRÉS</div></div>
            <div><div class="text-3xl font-light">{{ number_format((float)$stats['surface'],0,',',' ') }} m²</div><div class="text-xs tracking-widest text-zinc-500">AMÉNAGÉS</div></div>
            <div><div class="text-3xl font-light">{{ $stats['plots'] }}</div><div class="text-xs tracking-widest text-zinc-500">LOTS DISPONIBLES</div></div>
        </div>
    </div>

    <!-- Réalisations grille blanche -->
    <div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
        <h2 class="text-2xl font-light">Réalisations <span class="font-black">contextualisées</span></h2>
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @forelse($recentWorks as $work)
                <a href="{{ route('front.projects.show', $work) }}" class="group block">
                    <div class="aspect-[4/3] overflow-hidden bg-zinc-100">
                        @if($work->cover_path)
                            <img src="{{ Storage::disk('public')->url($work->cover_path) }}" alt="" class="size-full object-cover transition group-hover:scale-105" loading="lazy" />
                        @else
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&q=80&auto=format&fit=crop" alt="" class="size-full object-cover" loading="lazy" />
                        @endif
                    </div>
                    <div class="mt-3">
                        <div class="text-xs tracking-widest text-zinc-500">{{ $work->service_type->label() }}</div>
                        <div class="font-bold">{{ $work->title }}</div>
                        <div class="text-xs text-zinc-500">{{ $work->location }} — {{ $work->year }}</div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center text-sm text-zinc-500">Aucune réalisation publiée.</div>
            @endforelse
        </div>
    </div>

    <!-- A/B switch -->
    <div class="mx-auto max-w-7xl px-4 pb-10 lg:px-8">
        <div class="rounded-xl border border-[#99b3cc] bg-[#f0f4f8] p-4 text-center">
            <span class="text-sm">Variante AfricaSpace — </span>
            <a href="{{ route('home') }}" class="text-sm font-bold text-[#002244] underline">Voir version Construction (slider)</a>
        </div>
    </div>
</section>
