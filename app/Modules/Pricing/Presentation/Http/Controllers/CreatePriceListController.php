<?php

namespace App\Modules\Pricing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Pricing\Domain\Enums\PriceListType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatePriceListController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('prices.manage'), 403);
        return Inertia::render('pricing/Create', ['types' => collect(PriceListType::cases())->map(fn ($type) => ['value'=>$type->value,'label'=>$type->label()]), 'presentations' => $this->presentations()]);
    }
    private function presentations() { return ProductPresentation::query()->with('item:id,name')->where('is_sellable', true)->where('is_active', true)->orderBy('sku')->get()->map(fn ($p) => ['id'=>$p->id,'label'=>"{$p->item->name} — {$p->name} ({$p->sku})",'minimum_sale_price'=>$p->minimum_sale_price]); }
}
