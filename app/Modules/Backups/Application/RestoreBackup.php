<?php

namespace App\Modules\Backups\Application;

use App\Modules\Backups\Domain\Models\BackupRun;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class RestoreBackup
{
    public function __construct(
        private readonly VerifyBackup $verify,
        private readonly CreateBackup $createBackup,
        private readonly ResolveDatabaseBackupDriver $drivers,
        private readonly BackupWorkspace $workspace,
    ) {}

    public function execute(BackupRun $backup): array
    {
        $manifest = $this->verify->execute($backup);
        $safetyBackup = $this->createBackup->execute();
        $work = $this->workspace->create();
        $log = [
            'target_backup_id' => $backup->id,
            'safety_backup_id' => $safetyBackup->id,
            'safety_backup_path' => $safetyBackup->path,
            'started_at' => now()->toIso8601String(),
            'status' => 'running',
        ];

        try {
            $archive = "{$work}/backup.zip";
            $this->workspace->copyFromDisk($backup->disk, $backup->path, $archive);
            $zip = new ZipArchive;
            if ($zip->open($archive) !== true) {
                throw new RuntimeException('No fue posible abrir el respaldo para restaurarlo.');
            }
            $wasDownForMaintenance = app()->isDownForMaintenance();
            try {
                $databasePath = "{$work}/database.".$this->drivers->execute()->extension();
                $this->extractEntry($zip, $manifest['database']['archive_path'], $databasePath);
                if (! $wasDownForMaintenance) {
                    Artisan::call('down', ['--retry' => 60]);
                }
                $this->drivers->execute()->restore($databasePath);
                foreach ($manifest['attachments'] ?? [] as $attachment) {
                    $stream = $zip->getStream($attachment['archive_path']);
                    if (! is_resource($stream) || ! Storage::disk($attachment['disk'])->put($attachment['storage_path'], $stream)) {
                        is_resource($stream) && fclose($stream);
                        throw new RuntimeException("No fue posible restaurar {$attachment['storage_path']}.");
                    }
                    fclose($stream);
                }
            } finally {
                $zip->close();
                if (! $wasDownForMaintenance) {
                    Artisan::call('up');
                }
            }

            $log['status'] = 'completed';
            $log['completed_at'] = now()->toIso8601String();

            return $log;
        } catch (Throwable $exception) {
            $log['status'] = 'failed';
            $log['failed_at'] = now()->toIso8601String();
            $log['error'] = $exception->getMessage();
            throw $exception;
        } finally {
            Storage::disk((string) config('backups.disk'))->put(
                trim((string) config('backups.directory'), '/').'/restorations/'.now()->format('Ymd-His').'-'.$backup->id.'.json',
                json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            );
            $this->workspace->destroy($work);
        }
    }

    private function extractEntry(ZipArchive $zip, string $entry, string $destination): void
    {
        $input = $zip->getStream($entry);
        $output = fopen($destination, 'wb');
        if (! is_resource($input) || ! is_resource($output)) {
            is_resource($input) && fclose($input);
            is_resource($output) && fclose($output);
            throw new RuntimeException("No fue posible extraer {$entry}.");
        }
        try {
            stream_copy_to_stream($input, $output);
        } finally {
            fclose($input);
            fclose($output);
        }
    }
}
