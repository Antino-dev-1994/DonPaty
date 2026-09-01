<?php

namespace App\Modules\Pricing\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Pricing\Application\Data\PriceListData;
use App\Modules\Pricing\Domain\Models\PriceList;
use DomainException;
use Illuminate\Support\Facades\DB;

class SavePriceList
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(PriceListData $data, User $actor, ?PriceList $priceList = null): PriceList
    {
        if ($data->endsAt && $data->startsAt && $data->endsAt->lt($data->startsAt)) throw new DomainException('La fecha final no puede ser anterior a la inicial.');
        if ($data->items === []) throw new DomainException('La lista debe contener al menos un producto.');

        return DB::transaction(function () use ($data, $actor, $priceList): PriceList {
            $before = $priceList?->load('items')->toArray();
            if ($data->isDefault) PriceList::query()->where('id', '!=', $priceList?->id)->update(['is_default' => false]);
            $attributes = ['name' => $data->name, 'type' => $data->type, 'starts_at' => $data->startsAt, 'ends_at' => $data->endsAt, 'is_default' => $data->isDefault, 'is_active' => $data->isActive];
            $priceList ? $priceList->update($attributes) : $priceList = PriceList::create($attributes);
            $priceList->items()->delete();
            foreach ($data->items as $item) {
                if ($item['price'] < 0 || ($item['minimum_price'] !== null && $item['minimum_price'] < 0) || ($item['minimum_price'] !== null && $item['price'] < $item['minimum_price'])) throw new DomainException('El precio de lista no puede ser inferior al mínimo.');
                $priceList->items()->create($item);
            }
            $this->audit->execute($before ? 'pricing.list_updated' : 'pricing.list_created', $priceList, $actor, before: $before, after: $priceList->fresh('items')->toArray());
            return $priceList->fresh('items');
        });
    }
}
