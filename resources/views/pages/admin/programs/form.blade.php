<?php

use App\Models\Program;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public ?Program $program = null;
    public string $title = '';
    public string $slug = '';
    public string $city = '';
    public string $address = '';
    public string $total_area = '';
    public string $description = '';
    public bool $is_published = false;
    public $cover;
    public ?string $existing_cover = null;

    public function mount(?Program $program = null): void
    {
        if ($program && $program->exists) {
            $this->authorize('programs.update');
            $this->program = $program;
            $this->title = $program->title;
            $this->slug = $program->slug;
            $this->city = $program->city;
            $this->address = $program->address ?? '';
            $this->total_area = $program->total_area ? (string) $program->total_area : '';
            $this->description = $program->description ?? '';
            $this->is_published = $program->is_published;
            $this->existing_cover = $program->cover_path;
        } else {
            $this->authorize('programs.create');
        }
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->program) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('programs', 'slug')->ignore($this->program?->id)],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_published' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ]);

        $data = [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'city' => $validated['city'],
            'address' => $validated['address'] ?? null,
            'total_area' => $validated['total_area'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? ($this->program?->published_at ?? now()) : null,
        ];

        if ($this->cover) {
            $data['cover_path'] = ImageService::storeOptimized($this->cover, 'programs/covers');
        }

        if ($this->program) {
            $this->program->update($data);
            session()->flash('success', 'Programme mis à jour.');
        } else {
            Program::create($data);
            session()->flash('success', 'Programme créé.');
        }

        $this->redirect(route('admin.programs.index'), navigate: true);
    }

    public function getTitleAttribute(): string
    {
        return $this->program ? 'Éditer programme' : 'Nouveau programme';
    }
}; ?>

<section class="w-full p-6 max-w-3xl">
    <flux:heading size="xl" class="mb-1">{{ $this->program ? 'Éditer : '.$this->program->title : 'Nouveau programme' }}</flux:heading>
    <flux:text class="mb-6">Lotissement / site foncier — champs marqués * obligatoires</flux:text>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="title" label="Titre *" placeholder="Ex: Les Jardins de Cocody" required />
        <flux:input wire:model="slug" label="Slug *" description="URL: /lotissements/{{ $slug ?: 'mon-programme' }}" required />

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="city" label="Ville *" required />
            <flux:input wire:model="total_area" label="Surface totale (m²)" type="number" step="0.01" />
        </div>

        <flux:input wire:model="address" label="Adresse" />
        <flux:textarea wire:model="description" label="Description" rows="4" />
        <flux:checkbox wire:model="is_published" label="Publié (visible front-office)" />

        <div>
            <flux:input type="file" wire:model="cover" label="Image de couverture (WebP/JPG, max 4Mo)" accept="image/*" />
            @if($existing_cover)
                <div class="mt-2 text-xs text-zinc-500">Actuel: {{ $existing_cover }}</div>
            @endif
            @error('cover') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ $this->program ? 'Mettre à jour' : 'Créer le programme' }}</flux:button>
            <flux:button :href="route('admin.programs.index')" wire:navigate variant="ghost">Annuler</flux:button>
        </div>
    </form>
</section>
