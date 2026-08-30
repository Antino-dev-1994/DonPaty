<?php

namespace App\Modules\Catalog\Application\Data;

final readonly class PresentationData
{
    public function __construct(
        public string $sku,
        public string $name,
        public string $stockUnitId,
        public string $conversionToItemBase,
        public bool $isPurchasable,
        public bool $isSellable,
        public bool $isStockable,
        public bool $isActive,
        public ?string $barcode,
        public ?int $minimumSalePrice,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            sku: mb_strtoupper($data['sku']),
            name: $data['name'],
            stockUnitId: $data['stock_unit_id'],
            conversionToItemBase: (string) $data['conversion_to_item_base'],
            isPurchasable: (bool) $data['is_purchasable'],
            isSellable: (bool) $data['is_sellable'],
            isStockable: (bool) $data['is_stockable'],
            isActive: (bool) $data['is_active'],
            barcode: $data['barcode'] ?? null,
            minimumSalePrice: isset($data['minimum_sale_price']) ? (int) $data['minimum_sale_price'] : null,
        );
    }

    public function attributes(): array
    {
        return [
            'sku' => $this->sku,
            'name' => $this->name,
            'stock_unit_id' => $this->stockUnitId,
            'conversion_to_item_base' => $this->conversionToItemBase,
            'is_purchasable' => $this->isPurchasable,
            'is_sellable' => $this->isSellable,
            'is_stockable' => $this->isStockable,
            'is_active' => $this->isActive,
            'barcode' => $this->barcode,
            'minimum_sale_price' => $this->minimumSalePrice,
        ];
    }
}
