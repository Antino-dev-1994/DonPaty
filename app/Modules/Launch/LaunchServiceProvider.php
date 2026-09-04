<?php

namespace App\Modules\Launch;

use Illuminate\Support\ServiceProvider;

class LaunchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');
    }
}
