<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryExportController
{
    public function __invoke(Request $request): StreamedResponse
    {
        // Audit RGPD
        Log::channel('daily')->info('Inquiry export', ['user_id' => $request->user()?->id, 'ip' => $request->ip(), 'filters' => $request->only(['status', 'type'])]);

        $status = $request->query('status');
        $type = $request->query('type');

        $query = Inquiry::query()->with(['program', 'plot'])->latest();

        if ($status && in_array($status, array_column(InquiryStatus::cases(), 'value'), true)) {
            $query->where('status', $status);
        }

        if ($type && in_array($type, array_column(InquiryType::cases(), 'value'), true)) {
            $query->where('inquiry_type', $type);
        }

        $filename = 'prospects-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            // BOM UTF-8 pour Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Date', 'Nom', 'Email', 'Téléphone', 'Type', 'Service', 'Statut', 'Programme', 'Lot', 'Message']);

            $query->chunk(500, function ($inquiries) use ($handle): void {
                foreach ($inquiries as $inquiry) {
                    fputcsv($handle, [
                        $inquiry->id,
                        $inquiry->created_at->format('Y-m-d H:i'),
                        $inquiry->name,
                        $inquiry->email,
                        $inquiry->phone,
                        $inquiry->inquiry_type->label(),
                        $inquiry->service_type?->label() ?? '-',
                        $inquiry->status->label(),
                        $inquiry->program ? $inquiry->program->title : '-',
                        $inquiry->plot ? $inquiry->plot->reference : '-',
                        str_replace(["\r", "\n"], ' ', (string) $inquiry->message),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
