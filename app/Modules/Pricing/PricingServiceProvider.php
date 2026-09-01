<?php

namespace App\Modules\Pricing;

use Illuminate\Support\ServiceProvider;

class PricingServiceProvider extends ServiceProvider
{
    public function boot(): void { $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php'); }
}
