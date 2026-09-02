<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Store uploaded image and generate WebP + AVIF variants for performance.
     * Returns primary stored path (AVIF > WebP > original) if conversion succeeded.
     * Optionally deletes old file to avoid orphans.
     * Fallback gracieux si GD/Imagick manquants.
     */
    public static function storeOptimized(UploadedFile $file, string $directory, string $disk = 'public', ?string $oldPath = null): string
    {
        $originalPath = $file->store($directory, $disk);
        if (! is_string($originalPath)) {
            return '';
        }

        $finalPath = $originalPath;
        $generated = [];

        // Tentative WebP + AVIF via GD ou Imagick
        try {
            $content = Storage::disk($disk)->get($originalPath);
            if ($content !== null) {
                // 1. AVIF en priorité (meilleure compression)
                $avifPath = preg_replace('/\.[^.]+$/', '.avif', $originalPath);
                $avifPath = is_string($avifPath) ? $avifPath : $originalPath.'.avif';
                if ($avifPath !== $originalPath && self::tryConvert($content, $avifPath, 'avif', $disk)) {
                    $generated[] = $avifPath;
                    $finalPath = $avifPath; // AVIF prioritaire
                }

                // 2. WebP (fallback universel)
                $webpPath = preg_replace('/\.[^.]+$/', '.webp', $originalPath);
                $webpPath = is_string($webpPath) ? $webpPath : $originalPath.'.webp';
                if ($webpPath !== $originalPath && $webpPath !== $avifPath && self::tryConvert($content, $webpPath, 'webp', $disk)) {
                    $generated[] = $webpPath;
                    if ($finalPath === $originalPath) {
                        $finalPath = $webpPath;
                    }
                }

                // Si une variante a été créée, on peut supprimer l'original pour économiser
                if (! empty($generated)) {
                    Storage::disk($disk)->delete($originalPath);
                }
            }
        } catch (\Throwable $e) {
            // Fallback to original on any error
        }

        // Delete old file + ses variantes si différent
        if ($oldPath && $oldPath !== $finalPath && ! in_array($oldPath, $generated, true)) {
            self::delete($oldPath, $disk);
        }

        return $finalPath;
    }

    /**
     * Tente conversion vers $format (avif|webp) via GD puis Imagick.
     */
    private static function tryConvert(string $content, string $targetPath, string $format, string $disk): bool
    {
        // GD — support PNG/JPEG/GIF/WebP avec alpha
        if ($format === 'webp' && function_exists('imagewebp') && function_exists('imagecreatefromstring')) {
            try {
                $image = @imagecreatefromstring($content);
                if ($image !== false) {
                    // Préserve transparence PNG
                    if (function_exists('imagepalettetotruecolor')) {
                        @imagepalettetotruecolor($image);
                    }
                    @imagealphablending($image, false);
                    @imagesavealpha($image, true);
                    ob_start();
                    $ok = imagewebp($image, null, 82);
                    $out = ob_get_clean();
                    imagedestroy($image);
                    if ($ok && $out !== false) {
                        Storage::disk($disk)->put($targetPath, $out);

                        return true;
                    }
                }
            } catch (\Throwable $e) {
            }
        }
        if ($format === 'avif' && function_exists('imageavif') && function_exists('imagecreatefromstring')) {
            try {
                $image = @imagecreatefromstring($content);
                if ($image !== false) {
                    if (function_exists('imagepalettetotruecolor')) {
                        @imagepalettetotruecolor($image);
                    }
                    @imagealphablending($image, false);
                    @imagesavealpha($image, true);
                    ob_start();
                    $ok = imageavif($image, null, 50); // qualité 50 ~ bonne perf
                    $out = ob_get_clean();
                    imagedestroy($image);
                    if ($ok && $out !== false) {
                        Storage::disk($disk)->put($targetPath, $out);

                        return true;
                    }
                }
            } catch (\Throwable $e) {
            }
        }
        // Imagick fallback
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick;
                $imagick->readImageBlob($content);
                $imagick->setImageFormat($format);
                if ($format === 'webp') {
                    $imagick->setImageCompressionQuality(82);
                }
                if ($format === 'avif') {
                    $imagick->setImageCompressionQuality(50);
                }
                $imagick->stripImage();
                Storage::disk($disk)->put($targetPath, $imagick->getImagesBlob());
                $imagick->clear();

                return true;
            } catch (\Throwable $e) {
            }
        }

        return false;
    }

    public static function delete(?string $path, string $disk = 'public'): void
    {
        if (! $path) {
            return;
        }
        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
            foreach (['webp', 'avif'] as $ext) {
                $variant = preg_replace('/\.[^.]+$/', '.'.$ext, $path);
                if (is_string($variant) && $variant !== $path && Storage::disk($disk)->exists($variant)) {
                    Storage::disk($disk)->delete($variant);
                }
            }
            // Si path est déjà une variante, supprimer l'autre variante aussi
            if (str_ends_with($path, '.webp')) {
                $avif = preg_replace('/\.webp$/', '.avif', $path);
                if (is_string($avif) && Storage::disk($disk)->exists($avif)) {
                    Storage::disk($disk)->delete($avif);
                }
            }
            if (str_ends_with($path, '.avif')) {
                $webp = preg_replace('/\.avif$/', '.webp', $path);
                if (is_string($webp) && Storage::disk($disk)->exists($webp)) {
                    Storage::disk($disk)->delete($webp);
                }
            }
        } catch (\Throwable $e) {
            // silent
        }
    }

    /**
     * Generate responsive srcset string for stored image (AVIF + WebP + original).
     */
    public static function srcset(string $path, string $disk = 'public'): string
    {
        $urls = [];
        $base = preg_replace('/\.(webp|avif)$/', '', $path);
        $base = is_string($base) ? $base : $path;

        $avif = $base.'.avif';
        if (Storage::disk($disk)->exists($avif)) {
            $urls[] = Storage::disk($disk)->url($avif).' 1x';
        }
        $webp = $base.'.webp';
        if (Storage::disk($disk)->exists($webp)) {
            $urls[] = Storage::disk($disk)->url($webp).' 1x';
        }
        // Original si différent
        if (! str_ends_with($path, '.avif') && ! str_ends_with($path, '.webp') && Storage::disk($disk)->exists($path)) {
            $urls[] = Storage::disk($disk)->url($path).' 1x';
        } elseif (empty($urls)) {
            // Fallback : path lui-même si aucune variante
            return Storage::disk($disk)->url($path);
        }

        return implode(', ', $urls);
    }

    public static function url(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }

    /**
     * Génère balise <picture> avec sources AVIF/WebP + fallback.
     * Usage : {!! ImageService::picture($path, 'alt', ['class'=>'w-full']) !!}
     *
     * @param  array<string, string>  $attributes
     */
    public static function picture(string $path, string $alt = '', array $attributes = [], string $disk = 'public'): string
    {
        $base = preg_replace('/\.(webp|avif)$/', '', $path);
        $base = is_string($base) ? $base : $path;
        $avif = $base.'.avif';
        $webp = $base.'.webp';
        $attr = '';
        foreach ($attributes as $k => $v) {
            $attr .= ' '.e($k).'="'.e($v).'"';
        }

        $html = '<picture>';
        if (Storage::disk($disk)->exists($avif)) {
            $html .= '<source srcset="'.e(Storage::disk($disk)->url($avif)).'" type="image/avif">';
        }
        if (Storage::disk($disk)->exists($webp)) {
            $html .= '<source srcset="'.e(Storage::disk($disk)->url($webp)).'" type="image/webp">';
        }
        $fallback = Storage::disk($disk)->exists($path) ? Storage::disk($disk)->url($path) : (Storage::disk($disk)->exists($base) ? Storage::disk($disk)->url($base) : '');
        if ($fallback === '' && Storage::disk($disk)->exists($webp)) {
            $fallback = Storage::disk($disk)->url($webp);
        }
        $html .= '<img src="'.e($fallback).'" alt="'.e($alt).'" loading="lazy"'.$attr.'>';
        $html .= '</picture>';

        return $html;
    }

    public static function exists(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return true;
        }
        $base = preg_replace('/\.(webp|avif)$/', '', $path);
        $base = is_string($base) ? $base : $path;

        return Storage::disk($disk)->exists($base.'.webp') || Storage::disk($disk)->exists($base.'.avif');
    }
}
