<x-mail::message>
{!! \Illuminate\Support\Str::markdown($bodyMd) !!}

<x-mail::button :url="config('app.url')">
Découvrir SIBEA-CI
</x-mail::button>

<small>Vous recevez ceci car vous êtes abonné à SIBEA-CI. <a href="{{ config('app.url') }}/contact">Se désabonner : répondez à cet email</a></small>
</x-mail::message>
