<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Application\Data\UnitData;
use App\Modules\Catalog\Domain\Models\Unit;

class CreateUnit
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(UnitData $data): Unit
    {
        $unit = Unit::create($data->attributes());
        $this->audit->execute('catalog.unit_created', $unit, after: $unit->toArray());

        return $unit;
    }
}
