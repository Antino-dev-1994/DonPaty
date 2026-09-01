<?php

namespace App\Modules\Recipes\Domain\Services;

use App\Modules\Catalog\Domain\Enums\ItemType;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Domain\Services\UnitConverter;
use App\Modules\Recipes\Application\Data\RecipeVersionData;
use App\Modules\Recipes\Domain\Enums\IngredientRole;
use DomainException;

class RecipeVersionValidator
{
    public function __construct(private readonly UnitConverter $converter) {}

    public function validate(RecipeVersionData $data): void
    {
        if (bccomp($data->referenceFlourQuantity, '0', 6) <= 0 || bccomp($data->expectedDoughYield, '0', 6) <= 0) throw new DomainException('La harina de referencia y el rendimiento deben ser positivos.');
        $referenceUnit = Unit::query()->findOrFail($data->referenceFlourUnitId); $yieldUnit = Unit::query()->findOrFail($data->yieldUnitId);
        if ($referenceUnit->dimension->value !== 'mass' || $yieldUnit->dimension->value !== 'mass') throw new DomainException('Harina y rendimiento deben expresarse en unidades de masa.');
        $flours = array_values(array_filter($data->ingredients, fn ($line) => $line->role === IngredientRole::Flour));
        if (count($flours) !== 1 || count($data->ingredients) < 2) throw new DomainException('La versión debe tener una harina de referencia y al menos otro ingrediente.');

        foreach ($data->ingredients as $ingredient) {
            $item = Item::query()->where('is_active', true)->findOrFail($ingredient->itemId); $unit = Unit::query()->findOrFail($ingredient->unitId);
            if (bccomp($ingredient->quantity, '0', 6) <= 0) throw new DomainException('Las cantidades de ingredientes deben ser positivas.');
            if ($ingredient->presentationId && ! ProductPresentation::query()->where('item_id', $item->id)->where('id', $ingredient->presentationId)->exists()) throw new DomainException('La presentación del ingrediente no pertenece al artículo.');
            if ($ingredient->role === IngredientRole::Flour) {
                if ($unit->dimension->value !== 'mass' || bccomp((string) $ingredient->bakerPercentage, '100', 4) !== 0) throw new DomainException('La harina debe ser masa y tener 100 % panadero.');
                $referenceInIngredientUnit = $this->converter->convert($data->referenceFlourQuantity, $referenceUnit, $unit);
                if (bccomp($ingredient->quantity, $referenceInIngredientUnit, 4) !== 0) throw new DomainException('La cantidad de harina debe coincidir con la referencia de la versión.');
            }
        }

        if ($data->compatibleProducts === []) throw new DomainException('La versión debe incluir al menos un producto compatible.');
        foreach ($data->compatibleProducts as $product) {
            $presentation = ProductPresentation::query()->with('item')->where('is_active', true)->findOrFail($product->presentationId);
            $unit = Unit::query()->findOrFail($product->doughWeightUnitId);
            if ($presentation->item->type !== ItemType::FinishedProduct || $unit->dimension->value !== 'mass') throw new DomainException('Los productos compatibles deben ser productos terminados y usar peso de masa.');
            if (bccomp($product->doughWeightPerUnit, '0', 6) <= 0 || bccomp($product->costWeightFactor, '0', 6) <= 0) throw new DomainException('El peso de masa y el factor de costo deben ser positivos.');
        }
    }
}
