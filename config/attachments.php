<?php

use App\Modules\Finance\Domain\Models\ExpenseRecord;
use App\Modules\Finance\Domain\Models\IncomeRecord;
use App\Modules\Household\Domain\Models\FundRequest;
use App\Modules\Household\Domain\Models\HouseholdTransaction;
use App\Modules\Orders\Domain\Models\SalesOrder;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Purchasing\Domain\Models\Purchase;
use App\Modules\Sales\Domain\Models\Sale;

return [
    'disk' => env('ATTACHMENTS_DISK', 'local'),
    'maximum_size_kb' => (int) env('ATTACHMENTS_MAXIMUM_SIZE_KB', 10240),
    'resources' => [
        'sale' => Sale::class,
        'purchase' => Purchase::class,
        'production' => ProductionOrder::class,
        'order' => SalesOrder::class,
        'expense' => ExpenseRecord::class,
        'income' => IncomeRecord::class,
        'fund-request' => FundRequest::class,
        'household-transaction' => HouseholdTransaction::class,
    ],
];
