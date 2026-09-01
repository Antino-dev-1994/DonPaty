<?php

namespace App\Modules\Recipes\Application;

use App\Models\User;
use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Recipes\Application\Data\RecipeData;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use Illuminate\Support\Facades\DB;

class UpdateRecipeDraft
{
    public function __construct(private readonly SaveDraftRecipeVersion $saveVersion, private readonly RecordAuditEvent $audit) {}
    public function execute(Recipe $recipe, RecipeVersion $version, RecipeData $data, User $user): RecipeVersion
    {
        return DB::transaction(function () use ($recipe, $version, $data, $user): RecipeVersion {
            abort_unless($version->recipe_id === $recipe->id, 404); $before = $version->load(['ingredients', 'compatibleProducts'])->toArray();
            $recipe->update(['code' => $data->code, 'name' => $data->name, 'description' => $data->description, 'is_active' => $data->isActive]);
            $version = $this->saveVersion->execute($version, $data->version);
            $this->audit->execute('recipes.version_updated', $version, $user, before: $before, after: $version->toArray());
            return $version;
        });
    }
}
