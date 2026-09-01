<?php

use App\Modules\Dashboard\DashboardServiceProvider;
use App\Modules\Audit\AuditServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Identity\IdentityServiceProvider;
use App\Modules\Inventory\InventoryServiceProvider;
use App\Modules\Finance\FinanceServiceProvider;
use App\Modules\People\PeopleServiceProvider;
use App\Modules\Purchasing\PurchasingServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    IdentityServiceProvider::class,
    AuditServiceProvider::class,
    CatalogServiceProvider::class,
    InventoryServiceProvider::class,
    FinanceServiceProvider::class,
    DashboardServiceProvider::class,
    PeopleServiceProvider::class,
    PurchasingServiceProvider::class,
];
