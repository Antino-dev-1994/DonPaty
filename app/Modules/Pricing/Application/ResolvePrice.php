<?php

namespace App\Modules\Pricing\Application;

use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\Pricing\Domain\Models\PriceList;
use App\Modules\Pricing\Domain\Models\PriceListItem;
use Carbon\CarbonInterface;
use DomainException;

class ResolvePrice
{
    /** @return array{price_list:PriceList,item:PriceListItem} */
    public function execute(string $presentationId, ?CustomerProfile $customer = null, ?string $priceListId = null, ?CarbonInterface $at = null): array
    {
        $at ??= now();
        $list = $priceListId ? PriceList::query()->findOrFail($priceListId) : $customer?->defaultPriceList;
        $list ??= PriceList::query()->where('is_default', true)->first();
        if (! $list || ! $list->isApplicableAt($at)) throw new DomainException('No existe una lista de precios vigente para la operación.');
        $item = $list->items()->where('presentation_id', $presentationId)->first();
        if (! $item) throw new DomainException('El producto no tiene precio en la lista seleccionada.');
        return ['price_list' => $list, 'item' => $item];
    }
}
