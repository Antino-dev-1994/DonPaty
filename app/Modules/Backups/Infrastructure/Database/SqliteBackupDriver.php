<?php

namespace App\Modules\Backups\Infrastructure\Database;

use App\Modules\Backups\Application\Contracts\DatabaseBackupDriver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class SqliteBackupDriver implements DatabaseBackupDriver
{
    public function extension(): string
    {
        return 'sqlite';
    }

    public function create(string $destination): void
    {
        File::ensureDirectoryExists(dirname($destination));
        File::delete($destination);
        $quoted = DB::connection()->getPdo()->quote($destination);
        DB::connection()->unprepared("VACUUM INTO {$quoted}");
    }

    public function restore(string $source): void
    {
        $database = (string) config('database.connections.sqlite.database');
        if ($database === '' || $database === ':memory:') {
            throw new RuntimeException('La base SQLite configurada no puede restaurarse desde archivo.');
        }

        DB::disconnect();
        if (! File::copy($source, $database)) {
            throw new RuntimeException('No fue posible restaurar la base SQLite.');
        }
        DB::purge();
    }
}
