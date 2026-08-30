<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Application\Data\ItemData;
use App\Modules\Catalog\Domain\Models\Item;
use DomainException;

class UpdateItem
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(Item $item, ItemData $data): Item
    {
        if ($item->base_unit_id !== $data->baseUnitId && $item->presentations()->exists()) {
            throw new DomainException('No se puede cambiar la unidad base de un artículo con presentaciones.');
        }

        $before = $item->toArray();
        $item->update($data->attributes());
        $this->audit->execute('catalog.item_updated', $item, before: $before, after: $item->toArray());

        return $item;
    }
}
