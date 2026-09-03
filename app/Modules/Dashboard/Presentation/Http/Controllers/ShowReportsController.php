<?php

namespace App\Modules\Dashboard\Presentation\Http\Controllers;

use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Dashboard\Application\BusinessPerformanceQuery;
use App\Modules\Dashboard\Application\Data\ReportDateRange;
use App\Modules\Dashboard\Application\HouseholdReportQuery;
use App\Modules\Dashboard\Application\InventoryValuationQuery;
use App\Modules\Dashboard\Application\ObligationsQuery;
use App\Modules\Dashboard\Application\ProductMarginQuery;
use App\Modules\Dashboard\Application\ProductionPerformanceQuery;
use App\Modules\Dashboard\Presentation\Http\Requests\ReportFilterRequest;
use App\Modules\People\Domain\Models\Person;
use Inertia\Inertia;
use Inertia\Response;

class ShowReportsController
{
    public function __invoke(
        ReportFilterRequest $request,
        BusinessPerformanceQuery $business,
        ProductMarginQuery $margins,
        ProductionPerformanceQuery $production,
        InventoryValuationQuery $inventory,
        ObligationsQuery $obligations,
        HouseholdReportQuery $household,
    ): Response {
        $default = ReportDateRange::month();
        $range = ReportDateRange::dates(
            $request->input('from', $default->from->format('Y-m-d')),
            $request->input('to', $default->to->format('Y-m-d')),
        );
        $user = $request->user();
        $canFinancial = $user->hasPermission('reports.view-financial');
        $canOperational = $user->hasPermission('reports.view-operational');
        $canHousehold = $user->hasPermission('household.view-own') || $user->hasPermission('household.view-all');

        return Inertia::render('reports/Index', [
            'filters' => [
                'from' => $range->from->format('Y-m-d'),
                'to' => $range->to->format('Y-m-d'),
                'presentation_id' => $request->input('presentation_id'),
                'person_id' => $request->input('person_id'),
            ],
            'permissions' => ['financial' => $canFinancial, 'operational' => $canOperational, 'household' => $canHousehold],
            'business' => $canFinancial ? $business->execute($range) : null,
            'margins' => $canFinancial ? $margins->execute($range, $request->input('presentation_id')) : [],
            'obligations' => $canFinancial ? $obligations->execute($request->input('person_id')) : null,
            'production' => $canOperational ? $production->execute($range, $canFinancial) : null,
            'inventory' => $canOperational ? $inventory->execute($request->input('presentation_id'), $canFinancial) : null,
            'household' => $canHousehold ? $household->execute($range, $user) : null,
            'presentations' => ProductPresentation::query()->with('item:id,name')->where('is_active', true)->where(fn ($query) => $query->where('is_sellable', true)->orWhere('is_stockable', true))->orderBy('name')->get()->map(fn ($item) => ['id' => $item->id, 'name' => $item->item->name.' · '.$item->name]),
            'people' => $canFinancial || $user->hasPermission('household.view-all') ? Person::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']) : [],
        ]);
    }
}
