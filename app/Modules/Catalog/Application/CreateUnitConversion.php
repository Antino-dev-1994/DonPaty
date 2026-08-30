<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Domain\Models\UnitConversion;
use App\Modules\Catalog\Domain\Services\UnitConverter;
use DomainException;

class CreateUnitConversion
{
    public function __construct(
        private readonly UnitConverter $converter,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(Unit $from, Unit $to): UnitConversion
    {
        if ($from->is($to)) {
            throw new DomainException('Selecciona dos unidades diferentes.');
        }

        $conversion = UnitConversion::query()->updateOrCreate(
            ['from_unit_id' => $from->id, 'to_unit_id' => $to->id],
            ['factor' => $this->converter->factor($from, $to), 'is_active' => true],
        );
        $this->audit->execute('catalog.unit_conversion_saved', $conversion, after: $conversion->toArray());

        return $conversion;
    }
}
