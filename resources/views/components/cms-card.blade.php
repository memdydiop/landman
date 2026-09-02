@props(['title', 'open' => true])

<details @if($open) open @endif class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
    <summary class="flex cursor-pointer list-none items-center justify-between p-4 hover:bg-zinc-50 rounded-2xl">
        <flux:heading size="sm" class="!mb-0">{{ $title }}</flux:heading>
        <flux:icon.chevron-down class="size-4 text-zinc-400 transition group-open:rotate-180" />
    </summary>
    <div class="px-4 pb-4 pt-0">
        {{ $slot }}
    </div>
</details>
