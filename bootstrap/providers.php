<?php

use App\Modules\Dashboard\DashboardServiceProvider;
use App\Modules\Audit\AuditServiceProvider;
use App\Modules\Identity\IdentityServiceProvider;
use App\Modules\People\PeopleServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    IdentityServiceProvider::class,
    AuditServiceProvider::class,
    DashboardServiceProvider::class,
    PeopleServiceProvider::class,
];
