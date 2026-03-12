<?php

if (!function_exists('storage_url')) {
    /**
     * URL pública para archivos del disco 'public' (storage/app/public).
     * Usa la ruta _storage para que funcione con php artisan serve y en servidor sin symlink.
     */
    function storage_url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }
        return url('_storage/' . ltrim($path, '/'));
    }
}
