<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlotPlanController
{
    public function __invoke(Request $request, Plot $plot): StreamedResponse|Response
    {
        abort_unless(! empty($plot->plan_pdf_path), 404);

        // Front: only serve if lot's program is published or user has permission
        if (! $plot->program->is_published && ! $request->user()?->can('plots.view')) {
            abort(404);
        }

        $disk = Storage::disk('local');
        $publicDisk = Storage::disk('public');

        // Support legacy public + new private storage
        if ($disk->exists($plot->plan_pdf_path)) {
            return $disk->download($plot->plan_pdf_path, basename($plot->plan_pdf_path));
        }

        if ($publicDisk->exists($plot->plan_pdf_path)) {
            return $publicDisk->download($plot->plan_pdf_path, basename($plot->plan_pdf_path));
        }

        abort(404);
    }
}
