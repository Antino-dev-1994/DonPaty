<?php

namespace App\Modules\Purchasing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatePurchaseController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);

        return Inertia::render('purchasing/purchases/Create', [
            'suppliers' => SupplierProfile::query()->with('person:id,name')->where('is_active', true)->orderBy('trade_name')->get()
                ->map(fn (SupplierProfile $supplier) => [
                    'person_id' => $supplier->person_id,
                    'name' => $supplier->trade_name ?: $supplier->person->name,
                    'payment_term_days' => $supplier->default_payment_term_days,
                ]),
            'presentations' => ProductPresentation::query()->with(['item:id,name', 'stockUnit:id,code'])
                ->where('is_active', true)->where('is_purchasable', true)->orderBy('name')->get()
                ->map(fn (ProductPresentation $presentation) => [
                    'id' => $presentation->id, 'name' => "{$presentation->item->name} — {$presentation->name}",
                    'sku' => $presentation->sku, 'unit' => $presentation->stockUnit->code,
                ]),
            'paymentConditions' => collect(PurchasePaymentCondition::cases())->map(fn ($condition) => [
                'value' => $condition->value, 'label' => $condition->label(),
            ]),
            'today' => now()->toDateString(),
        ]);
    }
}
