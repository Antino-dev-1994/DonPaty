<?php

namespace App\Modules\Catalog\Application\Data;

final readonly class UnitData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $dimension,
        public string $scaleToBase,
        public int $precision,
        public bool $isActive,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: mb_strtolower($data['code']),
            name: $data['name'],
            dimension: $data['dimension'],
            scaleToBase: (string) $data['scale_to_base'],
            precision: (int) $data['precision'],
            isActive: (bool) $data['is_active'],
        );
    }

    public function attributes(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'dimension' => $this->dimension,
            'scale_to_base' => $this->scaleToBase,
            'precision' => $this->precision,
            'is_active' => $this->isActive,
        ];
    }
}
