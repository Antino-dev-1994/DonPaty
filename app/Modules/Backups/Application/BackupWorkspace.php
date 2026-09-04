<?php

namespace App\Modules\Backups\Application;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class BackupWorkspace
{
    public function create(): string
    {
        $path = storage_path('app/backup-work/'.Str::uuid());
        File::ensureDirectoryExists($path);

        return $path;
    }

    public function destroy(string $path): void
    {
        if (str_starts_with($path, storage_path('app/backup-work/'))) {
            File::deleteDirectory($path);
        }
    }

    public function copyFromDisk(string $disk, string $path, string $destination): void
    {
        $input = Storage::disk($disk)->readStream($path);
        if (! is_resource($input)) {
            throw new RuntimeException("No fue posible leer {$path}.");
        }
        File::ensureDirectoryExists(dirname($destination));
        $output = fopen($destination, 'wb');
        if (! is_resource($output)) {
            fclose($input);
            throw new RuntimeException('No fue posible preparar el archivo temporal.');
        }
        try {
            stream_copy_to_stream($input, $output);
        } finally {
            fclose($input);
            fclose($output);
        }
    }
}
