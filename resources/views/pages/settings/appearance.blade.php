<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Thème clair uniquement — le mode sombre a été désactivé.')">
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600">
            Mode clair forcé. Le sélecteur sombre/système a été retiré (Flux appearance désactivé).
        </div>
    </x-pages::settings.layout>
</section>
