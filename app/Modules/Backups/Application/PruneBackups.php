<?php

namespace App\Modules\Backups\Application;

use App\Modules\Backups\Domain\Models\BackupRun;
use Illuminate\Support\Facades\Storage;

class PruneBackups
{
    public function execute(int $keep): int
    {
        $deleted = 0;
        BackupRun::query()->where('status', 'completed')->latest()->skip(max(1, $keep))->each(
            function (BackupRun $backup) use (&$deleted): void {
                if ($backup->path && Storage::disk($backup->disk)->delete($backup->path)) {
                    $backup->delete();
                    $deleted++;
                }
            },
        );

        return $deleted;
    }
}
