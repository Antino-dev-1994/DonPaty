<?php

namespace App\Modules\Identity;

use App\Models\User;
use App\Modules\Identity\Infrastructure\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::before(function (User $user, string $ability): ?bool {
            if (! $user->isActive()) {
                return false;
            }

            return $user->hasPermission($ability) ? true : null;
        });

        $this->loadRoutesFrom(__DIR__.'/Presentation/routes.php');
    }
}
