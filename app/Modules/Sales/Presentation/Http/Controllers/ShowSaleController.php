<?php

namespace App\Modules\Sales\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attachments\Application\AttachmentViewData;
use App\Modules\Identity\Domain\Models\AuthorizationRequest;
use App\Modules\Sales\Domain\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowSaleController extends Controller
{
    public function __invoke(Request $request, Sale $sale, AttachmentViewData $attachmentView): Response
    {
        $user = $request->user();
        abort_unless($user->hasPermission('sales.create') || $user->hasPermission('finance.view'), 403);

        $canViewMargin = $user->hasPermission('finance.view');
        $sale->load(['customer:id,name', 'order:id,document_number', 'lines.presentation.item:id,name', 'lines.priceOverride', 'receivable', 'returns']);
        $authorizations = AuthorizationRequest::query()
            ->where('resource_type', $sale->getMorphClass())
            ->where('resource_id', $sale->id)
            ->get();

        return Inertia::render('sales/Show', [
            'sale' => [
                ...$sale->only(['id', 'document_number', 'subtotal', 'discount', 'total', 'advance_applied', 'paid_amount', 'balance_amount', 'payment_plan']),
                'cost_of_goods_sold' => $canViewMargin ? $sale->cost_of_goods_sold : null,
                'gross_profit' => $canViewMargin ? $sale->gross_profit : null,
                'customer' => $sale->customer?->name ?? 'Consumidor final',
                'order' => $sale->order?->document_number,
                'sold_at' => $sale->sold_at->format('Y-m-d H:i'),
                'due_at' => $sale->due_at?->format('Y-m-d'),
                'status' => $sale->status->value,
                'status_label' => $sale->status->label(),
                'returned_amount' => $sale->returns->where('status', 'confirmed')->sum('total_refund'),
                'lines' => $sale->lines->map(fn ($line) => [
                    ...$line->only(['id', 'quantity', 'list_unit_price', 'applied_unit_price', 'discount', 'line_total', 'price_reason']),
                    'unit_cost' => $canViewMargin ? $line->unit_cost : null,
                    'total_cost' => $canViewMargin ? $line->total_cost : null,
                    'product' => "{$line->presentation->item->name} — {$line->presentation->name}",
                    'minimum_price' => $line->priceOverride?->minimum_price,
                ]),
                'returns' => $sale->returns->sortByDesc('returned_at')->values()->map(fn ($return) => [
                    'id' => $return->id,
                    'document_number' => $return->document_number,
                    'returned_at' => $return->returned_at->format('Y-m-d H:i'),
                    'total_refund' => $return->total_refund,
                    'reason' => $return->reason,
                ]),
            ],
            'authorizations' => $authorizations->map(fn ($authorization) => [
                'id' => $authorization->id,
                'permission' => $authorization->approval_permission,
                'status' => $authorization->status->value,
                'usable' => $authorization->isUsable(),
                'reason' => $authorization->reason,
            ]),
            'attachments' => $attachmentView->execute('sale', $sale, $user),
            'canConfirm' => $user->hasPermission('sales.create'),
            'canReturn' => $user->hasPermission('sales.return'),
            'canRequestAuthorization' => $user->hasPermission('authorizations.request'),
            'canViewMargin' => $canViewMargin,
        ]);
    }
}
