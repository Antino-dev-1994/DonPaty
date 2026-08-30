<?php

namespace App\Modules\People;

use App\Modules\People\Domain\Models\Person;
use App\Modules\People\Infrastructure\Policies\PersonPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PeopleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Person::class, PersonPolicy::class);
        $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');
    }
}
