<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorageController extends Controller
{
    /**
     * Sirve archivos del disco 'public' (storage/app/public).
     * Permite que las imágenes funcionen sin depender del symlink public/storage,
     * tanto en local como en servidor (p. ej. cuando storage:link no se ejecuta en deploy).
     */
    public function __invoke(string $path): BinaryFileResponse
    {
        // Evitar path traversal
        if (str_contains($path, '..') || str_starts_with($path, '/')) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $mimeType = $disk->mimeType($path);
        $fullPath = $disk->path($path);

        // Asegurar que el path resuelto está dentro del root del disco
        $root = realpath($disk->path(''));
        $resolved = realpath($fullPath);
        if ($root === false || $resolved === false || !str_starts_with($resolved, $root)) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
