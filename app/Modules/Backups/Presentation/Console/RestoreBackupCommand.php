<?php

namespace App\Modules\Backups\Presentation\Console;

use App\Modules\Backups\Application\RestoreBackup;
use App\Modules\Backups\Domain\Models\BackupRun;
use Illuminate\Console\Command;

class RestoreBackupCommand extends Command
{
    protected $signature = 'backups:restore {backup : ULID del respaldo} {--confirm= : Frase exacta RESTAURAR-ULID}';
    protected $description = 'Restaura un respaldo verificado creando antes un respaldo de seguridad';

    public function handle(RestoreBackup $restore): int
    {
        $backup = BackupRun::query()->findOrFail((string) $this->argument('backup'));
        $expected = 'RESTAURAR-'.$backup->id;
        if (! hash_equals($expected, (string) $this->option('confirm'))) {
            $this->error("Confirmación inválida. Usa --confirm={$expected}");

            return self::FAILURE;
        }

        $this->warn('La aplicación entrará en mantenimiento y se creará un respaldo de seguridad previo.');
        $result = $restore->execute($backup);
        $this->info('Restauración completada. Respaldo previo: '.$result['safety_backup_path']);

        return self::SUCCESS;
    }
}
