<x-mail::message>
# Merci {{ $inquiry->name }} !

Votre demande **#{{ $inquiry->id }}** a bien été reçue par **SIBEA-CI**.

**Récapitulatif :**
- **Type :** {{ $inquiry->inquiry_type }}
@if($inquiry->program)**Programme :** {{ $inquiry->program->title }} @endif
@if($inquiry->plot)**Lot :** {{ $inquiry->plot->reference }} @endif
@if(!empty($inquiry->meta['budget_range']))- **Budget :** {{ $inquiry->meta['budget_range'] }} @endif

Notre équipe vous recontacte **sous 24h** au **{{ $inquiry->phone ?: $inquiry->email }}**.

@if($inquiry->plot && $inquiry->plot->plan_pdf_path)
<x-mail::button :url="Storage::disk(config('filesystems.default') === 's3' ? 'public' : 'public')->url($inquiry->plot->plan_pdf_path)">
Télécharger le plan PDF
</x-mail::button>
@endif

> {{ Str::limit($inquiry->message ?? '', 300) }}

Besoin urgent ? Répondez directement à cet email ou WhatsApp : **+225 07 00 00 00 00**

Merci de votre confiance,<br>
**SIBEA-CI** — BTP, Aménagement, Lotissement — Abidjan Bingerville

<small>Réf : #{{ $inquiry->id }} — {{ config('app.url') }}</small>
</x-mail::message>
