<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\TrackVisitJob;
use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    /** @var string[] */
    private array $botPatterns = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'facebookexternalhit',
        'headless', 'lighthouse', 'chrome-lighthouse', 'ptst', 'screaming',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldSkip($request, $response)) {
            return $response;
        }

        // Échantillonnage 25% en prod pour limiter volume (configurable)
        $sampleRate = (int) config('analytics.sample_rate', 25);
        if (app()->environment('production') && random_int(1, 100) > $sampleRate) {
            return $response;
        }

        // Anonymisation IP (hash) + consentement implicite : on ne stocke pas IP brute en prod
        $ip = $request->ip();
        $ipHash = $ip ? hash('sha256', $ip.config('app.key')) : null;
        // En local/testing on garde IP lisible pour debug
        if (! app()->environment('production')) {
            $ipHash = $ip;
        }

        $path = $request->path();
        $route = $request->route()?->getName();
        $ua = substr((string) $request->userAgent(), 0, 255);
        $userId = $request->user()?->id;

        // Dispatch async via queue si disponible, sinon log + fallback sync
        try {
            if (config('queue.default') !== 'sync' || app()->environment('testing')) {
                TrackVisitJob::dispatch($path, $route, $ipHash, $ua, $userId)->onQueue('analytics');
            } else {
                // Queue sync en dev : dispatch direct mais sans bloquer réponse (afterResponse)
                dispatch(function () use ($path, $route, $ipHash, $ua, $userId): void {
                    try {
                        PageVisit::create([
                            'path' => $path,
                            'route' => $route,
                            'ip' => $ipHash,
                            'user_agent' => $ua,
                            'user_id' => $userId,
                        ]);
                    } catch (\Throwable $e) {
                        Log::channel('daily')->warning('TrackVisits failed', ['e' => $e->getMessage()]);
                    }
                })->afterResponse();
            }
        } catch (\Throwable $e) {
            Log::channel('daily')->warning('TrackVisits dispatch failed', ['e' => $e->getMessage()]);
        }

        return $response;
    }

    private function shouldSkip(Request $request, Response $response): bool
    {
        if ($request->is('admin/*', 'livewire/*', 'flux/*', 'up', 'health', '_debugbar/*')) {
            return true;
        }
        // Ignorer requêtes non HTML (assets, api)
        if ($request->expectsJson() || $request->wantsJson()) {
            return true;
        }
        // Filtre bot basique
        $ua = strtolower((string) $request->userAgent());
        foreach ($this->botPatterns as $p) {
            if (str_contains($ua, $p)) {
                return true;
            }
        }
        // Ignorer réponses non 2xx
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return true;
        }

        return false;
    }
}
