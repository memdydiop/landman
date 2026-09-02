<?php

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] #[Title('Utilisateurs')] class extends Component {
    use WithPagination;

    public string $search = '';
    public array $editingRoles = [];
    public string $view = 'list';
    public bool $showCreate = false;
    public string $c_name = '';
    public string $c_email = '';
    public string $c_password = '';
    public string $c_password_confirmation = '';
    public array $c_roles = [];

    public function updatingSearch(): void { $this->resetPage(); }

    public function toggleRole(int $userId, string $roleName): void
    {
        $this->authorize('users.update');
        $user = User::findOrFail($userId);
        $role = Role::findByName($roleName);
        if ($user->hasRole($roleName)) {
            $user->removeRole($role);
        } else {
            $user->assignRole($role);
        }
    }

    public function createUser(): void
    {
        $this->authorize('users.create');
        $validated = $this->validate([
            'c_name' => ['required', 'string', 'max:255'],
            'c_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'c_password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::min(12)->mixedCase()->numbers()->symbols()],
            'c_roles' => ['nullable', 'array'],
            'c_roles.*' => ['string', \Illuminate\Validation\Rule::exists('roles', 'name')],
        ], [], ['c_name' => 'nom', 'c_email' => 'email', 'c_password' => 'mot de passe']);
        $user = User::create([
            'name' => $validated['c_name'],
            'email' => $validated['c_email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['c_password']),
            'email_verified_at' => now(),
        ]);
        if (!empty($validated['c_roles'])) $user->assignRole($validated['c_roles']);
        $this->reset(['showCreate', 'c_name', 'c_email', 'c_password', 'c_password_confirmation', 'c_roles']);
        session()->flash('success', 'Utilisateur '.$user->email.' créé.');
    }

    public function delete(int $id): void
    {
        $this->authorize('users.delete');
        if (auth()->id() === $id) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }
        User::findOrFail($id)->delete();
        session()->flash('success', 'Utilisateur supprimé.');
    }

    public function render(): \Illuminate\View\View
    {
        $users = User::with('roles')
            ->when($this->search, function ($q) {
                $s = str_replace(['%', '_'], '', $this->search);
                $q->where('name', 'like', '%'.$s.'%')->orWhere('email', 'like', '%'.$s.'%');
            })
            ->latest()
            ->paginate(15);

        $roles = Role::pluck('name');

        return view('pages.admin.users.index', ['users' => $users, 'roles' => $roles]);
    }
}; ?>

<section class="w-full p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <flux:heading size="xl">Utilisateurs — Droits SIBEA-CI</flux:heading>
            <flux:text>{{ $users->total() }} utilisateur(s) · Spatie Super Admin / Éditeur / Commercial</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button.group>
                <flux:button :variant="$view==='grid'?'primary':'ghost'" wire:click="$set('view','grid')" icon="squares-2x2" size="sm">Grille</flux:button>
                <flux:button :variant="$view==='list'?'primary':'ghost'" wire:click="$set('view','list')" icon="list-bullet" size="sm">Liste</flux:button>
            </flux:button.group>
            @can('users.create')
                <flux:button wire:click="$set('showCreate', true)" variant="primary" icon="plus">Créer</flux:button>
            @endcan
        </div>
    </div>

    <flux:modal wire:model="showCreate" class="md:w-[560px]">
        <form wire:submit="createUser" class="space-y-4">
            <div>
                <flux:heading size="lg">Créer utilisateur</flux:heading>
                <flux:text>Super Admin seul — 12+ caractères, mixte, chiffres, symboles</flux:text>
            </div>
            <flux:input wire:model="c_name" label="Nom *" required />
            <flux:input wire:model="c_email" label="Email *" type="email" required />
            <div class="grid gap-3 md:grid-cols-2">
                <flux:input wire:model="c_password" label="Mot de passe *" type="password" viewable required />
                <flux:input wire:model="c_password_confirmation" label="Confirmation *" type="password" viewable required />
            </div>
            <div>
                <flux:heading size="sm" class="mb-2">Rôles</flux:heading>
                <div class="flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <label class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm">
                            <input type="checkbox" wire:model="c_roles" value="{{ $role }}" class="rounded" /> {{ $role }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Annuler</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Créer</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher nom/email..." icon="magnifying-glass" class="max-w-xs mb-4" />

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div> @endif

    @if($view === 'grid')
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($users as $user)
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 hover:shadow transition">
                    <div class="flex items-center gap-3">
                        <flux:avatar :name="$user->name" size="lg" />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-bold">{{ $user->name }}</div>
                            <div class="truncate text-xs text-zinc-500">{{ $user->email }}</div>
                            <div class="text-[11px] {{ $user->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $user->email_verified_at ? 'Vérifié' : 'Non vérifié' }} · {{ $user->created_at->format('d/m/Y') }}</div>
                        </div>
                        @can('users.delete')
                            <flux:button size="xs" variant="ghost" icon="trash" wire:click="delete({{ $user->id }})" wire:confirm="Supprimer ?" class="text-red-600 shrink-0" />
                        @endcan
                    </div>
                    <div class="mt-3">
                        <div class="text-xs font-medium text-zinc-700">Rôles</div>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            @foreach($roles as $role)
                                <label class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs">
                                    <flux:checkbox 
                                        label="{{ $role }}" 
                                        :checked="$user->hasRole($role)" 
                                        wire:click="toggleRole({{ $user->id }}, '{{ $role }}')" />
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-3 rounded-xl bg-zinc-50 p-2 text-[11px] text-zinc-600">
                        {{ $user->getPermissionNames()->take(8)->join(', ') ?: '— aucune permission —' }}
                        @if($user->getPermissionNames()->count() > 8) <span class="text-zinc-400">+{{ $user->getPermissionNames()->count()-8 }}</span> @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm text-zinc-500">Aucun utilisateur.</div>
            @endforelse
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50">
                    <tr class="text-left">
                        <th class="px-4 py-3">Utilisateur</th>
                        <th class="px-4 py-3">Rôles</th>
                        <th class="px-4 py-3">Permissions</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-t border-zinc-100 hover:bg-zinc-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar :name="$user->name" size="sm" />
                                    <div class="min-w-0"><div class="truncate font-medium">{{ $user->name }}</div><div class="truncate text-xs text-zinc-500">{{ $user->email }}</div></div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($roles as $role)
                                        <flux:checkbox 
                                            label="{{ $role }}" 
                                            :checked="$user->hasRole($role)" 
                                            wire:click="toggleRole({{ $user->id }}, '{{ $role }}')" />

                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500 max-w-[260px] truncate">{{ $user->getPermissionNames()->take(6)->join(', ') }}</td>
                            <td class="px-4 py-3">@can('users.delete')<flux:button size="xs" variant="ghost" wire:click="delete({{ $user->id }})" wire:confirm="Supprimer ?">Supprimer</flux:button>@endcan</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-zinc-500">Aucun utilisateur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
    <div class="mt-4">{{ $users->links() }}</div>
</section>
