<?php

namespace App\Modules\Purchasing;

use Illuminate\Support\ServiceProvider;

class PurchasingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');
    }
}
