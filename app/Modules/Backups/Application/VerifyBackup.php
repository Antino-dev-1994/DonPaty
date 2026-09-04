<?php

namespace App\Modules\Backups\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Backups\Domain\Models\BackupRun;
use RuntimeException;
use Throwable;
use ZipArchive;

class VerifyBackup
{
    public function __construct(
        private readonly BackupWorkspace $workspace,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(BackupRun $backup, ?User $actor = null): array
    {
        if ($backup->status !== 'completed' || ! $backup->path) {
            throw new RuntimeException('El respaldo no está disponible para verificación.');
        }
        $work = $this->workspace->create();
        try {
            $archive = "{$work}/backup.zip";
            $this->workspace->copyFromDisk($backup->disk, $backup->path, $archive);
            $this->assertHash($archive, (string) $backup->sha256, 'archivo ZIP');
            $zip = new ZipArchive;
            if ($zip->open($archive) !== true) {
                throw new RuntimeException('El respaldo no es un ZIP válido.');
            }
            try {
                $manifestJson = $zip->getFromName('manifest.json');
                if (! is_string($manifestJson)) {
                    throw new RuntimeException('El respaldo no contiene manifiesto.');
                }
                $manifest = json_decode($manifestJson, true, flags: JSON_THROW_ON_ERROR);
                if (($manifest['format_version'] ?? null) !== 1 || ($manifest['backup_id'] ?? null) !== $backup->id) {
                    throw new RuntimeException('El manifiesto no corresponde al respaldo seleccionado.');
                }
                $entries = array_merge([$manifest['database']], $manifest['attachments'] ?? []);
                foreach ($entries as $entry) {
                    $this->assertZipEntry($zip, $entry);
                }
            } finally {
                $zip->close();
            }
            $backup->update(['verification_status' => 'valid', 'verified_at' => now(), 'error_message' => null]);
            $this->audit->execute('backup.verified', $backup, $actor, after: ['verification_status' => 'valid']);

            return $manifest;
        } catch (Throwable $exception) {
            $backup->update(['verification_status' => 'invalid', 'verified_at' => now(), 'error_message' => $exception->getMessage()]);
            throw $exception;
        } finally {
            $this->workspace->destroy($work);
        }
    }

    private function assertHash(string $path, string $expected, string $label): void
    {
        $actual = hash_file('sha256', $path);
        if (! is_string($actual) || ! hash_equals($expected, $actual)) {
            throw new RuntimeException("Falló la integridad de {$label}.");
        }
    }

    private function assertZipEntry(ZipArchive $zip, array $entry): void
    {
        $stream = $zip->getStream((string) $entry['archive_path']);
        if (! is_resource($stream)) {
            throw new RuntimeException("Falta {$entry['archive_path']} en el respaldo.");
        }
        try {
            $context = hash_init('sha256');
            hash_update_stream($context, $stream);
            $actual = hash_final($context);
        } finally {
            fclose($stream);
        }
        if (! hash_equals((string) $entry['sha256'], $actual)) {
            throw new RuntimeException("Falló la integridad de {$entry['archive_path']}.");
        }
    }
}
