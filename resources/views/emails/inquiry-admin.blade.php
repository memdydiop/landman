<x-mail::message>
# Nouvelle demande #{{ $inquiry->id }}

**Type :** {{ $inquiry->inquiry_type }} @if($inquiry->service_type) / {{ $inquiry->service_type }} @endif  
**Prospect :** {{ $inquiry->name }} — {{ $inquiry->email }} @if($inquiry->phone) / {{ $inquiry->phone }} @endif  
@if($inquiry->company)**Entreprise :** {{ $inquiry->company }} @endif

@if($inquiry->program || $inquiry->plot)
**Programme/Lot :** {{ $inquiry->program?->title ?? '—' }} @if($inquiry->plot) / Lot {{ $inquiry->plot->reference }} ({{ $inquiry->plot->surface_m2 }} m²) @endif
@endif

@if($inquiry->meta)
@foreach(['budget_range','budget','surface_wanted','project_size','project_type','deadline','location','services_needed'] as $k)
@if(!empty($inquiry->meta[$k]))
**{{ Str::headline(str_replace('_',' ',$k)) }} :** {{ is_array($inquiry->meta[$k]) ? implode(', ', $inquiry->meta[$k]) : $inquiry->meta[$k] }}
@endif
@endforeach
@endif

@if($inquiry->message)
**Message :**
> {{ $inquiry->message }}
@endif

<x-mail::button :url="config('app.url').'/admin/inquiries'">
Voir dans le backoffice
</x-mail::button>

Référence : #{{ $inquiry->id }} — {{ $inquiry->created_at->format('d/m/Y H:i') }}
</x-mail::message>
