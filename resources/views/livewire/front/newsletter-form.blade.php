<div class="rounded-2xl border border-zinc-200 p-4">
    @if($done)
        <div class="text-sm font-medium text-emerald-700">Merci — vous êtes inscrit à la newsletter.</div>
    @else
        <form wire:submit="submit" class="flex gap-2">
            <input wire:model="email" type="email" placeholder="Votre email" required autocomplete="email" class="flex-1 rounded-full border border-zinc-300 px-4 py-2 text-sm" />
            <!-- honeypot -->
            <input wire:model="website" type="text" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
            <button type="submit" class="rounded-full bg-[#003366] px-5 py-2 text-sm font-bold text-white hover:bg-[#002244]">S'inscrire</button>
        </form>
        @error('email') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        <div class="text-xs text-zinc-500 mt-1">1 email/mois — conseils achat foncier, normes.</div>
    @endif
</div>
