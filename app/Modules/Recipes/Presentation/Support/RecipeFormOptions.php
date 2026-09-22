<?php

namespace App\Modules\Recipes\Presentation\Support;

use App\Modules\Catalog\Domain\Enums\ItemType;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Recipes\Domain\Enums\IngredientRole;

class RecipeFormOptions
{
    /** @return array<string, mixed> */
    public function get(): array
    {
        return [
            'units' => Unit::query()->where('is_active', true)->orderBy('dimension')->orderBy('name')->get()->map(fn (Unit $unit) => ['id' => $unit->id, 'code' => $unit->code, 'name' => $unit->name, 'dimension' => $unit->dimension->value]),
            'items' => Item::query()->with('presentations:id,item_id,name,sku')->where('is_active', true)->whereIn('type', [ItemType::RawMaterial, ItemType::Supply, ItemType::Packaging, ItemType::Intermediate])->orderBy('name')->get()->map(fn (Item $item) => ['id' => $item->id, 'name' => $item->name, 'type' => $item->type->value, 'presentations' => $item->presentations]),
            'products' => ProductPresentation::query()->with('item:id,name,type')->where('is_active', true)->whereHas('item', fn ($query) => $query->whereIn('type', [ItemType::FinishedProduct, ItemType::Intermediate]))->orderBy('name')->get()->map(fn (ProductPresentation $presentation) => ['id' => $presentation->id, 'name' => "{$presentation->item->name} — {$presentation->name}", 'sku' => $presentation->sku]),
            'roles' => collect(IngredientRole::cases())->map(fn ($role) => ['value' => $role->value, 'label' => $role->label()]),
        ];
    }
}
