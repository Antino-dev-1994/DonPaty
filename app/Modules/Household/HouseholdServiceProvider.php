<?php

namespace App\Modules\Household;

use Illuminate\Support\ServiceProvider;

class HouseholdServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');
    }
}
