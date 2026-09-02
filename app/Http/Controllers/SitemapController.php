<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', 3600, function (): string {
            $urls = [];

            $base = rtrim(config('app.url'), '/');

            // Statiques
            $statics = [
                '' => ['priority' => '1.0', 'freq' => 'daily'],
                'a-propos' => ['priority' => '0.8', 'freq' => 'monthly'],
                'services' => ['priority' => '0.9', 'freq' => 'weekly'],
                'realisations' => ['priority' => '0.9', 'freq' => 'weekly'],
                'lotissements' => ['priority' => '0.9', 'freq' => 'weekly'],
                'actualites' => ['priority' => '0.7', 'freq' => 'weekly'],
                'contact' => ['priority' => '0.6', 'freq' => 'monthly'],
            ];
            foreach ($statics as $path => $meta) {
                $urls[] = [
                    'loc' => $path === '' ? $base.'/' : $base.'/'.$path,
                    'lastmod' => now()->toAtomString(),
                    'priority' => $meta['priority'],
                    'freq' => $meta['freq'],
                ];
            }

            // Programmes publiés
            foreach (Program::published()->orderBy('updated_at', 'desc')->get(['slug', 'updated_at']) as $prog) {
                $urls[] = [
                    'loc' => $base.'/lotissements/'.$prog->slug,
                    'lastmod' => $prog->updated_at->toAtomString(),
                    'priority' => '0.8',
                    'freq' => 'weekly',
                ];
            }

            // Projets publiés
            foreach (Project::published()->orderBy('updated_at', 'desc')->get(['slug', 'updated_at']) as $proj) {
                $urls[] = [
                    'loc' => $base.'/realisations/'.$proj->slug,
                    'lastmod' => $proj->updated_at->toAtomString(),
                    'priority' => '0.8',
                    'freq' => 'weekly',
                ];
            }

            // Posts publiés
            foreach (Post::published()->orderBy('updated_at', 'desc')->get(['slug', 'updated_at']) as $post) {
                $urls[] = [
                    'loc' => $base.'/actualites/'.$post->slug,
                    'lastmod' => $post->updated_at->toAtomString(),
                    'priority' => '0.6',
                    'freq' => 'weekly',
                ];
            }

            // Services dynamiques (via ServiceType enum)
            foreach (\App\Enums\ServiceType::cases() as $service) {
                $urls[] = [
                    'loc' => $base.'/services/'.$service->value,
                    'lastmod' => now()->toAtomString(),
                    'priority' => '0.7',
                    'freq' => 'monthly',
                ];
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach ($urls as $u) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.e($u['loc'])."</loc>\n";
                $xml .= '    <lastmod>'.$u['lastmod']."</lastmod>\n";
                $xml .= '    <changefreq>'.$u['freq']."</changefreq>\n";
                $xml .= '    <priority>'.$u['priority']."</priority>\n";
                $xml .= "  </url>\n";
            }
            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
