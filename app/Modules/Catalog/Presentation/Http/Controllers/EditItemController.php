<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Enums\ItemType;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditItemController extends Controller
{
    public function __invoke(Request $request, Item $item): Response
    {
        abort_unless($request->user()->hasPermission('catalog.manage'), 403);
        $item->load(['baseUnit:id,code,name', 'presentations.stockUnit:id,code']);

        return Inertia::render('catalog/items/Edit', [
            'item' => [
                ...$item->only(['id', 'code', 'name', 'base_unit_id', 'minimum_stock', 'allow_negative_stock', 'is_active', 'notes']),
                'type' => $item->type->value,
                'base_unit' => $item->baseUnit,
                'presentations' => $item->presentations->map(fn ($presentation) => [
                    ...$presentation->only(['id', 'sku', 'name', 'conversion_to_item_base', 'is_purchasable', 'is_sellable', 'is_stockable', 'is_active', 'minimum_sale_price']),
                    'stock_unit' => $presentation->stockUnit->code,
                    'is_package' => $presentation->isPackage(),
                ]),
            ],
            'units' => Unit::query()->where('is_active', true)->orderBy('dimension')->orderBy('name')->get(['id', 'code', 'name', 'dimension']),
            'types' => collect(ItemType::cases())->map(fn ($type) => ['value' => $type->value, 'label' => $type->label()]),
        ]);
    }
}
