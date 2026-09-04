<?php

namespace App\Modules\Backups\Application;

use App\Modules\Backups\Application\Contracts\DatabaseBackupDriver;
use App\Modules\Backups\Infrastructure\Database\MySqlBackupDriver;
use App\Modules\Backups\Infrastructure\Database\SqliteBackupDriver;
use RuntimeException;

class ResolveDatabaseBackupDriver
{
    public function __construct(
        private readonly SqliteBackupDriver $sqlite,
        private readonly MySqlBackupDriver $mysql,
    ) {}

    public function execute(): DatabaseBackupDriver
    {
        return match (config('database.default')) {
            'sqlite' => $this->sqlite,
            'mysql', 'mariadb' => $this->mysql,
            default => throw new RuntimeException('El motor de base de datos no tiene estrategia de respaldo.'),
        };
    }
}
