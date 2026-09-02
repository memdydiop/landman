@php
    $waGlobal = \App\Models\SiteSetting::get('global', []);
    $raw = $waGlobal['company_whatsapp'] ?? $waGlobal['company_phone'] ?? '2250700000000';
    $waNumber = preg_replace('/[^0-9]/', '', $raw);
    // Normalise CI : 07... → 22507...
    if (!str_starts_with($waNumber, '225') && str_starts_with($waNumber, '0')) {
        $waNumber = '225' . ltrim($waNumber, '0');
    }
    if (strlen($waNumber) < 11) $waNumber = '225' . ltrim($waNumber, '0');
@endphp
<a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Bonjour SIBEA-CI, je souhaite des infos sur vos terrains/projets.') }}" target="_blank" rel="noopener"
   class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 group flex size-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-[0_8px_24px_rgba(16,185,129,0.35)] ring-1 ring-white/15 hover:bg-emerald-700 hover:shadow-[0_12px_32px_rgba(16,185,129,0.45)] hover:scale-105 active:scale-95 transition-all duration-300"
   aria-label="WhatsApp SIBEA-CI">
    <span class="absolute inset-0 rounded-full bg-emerald-600 animate-ping opacity-20 group-hover:opacity-0"></span>
    <svg class="relative size-7 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/>
    </svg>
</a>
