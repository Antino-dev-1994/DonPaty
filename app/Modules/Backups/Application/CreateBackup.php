<?php

namespace App\Modules\Backups\Application;

use App\Models\User;
use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Backups\Domain\Models\BackupRun;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class CreateBackup
{
    public function __construct(
        private readonly ResolveDatabaseBackupDriver $drivers,
        private readonly BackupWorkspace $workspace,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(?User $creator = null): BackupRun
    {
        $disk = (string) config('backups.disk', 'local');
        $backup = BackupRun::create([
            'status' => 'running',
            'database_driver' => (string) config('database.default'),
            'disk' => $disk,
            'created_by' => $creator?->id,
        ]);
        $work = $this->workspace->create();

        try {
            $driver = $this->drivers->execute();
            $databasePath = "{$work}/database.{$driver->extension()}";
            $archivePath = "{$work}/backup.zip";
            $driver->create($databasePath);

            $zip = new ZipArchive;
            if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No fue posible crear el archivo ZIP del respaldo.');
            }

            $databaseEntry = 'database/database.'.$driver->extension();
            $zip->addFile($databasePath, $databaseEntry);
            $manifest = [
                'format_version' => 1,
                'backup_id' => $backup->id,
                'created_at' => now()->toIso8601String(),
                'database' => [
                    'driver' => config('database.default'),
                    'archive_path' => $databaseEntry,
                    'size' => filesize($databasePath),
                    'sha256' => hash_file('sha256', $databasePath),
                ],
                'attachments' => [],
            ];

            Attachment::query()->orderBy('id')->each(function (Attachment $attachment) use ($zip, &$manifest, $work): void {
                if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
                    throw new RuntimeException("Falta el adjunto {$attachment->id}; el respaldo se canceló.");
                }
                $temporary = "{$work}/attachments/{$attachment->id}";
                $this->workspace->copyFromDisk($attachment->disk, $attachment->path, $temporary);
                $archiveEntry = "attachments/{$attachment->id}/".basename($attachment->path);
                $zip->addFile($temporary, $archiveEntry);
                $manifest['attachments'][] = [
                    'id' => $attachment->id,
                    'disk' => $attachment->disk,
                    'storage_path' => $attachment->path,
                    'archive_path' => $archiveEntry,
                    'size' => filesize($temporary),
                    'sha256' => hash_file('sha256', $temporary),
                ];
            });

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            $zip->close();

            $storagePath = trim((string) config('backups.directory', 'backups'), '/').'/'.$backup->id.'.zip';
            $stream = fopen($archivePath, 'rb');
            if (! is_resource($stream) || ! Storage::disk($disk)->put($storagePath, $stream)) {
                is_resource($stream) && fclose($stream);
                throw new RuntimeException('No fue posible guardar el respaldo en el disco configurado.');
            }
            fclose($stream);

            $backup->update([
                'status' => 'completed', 'path' => $storagePath,
                'size' => filesize($archivePath), 'sha256' => hash_file('sha256', $archivePath),
                'manifest' => $manifest, 'verification_status' => 'pending',
            ]);
            $this->audit->execute('backup.created', $backup, $creator, after: $backup->only(['database_driver', 'size', 'sha256']));

            return $backup->fresh();
        } catch (Throwable $exception) {
            if (isset($zip) && $zip instanceof ZipArchive) {
                $zip->close();
            }
            $backup->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
            throw $exception;
        } finally {
            $this->workspace->destroy($work);
        }
    }
}
