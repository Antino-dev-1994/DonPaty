<?php

namespace App\Modules\Dashboard\Application;

use App\Models\User;
use App\Modules\CashManagement\Application\FinancialAccountBalance;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Finance\Domain\Enums\FinancialAccountType;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Orders\Domain\Enums\ProductionDemandStatus;
use App\Modules\Orders\Domain\Models\ProductionDemand;
use App\Modules\Orders\Domain\Models\SalesOrder;
use App\Modules\Production\Domain\Enums\ProductionStatus;
use App\Modules\Production\Domain\Models\ProductionOrder;
use Carbon\CarbonImmutable;

class DashboardOverviewQuery
{
    public function __construct(
        private readonly BusinessPerformanceQuery $business,
        private readonly ProductionPerformanceQuery $production,
        private readonly InventoryValuationQuery $inventory,
        private readonly ObligationsQuery $obligations,
        private readonly HouseholdReportQuery $household,
        private readonly FinancialAccountBalance $balances,
    ) {}

    /** @return array<string, mixed> */
    public function execute(User $user): array
    {
        $now = CarbonImmutable::now();
        $canFinancial = $user->hasPermission('reports.view-financial');
        $canOperational = $user->hasPermission('reports.view-operational');
        $canHousehold = $user->hasPermission('household.view-own') || $user->hasPermission('household.view-all');
        $today = new ReportDateRange($now->startOfDay(), $now->endOfDay());
        $month = ReportDateRange::month($now->format('Y-m'));
        $inventory = $canOperational ? $this->inventory->execute(includeValue: $canFinancial) : null;
        $obligations = $canFinancial ? $this->obligations->execute() : null;
        $household = $canHousehold ? $this->household->execute($month, $user) : null;
        $upcomingOrders = $this->upcomingOrders($canOperational);
        $demand = $canOperational ? $this->pendingDemand() : null;
        $costPeriod = $canOperational && $user->hasPermission('cost-periods.view') ? [...$this->costPeriod(), 'can_manage' => $user->hasPermission('cost-periods.manage')] : null;

        return [
            'permissions' => ['financial' => $canFinancial, 'operational' => $canOperational, 'household' => $canHousehold],
            'roles' => $user->roles->pluck('label')->values()->all(),
            'financial' => $canFinancial ? [
                'today' => $this->business->execute($today),
                'month' => $this->business->execute($month),
                'cash' => $this->cashBalances(),
                'obligations' => $obligations,
            ] : null,
            'operational' => $canOperational ? [
                'production' => $this->production->execute($today, $canFinancial),
                'active_productions' => $this->activeProductions(),
                'demand' => $demand,
                'upcoming_orders' => $upcomingOrders,
                'inventory' => [
                    'total_value' => $canFinancial ? $inventory['total_value'] : null,
                    'low_count' => $inventory['low_count'],
                    'negative_count' => $inventory['negative_count'],
                    'alerts' => collect($inventory['rows'])->filter(fn ($row) => $row['is_low'] || $row['is_negative'])->take(10)->values()->all(),
                ],
                'cost_period' => $costPeriod,
            ] : null,
            'household' => $household,
            'alert_count' => ($inventory['low_count'] ?? 0) + ($inventory['negative_count'] ?? 0)
                + collect($upcomingOrders)->where('is_overdue', true)->count()
                + (($obligations['receivables_overdue'] ?? 0) > 0 ? 1 : 0)
                + (($obligations['payables_overdue'] ?? 0) > 0 ? 1 : 0)
                + ($household['pending_requests'] ?? 0)
                + ($demand['count'] ?? 0)
                + ($costPeriod && ! $costPeriod['exists'] ? 1 : 0),
        ];
    }

    /** @return array{accounts:list<array<string, int|string>>, total:int} */
    private function cashBalances(): array
    {
        $accounts = FinancialAccount::query()->where('scope', FinancialScope::Business)->where('account_type', FinancialAccountType::Asset)->where('accepts_payments', true)->where('is_active', true)->orderBy('code')->get()
            ->map(fn (FinancialAccount $account) => ['id' => $account->id, 'name' => $account->name, 'balance' => $this->balances->execute($account->id)]);

        return ['accounts' => $accounts->all(), 'total' => (int) $accounts->sum('balance')];
    }

    /** @return list<array<string, int|float|string|null>> */
    private function activeProductions(): array
    {
        return ProductionOrder::query()->with(['recipeVersion.recipe:id,name', 'responsiblePerson:id,name'])
            ->whereIn('status', [ProductionStatus::Planned, ProductionStatus::InProgress])
            ->orderBy('planned_for')->limit(10)->get()->map(fn (ProductionOrder $order) => [
                'id' => $order->id,
                'document' => $order->document_number,
                'recipe' => $order->recipeVersion->recipe->name,
                'planned_for' => $order->planned_for->format('Y-m-d H:i'),
                'status' => $order->status->label(),
                'flour_quantity' => (float) $order->flour_quantity,
                'responsible' => $order->responsiblePerson?->name,
            ])->all();
    }

    /** @return array{count:int, dough_quantity:float, flour_quantity:float} */
    private function pendingDemand(): array
    {
        $query = ProductionDemand::query()->where('status', ProductionDemandStatus::Pending);

        return [
            'count' => (clone $query)->count(),
            'dough_quantity' => round((float) (clone $query)->sum('pending_dough_quantity'), 6),
            'flour_quantity' => round((float) (clone $query)->sum('suggested_flour_quantity'), 6),
        ];
    }

    /** @return list<array<string, string|bool>> */
    private function upcomingOrders(bool $enabled = true): array
    {
        if (! $enabled) {
            return [];
        }

        return SalesOrder::query()->with('customer:id,name')->where('due_at', '<=', now()->addHours(24))->whereNotIn('status', ['delivered', 'cancelled'])->orderBy('due_at')->limit(10)->get()->map(fn (SalesOrder $order) => [
            'id' => $order->id,
            'document' => $order->document_number,
            'customer' => $order->customer->name,
            'due_at' => $order->due_at->format('Y-m-d H:i'),
            'is_overdue' => $order->due_at->isPast(),
            'status' => $order->status->label(),
        ])->all();
    }

    /** @return array{exists:bool,status:?string,label:string} */
    private function costPeriod(): array
    {
        $period = CostPeriod::query()->where('year', now()->year)->where('month', now()->month)->first();

        return ['exists' => (bool) $period, 'status' => $period?->status->value, 'label' => $period ? $period->status->label() : 'Sin abrir'];
    }
}
