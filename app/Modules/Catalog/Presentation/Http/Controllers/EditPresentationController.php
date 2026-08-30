<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditPresentationController extends Controller
{
    public function __invoke(Request $request, Item $item, ProductPresentation $presentation): Response
    {
        abort_unless($request->user()->hasPermission('catalog.manage'), 403);
        abort_unless($presentation->item_id === $item->id, 404);
        $item->load('baseUnit');
        $presentation->load('packageComponents.componentPresentation:id,item_id,sku,name');

        return Inertia::render('catalog/presentations/Edit', [
            'item' => $item->only(['id', 'code', 'name']),
            'presentation' => [
                ...$presentation->only(['id', 'sku', 'name', 'stock_unit_id', 'conversion_to_item_base', 'is_purchasable', 'is_sellable', 'is_stockable', 'is_active', 'barcode', 'minimum_sale_price']),
                'components' => $presentation->packageComponents->map(fn ($component) => [
                    'presentation_id' => $component->component_presentation_id,
                    'quantity' => $component->quantity,
                    'name' => $component->componentPresentation->name,
                ]),
            ],
            'units' => Unit::query()->where('is_active', true)->where('dimension', $item->baseUnit->dimension)->orderBy('scale_to_base')->get(['id', 'code', 'name']),
            'componentOptions' => $item->presentations()->whereKeyNot($presentation->id)->where('is_active', true)->orderBy('name')->get(['id', 'sku', 'name']),
        ]);
    }
}
