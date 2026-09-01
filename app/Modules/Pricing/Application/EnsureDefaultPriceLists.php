<?php

namespace App\Modules\Pricing\Application;

use App\Modules\Pricing\Domain\Enums\PriceListType;
use App\Modules\Pricing\Domain\Models\PriceList;

class EnsureDefaultPriceLists
{
    public function execute(): void
    {
        PriceList::query()->updateOrCreate(['name' => 'Minorista'], ['type' => PriceListType::Retail, 'is_default' => true, 'is_active' => true]);
        PriceList::query()->updateOrCreate(['name' => 'Mayorista'], ['type' => PriceListType::Wholesale, 'is_default' => false, 'is_active' => true]);
        PriceList::query()->updateOrCreate(['name' => 'Promocional'], ['type' => PriceListType::Promotional, 'is_default' => false, 'is_active' => true]);
    }
}
