<?php

namespace App\Modules\Pricing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Pricing\Domain\Enums\PriceListType;
use App\Modules\Pricing\Domain\Models\PriceList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditPriceListController extends Controller
{
    public function __invoke(Request $request, PriceList $priceList): Response
    {
        abort_unless($request->user()->hasPermission('prices.manage'),403); $priceList->load('items');
        $presentations=ProductPresentation::query()->with('item:id,name')->where('is_sellable',true)->where('is_active',true)->orderBy('sku')->get()->map(fn($p)=>['id'=>$p->id,'label'=>"{$p->item->name} — {$p->name} ({$p->sku})",'minimum_sale_price'=>$p->minimum_sale_price]);
        return Inertia::render('pricing/Edit',['priceList'=>[...$priceList->only(['id','name','is_default','is_active']),'type'=>$priceList->type->value,'starts_at'=>$priceList->starts_at?->format('Y-m-d\TH:i'),'ends_at'=>$priceList->ends_at?->format('Y-m-d\TH:i'),'items'=>$priceList->items->map->only(['presentation_id','price','minimum_price'])],'types'=>collect(PriceListType::cases())->map(fn($type)=>['value'=>$type->value,'label'=>$type->label()]),'presentations'=>$presentations]);
    }
}
