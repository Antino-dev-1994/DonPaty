<?php

use App\Modules\Dashboard\DashboardServiceProvider;
use App\Modules\Audit\AuditServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\CashManagement\CashManagementServiceProvider;
use App\Modules\CostAccounting\CostAccountingServiceProvider;
use App\Modules\Customers\CustomersServiceProvider;
use App\Modules\Identity\IdentityServiceProvider;
use App\Modules\Inventory\InventoryServiceProvider;
use App\Modules\Finance\FinanceServiceProvider;
use App\Modules\People\PeopleServiceProvider;
use App\Modules\Orders\OrdersServiceProvider;
use App\Modules\Purchasing\PurchasingServiceProvider;
use App\Modules\Production\ProductionServiceProvider;
use App\Modules\Pricing\PricingServiceProvider;
use App\Modules\Recipes\RecipesServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    IdentityServiceProvider::class,
    AuditServiceProvider::class,
    CatalogServiceProvider::class,
    CashManagementServiceProvider::class,
    CostAccountingServiceProvider::class,
    CustomersServiceProvider::class,
    InventoryServiceProvider::class,
    FinanceServiceProvider::class,
    DashboardServiceProvider::class,
    PeopleServiceProvider::class,
    OrdersServiceProvider::class,
    PurchasingServiceProvider::class,
    ProductionServiceProvider::class,
    PricingServiceProvider::class,
    RecipesServiceProvider::class,
];
