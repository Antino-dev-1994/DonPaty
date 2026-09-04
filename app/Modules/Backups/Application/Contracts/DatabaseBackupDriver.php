<?php

namespace App\Modules\Backups\Application\Contracts;

interface DatabaseBackupDriver
{
    public function extension(): string;

    public function create(string $destination): void;

    public function restore(string $source): void;
}
