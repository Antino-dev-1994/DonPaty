<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Application\Data\UnitData;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\UnitConversion;
use DomainException;

class UpdateUnit
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(Unit $unit, UnitData $data): Unit
    {
        $isUsed = $unit->items()->exists()
            || ProductPresentation::query()->where('stock_unit_id', $unit->id)->exists()
            || UnitConversion::query()->where('from_unit_id', $unit->id)->orWhere('to_unit_id', $unit->id)->exists();

        if ($unit->dimension->value !== $data->dimension && $isUsed) {
            throw new DomainException('No se puede cambiar la dimensión de una unidad utilizada.');
        }

        $before = $unit->toArray();
        $unit->update($data->attributes());
        $this->audit->execute('catalog.unit_updated', $unit, before: $before, after: $unit->toArray());

        return $unit;
    }
}
