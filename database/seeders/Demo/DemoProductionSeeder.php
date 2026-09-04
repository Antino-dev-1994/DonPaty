<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Modules\People\Domain\Models\Person;
use App\Modules\Production\Application\CompleteProduction;
use App\Modules\Production\Application\Data\ActualConsumptionData;
use App\Modules\Production\Application\Data\ActualOutputData;
use App\Modules\Production\Application\Data\CompleteProductionData;
use App\Modules\Production\Application\Data\PlanProductionData;
use App\Modules\Production\Application\Data\PlannedOutputData;
use App\Modules\Production\Application\PlanProduction;
use App\Modules\Production\Application\StartProduction;
use App\Modules\Production\Domain\Enums\LaborMethod;
use App\Modules\Production\Domain\Models\ProductionOrder;
use App\Modules\Recipes\Domain\Models\Recipe;
use App\Modules\Recipes\Domain\Models\RecipeCompatibleProduct;
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoProductionSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()->where('email', DemoPeopleSeeder::ADMIN_EMAIL)->sole();
        $responsible = Person::query()->where('document_number', 'DEMO-1002')->sole();
        $now = Carbon::now();

        $baseVersion = $this->version(DemoCatalogSeeder::BASE_RECIPE, $now);
        $this->produce(
            version: $baseVersion,
            actor: $actor,
            responsiblePersonId: $responsible->id,
            flourQuantity: '10',
            outputs: [
                ['DEMO-PAN-CAS-UND', '140', '8.4'],
                ['DEMO-PAN-BOL-UND', '110', '8.8'],
            ],
            actualDough: '17.6',
            waste: '0.4',
            plannedFor: $now->copy()->subHours(4),
        );

        $slicedVersion = $this->version(DemoCatalogSeeder::SLICED_RECIPE, $now);
        $this->produce(
            version: $slicedVersion,
            actor: $actor,
            responsiblePersonId: $responsible->id,
            flourQuantity: '8',
            outputs: [
                ['DEMO-PAN-TAJ-UND', '22', '14.08'],
            ],
            actualDough: '14.52',
            waste: '0.44',
            plannedFor: $now->copy()->subHours(2),
        );
    }

    /** @param list<array{string,string,string}> $outputs */
    private function produce(
        RecipeVersion $version,
        User $actor,
        string $responsiblePersonId,
        string $flourQuantity,
        array $outputs,
        string $actualDough,
        string $waste,
        Carbon $plannedFor,
    ): void {
        $compatible = $version->compatibleProducts()->with('presentation')->get()->keyBy('presentation.sku');
        $order = app(PlanProduction::class)->execute(new PlanProductionData(
            recipeVersionId: $version->id,
            plannedFor: $plannedFor,
            flourQuantityKg: $flourQuantity,
            laborMethod: LaborMethod::StandardPerKilogram,
            responsiblePersonId: $responsiblePersonId,
            creator: $actor,
            outputs: collect($outputs)->map(fn (array $output) => new PlannedOutputData(
                compatibleProductId: $compatible->get($output[0])->id,
                quantity: $output[1],
            ))->all(),
        ));

        app(StartProduction::class)->execute($order, $actor);
        $order = ProductionOrder::query()->with('consumptions')->findOrFail($order->id);
        app(CompleteProduction::class)->execute($order, new CompleteProductionData(
            completedAt: Carbon::now(),
            actualDoughQuantityKg: $actualDough,
            wasteQuantityKg: $waste,
            actor: $actor,
            consumptions: $order->consumptions->map(fn ($consumption) => new ActualConsumptionData(
                consumptionId: $consumption->id,
                actualQuantity: $consumption->calculated_quantity,
                differenceReason: null,
            ))->all(),
            outputs: collect($outputs)->map(fn (array $output) => new ActualOutputData(
                compatibleProductId: $compatible->get($output[0])->id,
                quantity: $output[1],
                doughQuantityKg: $output[2],
            ))->all(),
        ));
    }

    private function version(string $recipeCode, Carbon $date): RecipeVersion
    {
        $recipe = Recipe::query()->where('code', $recipeCode)->sole();

        return RecipeVersion::query()
            ->where('recipe_id', $recipe->id)
            ->applicableOn($date->toDateString())
            ->sole();
    }
}
