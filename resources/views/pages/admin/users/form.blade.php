<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] #[Title('Créer utilisateur')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $roles = [];

    public function mount(): void
    {
        $this->authorize('users.create');
    }

    public function save(): void
    {
        $this->authorize('users.create');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        if (! empty($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        session()->flash('success', 'Utilisateur '.$user->email.' créé — rôle(s) : '.implode(', ', $validated['roles'] ?? ['aucun']).'. Transmettez le mot de passe de façon sécurisée.');

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages.admin.users.form', [
            'allRoles' => Role::pluck('name'),
        ]);
    }
}; ?>

<section class="w-full p-6 max-w-2xl">
    <div class="mb-6">
        <flux:heading size="xl">Créer un utilisateur</flux:heading>
        <flux:text>Accès sécurisé — seul Super Admin. Mot de passe 12+ caractères, mixte, chiffres, symboles. L'utilisateur devra changer à la première connexion (à implémenter).</flux:text>
    </div>

    <form wire:submit="save" class="space-y-4 rounded-2xl border border-zinc-200 p-6">
        <flux:input wire:model="name" label="Nom *" required autocomplete="name" />
        <flux:input wire:model="email" label="Email *" type="email" required autocomplete="email" />
        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="password" label="Mot de passe *" type="password" required viewable />
            <flux:input wire:model="password_confirmation" label="Confirmation *" type="password" required viewable />
        </div>
        <div class="text-xs text-zinc-500">12+ caractères, majuscule/minuscule, chiffre, symbole — `Password::min(12)->mixedCase()->numbers()->symbols()`</div>

        <div>
            <flux:heading size="sm" class="mb-2">Rôles</flux:heading>
            <div class="flex flex-wrap gap-3">
                @foreach($allRoles as $role)
                    <label class="inline-flex items-center gap-2 rounded-full border border-zinc-200 px-3 py-1 text-sm">
                        <input type="checkbox" wire:model="roles" value="{{ $role }}" class="rounded" />
                        {{ $role }}
                    </label>
                @endforeach
            </div>
            @error('roles.*') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Créer l'utilisateur</flux:button>
            <flux:button :href="route('admin.users.index')" wire:navigate variant="ghost">Annuler</flux:button>
        </div>
    </form>

    <div class="mt-6 rounded-xl bg-[#f0f4f8] p-4 text-xs text-[#001a33]">
        <strong>Sécurité :</strong> Inscription publique <code>/register</code> doit être désactivée en production : <code>config/fortify.php:171</code> commenter <code>Features::registration()</code> puis <code>php artisan config:clear</code>. Seul ce formulaire reste.
    </div>
</section>
