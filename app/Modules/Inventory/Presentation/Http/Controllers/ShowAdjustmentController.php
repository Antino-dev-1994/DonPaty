<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Inventory\Domain\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowAdjustmentController extends Controller
{
    public function __invoke(Request $request, InventoryAdjustment $adjustment): Response
    {
        abort_unless($request->user()->hasPermission('inventory.view'), 403);
        $adjustment->load(['creator:id,name', 'lines.presentation.item:id,name,allow_negative_stock', 'lines.presentation.stockUnit:id,code']);
        $authorization = AuthorizationRequest::query()
            ->with(['requester:id,name', 'approver:id,name'])
            ->where('resource_type', $adjustment->getMorphClass())->where('resource_id', $adjustment->id)
            ->whereIn('status', [AuthorizationStatus::Pending, AuthorizationStatus::Approved])->whereNull('used_at')->latest()->first();

        return Inertia::render('inventory/adjustments/Show', [
            'adjustment' => [
                ...$adjustment->only(['id', 'document_number', 'adjustment_type', 'reason']),
                'status' => $adjustment->status->value,
                'status_label' => $adjustment->status->label(),
                'effective_at' => $adjustment->effective_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
                'creator' => $adjustment->creator->name,
                'lines' => $adjustment->lines->map(fn ($line) => [
                    'id' => $line->id,
                    'presentation' => $line->presentation->item->name.' · '.$line->presentation->name,
                    'unit' => $line->presentation->stockUnit->code,
                    'expected_quantity' => $line->expected_quantity,
                    'counted_quantity' => $line->counted_quantity,
                    'difference_quantity' => $line->difference_quantity,
                    'unit_cost' => $line->unit_cost,
                    'allow_negative_stock' => $line->presentation->item->allow_negative_stock,
                ]),
            ],
            'authorization' => $authorization ? [
                'id' => $authorization->id,
                'status' => $authorization->status->value,
                'status_label' => $authorization->status->label(),
                'requester' => $authorization->requester->name,
                'approver' => $authorization->approver?->name,
            ] : null,
            'canAdjust' => $request->user()->hasPermission('inventory.adjust'),
        ]);
    }
}
