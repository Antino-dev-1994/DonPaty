<?php

use App\Modules\Dashboard\DashboardServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    DashboardServiceProvider::class,
];
