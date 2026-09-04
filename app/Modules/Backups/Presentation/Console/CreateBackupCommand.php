<?php

namespace App\Modules\Backups\Presentation\Console;

use App\Modules\Backups\Application\CreateBackup;
use App\Modules\Backups\Application\PruneBackups;
use App\Modules\Backups\Application\VerifyBackup;
use Illuminate\Console\Command;

class CreateBackupCommand extends Command
{
    protected $signature = 'backups:create {--keep= : Cantidad de respaldos completos a conservar}';
    protected $description = 'Crea, verifica y aplica retención a los respaldos automáticos';

    public function handle(CreateBackup $create, VerifyBackup $verify, PruneBackups $prune): int
    {
        $backup = $create->execute();
        $verify->execute($backup);
        $keep = max(1, (int) ($this->option('keep') ?: config('backups.retention_count', 30)));
        $deleted = $prune->execute($keep);
        $this->info("Respaldo {$backup->id} creado y verificado; {$deleted} respaldo(s) antiguo(s) eliminado(s).");

        return self::SUCCESS;
    }
}
