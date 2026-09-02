<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PageVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $path,
        public ?string $route,
        public ?string $ipHash,
        public ?string $userAgent,
        public ?int $userId,
    ) {}

    public function handle(): void
    {
        try {
            PageVisit::create([
                'path' => $this->path,
                'route' => $this->route,
                'ip' => $this->ipHash,
                'user_agent' => $this->userAgent ? substr($this->userAgent, 0, 255) : null,
                'user_id' => $this->userId,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
