<?php

namespace App\Modules\Backups;

use App\Modules\Backups\Presentation\Console\RestoreBackupCommand;
use App\Modules\Backups\Presentation\Console\CreateBackupCommand;
use Illuminate\Support\ServiceProvider;

class BackupsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');
        if ($this->app->runningInConsole()) {
            $this->commands([CreateBackupCommand::class, RestoreBackupCommand::class]);
        }
    }
}
