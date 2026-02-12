<?php

if (!function_exists('storage_asset')) {
    /**
     * Generate URL for a file in storage/app/public (works on cPanel without symlink).
     * Uses the storage/serve route so files are served by Laravel.
     */
    function storage_asset(string $path): string
    {
        return url('storage/serve/' . ltrim($path, '/'));
    }
}
