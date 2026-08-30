<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Catalog\Domain\Models\Unit;

class EnsureCatalogReferenceData
{
    public function execute(): void
    {
        foreach ($this->units() as $attributes) {
            Unit::query()->updateOrCreate(['code' => $attributes['code']], $attributes);
        }
    }

    /** @return list<array<string, mixed>> */
    private function units(): array
    {
        return [
            ['code' => 'g', 'name' => 'Gramo', 'dimension' => 'mass', 'scale_to_base' => 1, 'precision' => 3, 'is_active' => true],
            ['code' => 'kg', 'name' => 'Kilogramo', 'dimension' => 'mass', 'scale_to_base' => 1000, 'precision' => 3, 'is_active' => true],
            ['code' => 'ml', 'name' => 'Mililitro', 'dimension' => 'volume', 'scale_to_base' => 1, 'precision' => 3, 'is_active' => true],
            ['code' => 'l', 'name' => 'Litro', 'dimension' => 'volume', 'scale_to_base' => 1000, 'precision' => 3, 'is_active' => true],
            ['code' => 'und', 'name' => 'Unidad', 'dimension' => 'count', 'scale_to_base' => 1, 'precision' => 0, 'is_active' => true],
            ['code' => 'doc', 'name' => 'Docena', 'dimension' => 'count', 'scale_to_base' => 12, 'precision' => 2, 'is_active' => true],
        ];
    }
}
