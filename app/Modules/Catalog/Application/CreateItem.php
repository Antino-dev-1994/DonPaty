<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Application\Data\ItemData;
use App\Modules\Catalog\Domain\Models\Item;

class CreateItem
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(ItemData $data): Item
    {
        $item = Item::create($data->attributes());
        $this->audit->execute('catalog.item_created', $item, after: $item->toArray());

        return $item;
    }
}
