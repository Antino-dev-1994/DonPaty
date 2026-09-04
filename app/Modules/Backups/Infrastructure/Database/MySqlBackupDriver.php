<?php

namespace App\Modules\Backups\Infrastructure\Database;

use App\Modules\Backups\Application\Contracts\DatabaseBackupDriver;
use RuntimeException;
use Symfony\Component\Process\Process;

class MySqlBackupDriver implements DatabaseBackupDriver
{
    public function extension(): string
    {
        return 'sql';
    }

    public function create(string $destination): void
    {
        $connection = config('database.connections.mysql');
        $command = [
            (string) config('backups.mysql_dump_binary'),
            '--single-transaction', '--routines', '--triggers', '--skip-comments',
            '--host='.(string) $connection['host'], '--port='.(string) $connection['port'],
            '--user='.(string) $connection['username'], '--result-file='.$destination,
            (string) $connection['database'],
        ];
        $process = new Process($command, null, ['MYSQL_PWD' => (string) $connection['password']], null, 600);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException('Falló mysqldump: '.$process->getErrorOutput());
        }
    }

    public function restore(string $source): void
    {
        $connection = config('database.connections.mysql');
        $input = fopen($source, 'rb');
        if (! is_resource($input)) {
            throw new RuntimeException('No fue posible leer el respaldo SQL.');
        }
        $process = new Process([
            (string) config('backups.mysql_binary'), '--host='.(string) $connection['host'],
            '--port='.(string) $connection['port'], '--user='.(string) $connection['username'],
            (string) $connection['database'],
        ], null, ['MYSQL_PWD' => (string) $connection['password']], $input, 600);
        try {
            $process->run();
        } finally {
            fclose($input);
        }
        if (! $process->isSuccessful()) {
            throw new RuntimeException('Falló la restauración MySQL: '.$process->getErrorOutput());
        }
    }
}
