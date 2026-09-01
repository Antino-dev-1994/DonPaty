<?php

namespace App\Modules\Recipes;

use Illuminate\Support\ServiceProvider;

class RecipesServiceProvider extends ServiceProvider
{
    public function boot(): void { $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php'); }
}
