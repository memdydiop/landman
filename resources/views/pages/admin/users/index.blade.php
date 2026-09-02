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
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Utilisateurs & Droits</flux:heading>
            <flux:text>Matrice Spatie — Super Admin / Éditeur BTP / Commercial Lotissement</flux:text>
        </div>
        @can('users.create')
            <flux:button :href="route('admin.users.create')" wire:navigate variant="primary" icon="plus">Créer utilisateur</flux:button>
        @endcan
    </div>

    <flux:input wire:model.live.debounce.300ms="search" placeholder="Rechercher nom/email..." class="max-w-xs mb-4" />

    @if(session('success')) <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div> @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50">
                <tr class="text-left">
                    <th class="px-4 py-3">Utilisateur</th>
                    <th class="px-4 py-3">Rôles</th>
                    <th class="px-4 py-3">Permissions (via rôles)</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-t border-zinc-100">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $user->email }} · Vérifié: {{ $user->email_verified_at ? 'oui' : 'non' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($roles as $role)
                                    <label class="inline-flex items-center gap-1 text-xs">
                                        <input type="checkbox" wire:click="toggleRole({{ $user->id }}, '{{ $role }}')" {{ $user->hasRole($role) ? 'checked' : '' }} class="rounded" />
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-500">
                            {{ $user->getPermissionNames()->take(6)->join(', ') }}
                            @if($user->getPermissionNames()->count() > 6) <span class="text-zinc-400">+{{ $user->getPermissionNames()->count()-6 }} autres</span> @endif
                        </td>
                        <td class="px-4 py-3">
                            @can('users.delete')
                                <flux:button size="xs" variant="ghost" wire:click="delete({{ $user->id }})" wire:confirm="Supprimer cet utilisateur ?">Supprimer</flux:button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-zinc-500">Aucun utilisateur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</section>
