<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Application\Data\PresentationData;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use DomainException;

class CreatePresentation
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(Item $item, PresentationData $data): ProductPresentation
    {
        $this->ensureCompatibleUnit($item, $data->stockUnitId);
        $presentation = $item->presentations()->create($data->attributes());
        $this->audit->execute('catalog.presentation_created', $presentation, after: $presentation->toArray());

        return $presentation;
    }

    private function ensureCompatibleUnit(Item $item, string $unitId): void
    {
        $item->loadMissing('baseUnit');
        $unit = Unit::query()->findOrFail($unitId);

        if ($item->baseUnit->dimension !== $unit->dimension) {
            throw new DomainException('La unidad de la presentación debe tener la misma dimensión de la unidad base.');
        }
    }
}
