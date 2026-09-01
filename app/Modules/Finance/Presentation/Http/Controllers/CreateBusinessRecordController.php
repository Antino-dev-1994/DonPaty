<?php

namespace App\Modules\Finance\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CostAccounting\Domain\Enums\CostPeriodStatus;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\Finance\Domain\Enums\FinancialScope;
use App\Modules\Finance\Domain\Models\ExpenseRecord;
use App\Modules\Finance\Domain\Models\FinancialAccount;
use App\Modules\Finance\Domain\Models\FinancialCategory;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateBusinessRecordController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('finance.manage'), 403);
        $periods = CostPeriod::query()
            ->with(['poolEntries' => fn ($query) => $query->whereNotIn('id', ExpenseRecord::query()->whereNotNull('cost_pool_entry_id')->select('cost_pool_entry_id'))])
            ->where('status', CostPeriodStatus::Open)
            ->orderByDesc('year')->orderByDesc('month')->get();

        return Inertia::render('finance/records/Create', [
            'categories' => FinancialCategory::query()
                ->where('scope', FinancialScope::Business)
                ->where('is_active', true)
                ->orderBy('record_type')->orderBy('name')
                ->get()
                ->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->record_type->value,
                    'cost_type' => $category->cost_type?->value,
                ]),
            'people' => Person::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'accounts' => FinancialAccount::query()->where('accepts_payments', true)->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'costPeriods' => $periods->map(fn ($period) => [
                'id' => $period->id,
                'label' => $period->label(),
                'pool_entries' => $period->poolEntries->map(fn ($entry) => [
                    'id' => $entry->id,
                    'cost_type' => $entry->cost_type->value,
                    'amount' => $entry->amount,
                    'effective_at' => $entry->effective_at->format('Y-m-d'),
                ]),
            ]),
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }
}
