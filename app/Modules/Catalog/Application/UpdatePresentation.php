<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Application\Data\PresentationData;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Domain\Models\Unit;
use DomainException;

class UpdatePresentation
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(ProductPresentation $presentation, PresentationData $data): ProductPresentation
    {
        $presentation->loadMissing('item.baseUnit');
        $unit = Unit::query()->findOrFail($data->stockUnitId);

        if ($presentation->item->baseUnit->dimension !== $unit->dimension) {
            throw new DomainException('La unidad de la presentación debe tener la misma dimensión de la unidad base.');
        }

        $before = $presentation->toArray();
        $presentation->update($data->attributes());
        $this->audit->execute('catalog.presentation_updated', $presentation, before: $before, after: $presentation->toArray());

        return $presentation;
    }
}
