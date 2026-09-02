<?php

use App\Enums\ProjectStatus;
use App\Enums\ServiceType;
use App\Models\Project;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public ?Project $project = null;
    public string $title = '';
    public string $slug = '';
    public string $service_type = '';
    public string $status = '';
    public string $location = '';
    public string $surface_m2 = '';
    public string $duration_months = '';
    public string $year = '';
    public string $description = '';
    public string $technical_sheet = '';
    public bool $is_featured = false;
    public bool $is_published = false;
    public $cover;
    public $gallery = [];
    public ?string $existing_cover = null;

    public function mount(?Project $project = null): void
    {
        if ($project && $project->exists) {
            $this->authorize('projects.update');
            $this->project = $project;
            $this->title = $project->title;
            $this->slug = $project->slug;
            $this->service_type = $project->service_type->value;
            $this->status = $project->status->value;
            $this->location = $project->location ?? '';
            $this->surface_m2 = $project->surface_m2 ? (string) $project->surface_m2 : '';
            $this->duration_months = $project->duration_months ? (string) $project->duration_months : '';
            $this->year = $project->year ? (string) $project->year : '';
            $this->description = $project->description ?? '';
            $this->technical_sheet = $project->technical_sheet ? json_encode($project->technical_sheet, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
            $this->is_featured = $project->is_featured;
            $this->is_published = $project->is_published;
            $this->existing_cover = $project->cover_path;
        } else {
            $this->authorize('projects.create');
            $this->service_type = ServiceType::BTP->value;
            $this->status = ProjectStatus::LIVRE->value;
        }
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->project) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($this->project?->id)],
            'service_type' => ['required', Rule::in(array_column(ServiceType::cases(), 'value'))],
            'status' => ['required', Rule::in(array_column(ProjectStatus::cases(), 'value'))],
            'location' => ['nullable', 'string', 'max:255'],
            'surface_m2' => ['nullable', 'numeric', 'min:0'],
            'duration_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'year' => ['nullable', 'integer', 'min:1990', 'max:2035'],
            'description' => ['nullable', 'string', 'max:10000'],
            'technical_sheet' => ['nullable', 'string', 'max:5000'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'max:5120'],
        ]);

        $technical = null;
        if (! empty($validated['technical_sheet'])) {
            $decoded = json_decode($validated['technical_sheet'], true);
            $technical = json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $validated['technical_sheet']];
        }

        $data = [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
            'location' => $validated['location'] ?? null,
            'surface_m2' => $validated['surface_m2'] ?? null,
            'duration_months' => $validated['duration_months'] ?? null,
            'year' => $validated['year'] ?? null,
            'description' => $validated['description'] ?? null,
            'technical_sheet' => $technical,
            'is_featured' => $validated['is_featured'],
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? ($this->project?->published_at ?? now()) : null,
        ];

        if ($this->cover) {
            $data['cover_path'] = ImageService::storeOptimized($this->cover, 'projects/covers');
        }

        if ($this->project) {
            $this->project->update($data);
            $project = $this->project;
        } else {
            $project = Project::create($data);
        }

        if ($this->gallery) {
            $position = (int) ($project->media()->max('position') ?? 0);
            foreach ($this->gallery as $file) {
                $path = ImageService::storeOptimized($file, 'projects/gallery');
                $project->media()->create([
                    'path' => $path,
                    'disk' => 'public',
                    'mime' => 'image/webp',
                    'size' => $file->getSize(),
                    'position' => ++$position,
                ]);
            }
        }

        session()->flash('success', $this->project ? 'Projet mis à jour.' : 'Projet créé.');
        $this->redirect(route('admin.projects.index'), navigate: true);
    }

    public function removeMedia(int $mediaId): void
    {
        $this->authorize('projects.update');
        if (! $this->project) return;
        $media = $this->project->media()->findOrFail($mediaId);
        \Illuminate\Support\Facades\Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    public function moveMedia(int $mediaId, string $direction): void
    {
        $this->authorize('projects.update');
        if (! $this->project) return;
        $media = $this->project->media()->findOrFail($mediaId);
        $swap = $direction === 'up'
            ? $this->project->media()->where('position', '<', $media->position)->orderByDesc('position')->first()
            : $this->project->media()->where('position', '>', $media->position)->orderBy('position')->first();
        if ($swap) {
            [$media->position, $swap->position] = [$swap->position, $media->position];
            $media->save(); $swap->save();
        }
    }
}; ?>

<section class="w-full p-6 max-w-4xl">
    <flux:heading size="xl" class="mb-1">{{ $this->project ? 'Éditer : '.$this->project->title : 'Nouveau projet' }}</flux:heading>
    <flux:text class="mb-6">Typage strict via Enums ServiceType / ProjectStatus — upload WebP/AVIF recommandé</flux:text>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="title" label="Titre *" required />
        <flux:input wire:model="slug" label="Slug *" required />

        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model="service_type" label="Service *" required>
                @foreach(ServiceType::cases() as $case)
                    <flux:select.option value="{{ $case->value }}">{{ $case->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model="status" label="Statut *" required>
                @foreach(ProjectStatus::cases() as $case)
                    <flux:select.option value="{{ $case->value }}">{{ $case->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="location" label="Localisation" placeholder="Abidjan, Cocody" />
            <flux:input wire:model="surface_m2" label="Surface (m²)" type="number" step="0.01" />
            <flux:input wire:model="duration_months" label="Durée (mois)" type="number" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="year" label="Année" type="number" />
            <flux:checkbox wire:model="is_featured" label="Mettre à la une (hero / projets phares)" />
        </div>

        <flux:textarea wire:model="description" label="Description" rows="4" />
        <flux:textarea wire:model="technical_sheet" label="Fiche technique (JSON)" rows="3" description='Ex: {"maitre_ouvrage":"...","budget":"1 200 000 €"}' />
        <flux:checkbox wire:model="is_published" label="Publié" />

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <flux:input type="file" wire:model="cover" label="Couverture (max 5Mo)" accept="image/*" />
                @if($existing_cover) <div class="mt-1 text-xs text-zinc-500">Actuel: {{ $existing_cover }}</div> @endif
            </div>
            <div>
                <flux:input type="file" wire:model="gallery" label="Galerie (max 10 images, 5Mo chacune)" accept="image/*" multiple />
            </div>
        </div>
        @error('cover') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        @error('gallery.*') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ $this->project ? 'Mettre à jour' : 'Créer' }}</flux:button>
            <flux:button :href="route('admin.projects.index')" wire:navigate variant="ghost">Annuler</flux:button>
        </div>
    </form>

    @if($this->project && $this->project->media()->exists())
        <div class="mt-10 rounded-2xl border border-zinc-200 p-4">
            <flux:heading class="mb-3">Galerie — réordonnancement</flux:heading>
            <div class="grid gap-3 md:grid-cols-4">
                @foreach($this->project->media()->ordered()->get() as $media)
                    <div class="rounded-lg border border-zinc-200 p-2">
                        <div class="text-xs truncate mb-1">{{ $media->path }}</div>
                        <div class="text-xs text-zinc-500">Pos: {{ $media->position }}</div>
                        <div class="mt-2 flex gap-1">
                            <flux:button size="xs" variant="ghost" wire:click="moveMedia({{ $media->id }}, 'up')">↑</flux:button>
                            <flux:button size="xs" variant="ghost" wire:click="moveMedia({{ $media->id }}, 'down')">↓</flux:button>
                            <flux:button size="xs" variant="ghost" wire:click="removeMedia({{ $media->id }})" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
