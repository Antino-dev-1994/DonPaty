<?php

namespace App\Modules\Catalog\Application\Data;

final readonly class ItemData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $type,
        public string $baseUnitId,
        public string $minimumStock,
        public bool $allowNegativeStock,
        public bool $isActive,
        public ?string $notes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: mb_strtoupper($data['code']),
            name: $data['name'],
            type: $data['type'],
            baseUnitId: $data['base_unit_id'],
            minimumStock: (string) $data['minimum_stock'],
            allowNegativeStock: (bool) $data['allow_negative_stock'],
            isActive: (bool) $data['is_active'],
            notes: $data['notes'] ?? null,
        );
    }

    public function attributes(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'base_unit_id' => $this->baseUnitId,
            'minimum_stock' => $this->minimumStock,
            'allow_negative_stock' => $this->allowNegativeStock,
            'is_active' => $this->isActive,
            'notes' => $this->notes,
        ];
    }
}
