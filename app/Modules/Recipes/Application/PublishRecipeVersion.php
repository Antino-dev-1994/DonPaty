<?php

namespace App\Modules\Recipes\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Recipes\Domain\Enums\RecipeVersionStatus;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

class PublishRecipeVersion
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    public function execute(RecipeVersion $version, CarbonInterface $effectiveFrom, User $activator): RecipeVersion
    {
        return DB::transaction(function () use ($version, $effectiveFrom, $activator): RecipeVersion {
            $version = RecipeVersion::query()->lockForUpdate()->findOrFail($version->id);
            if (! $version->isDraft() || $version->ingredients()->count() < 2 || ! $version->compatibleProducts()->exists()) throw new DomainException('Solo puede publicarse un borrador completo.');
            $latest = RecipeVersion::query()->where('recipe_id', $version->recipe_id)->where('status', RecipeVersionStatus::Published)->lockForUpdate()->latest('effective_from')->first();
            if ($latest?->effective_from && $effectiveFrom->lte($latest->effective_from)) throw new DomainException('La nueva vigencia debe comenzar después de la versión publicada más reciente.');
            if ($latest) $latest->update(['effective_to' => $effectiveFrom->copy()->subDay()]);
            $version->update(['status' => RecipeVersionStatus::Published, 'effective_from' => $effectiveFrom, 'effective_to' => null, 'activated_by' => $activator->id, 'activated_at' => now()]);
            $this->audit->execute('recipes.version_published', $version, $activator, after: $version->fresh()->toArray());

            return $version->fresh();
        });
    }
}
