<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberExportController
{
    public function __invoke(Request $request): StreamedResponse
    {
        Log::channel('daily')->info('Subscriber export', ['user_id' => $request->user()?->id, 'ip' => $request->ip()]);
        $filename = 'abonnes-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Email', 'Nom', 'Date']);
            Subscriber::chunk(500, function ($subscribers) use ($handle): void {
                foreach ($subscribers as $s) {
                    fputcsv($handle, [$s->id, $s->email, $s->name ?? '', $s->created_at->format('Y-m-d')]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
