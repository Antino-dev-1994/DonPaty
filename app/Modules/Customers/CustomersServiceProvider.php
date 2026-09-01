<?php

namespace App\Modules\Customers;

use Illuminate\Support\ServiceProvider;

class CustomersServiceProvider extends ServiceProvider
{
    public function boot(): void { $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php'); }
}
