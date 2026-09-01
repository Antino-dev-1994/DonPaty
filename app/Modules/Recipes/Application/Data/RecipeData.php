<?php

namespace App\Modules\Recipes\Application\Data;

final readonly class RecipeData
{
    public function __construct(public string $code, public string $name, public ?string $description, public bool $isActive, public RecipeVersionData $version) {}
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self { return new self(mb_strtoupper($data['code']), $data['name'], $data['description'] ?? null, (bool) $data['is_active'], RecipeVersionData::fromArray($data)); }
}
