<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Application\CreateAuthorizationRequest;
use App\Modules\Identity\Application\DecideAuthorizationRequest;
use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
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
use App\Modules\Recipes\Domain\Models\RecipeVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DonPatyProductionSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $version = RecipeVersion::query()->whereHas('recipe', fn ($query) => $query->where('code', DonPatyCatalogSeeder::RECIPE_CODE))->applicableOn($today->toDateString())->sole();
        if (ProductionOrder::query()->where('recipe_version_id', $version->id)->whereDate('planned_for', $today)->exists()) {
            return;
        }

        $maria = User::query()->where('email', DonPatyPeopleSeeder::MARIA_EMAIL)->sole();
        $kevin = User::query()->where('email', DonPatyPeopleSeeder::KEVIN_EMAIL)->sole();
        $compatible = $version->compatibleProducts()->whereHas('presentation', fn ($query) => $query->where('sku', DonPatyCatalogSeeder::TAJADO_SKU))->sole();

        $order = app(PlanProduction::class)->execute(new PlanProductionData(
            recipeVersionId: $version->id,
            plannedFor: $today,
            flourQuantityKg: '6',
            laborMethod: LaborMethod::AuthorizedManual,
            responsiblePersonId: $maria->person_id,
            creator: $maria,
            outputs: [new PlannedOutputData($compatible->id, '21.6')],
        ));
        app(StartProduction::class)->execute($order, $maria);
        $order = $order->fresh(['consumptions']);

        $laborAuthorization = app(CreateAuthorizationRequest::class)->execute(
            'production.manual_labor', 'production.authorize-manual-labor', $order, $maria,
            'Mano de obra acordada para este lote de pan tajado: $5.000.', 120,
        );
        app(DecideAuthorizationRequest::class)->execute($laborAuthorization, $kevin, AuthorizationStatus::Approved, 'Autorizado para el lote inicial.');

        app(CompleteProduction::class)->execute($order, new CompleteProductionData(
            completedAt: now(),
            actualDoughQuantityKg: '11.029',
            wasteQuantityKg: '0',
            actor: $maria,
            consumptions: $order->consumptions->map(fn ($consumption) => new ActualConsumptionData(
                consumptionId: $consumption->id,
                actualQuantity: $this->actualQuantity($consumption->item->code, $consumption->calculated_quantity),
                differenceReason: $this->differenceReason($consumption->item->code),
            ))->all(),
            outputs: [new ActualOutputData($compatible->id, '22', '11.029')],
            manualLaborAmount: 5000,
            manualLaborReason: 'Pago del panadero por la producción de un lote de 6 kg de harina.',
            manualLaborAuthorization: $laborAuthorization->fresh(),
        ));
    }

    private function actualQuantity(string $itemCode, string $calculated): string
    {
        return match ($itemCode) {
            'DP-HARINA' => '6.3',
            'DP-BOLSA-TAJADO' => '22',
            default => $calculated,
        };
    }

    private function differenceReason(string $itemCode): ?string
    {
        return match ($itemCode) {
            'DP-HARINA' => 'Se usaron 0,300 kg adicionales durante el cilindrado para equilibrar la absorción de agua; el lote rindió 22 panes.',
            'DP-BOLSA-TAJADO' => 'Se empacaron 22 panes terminados; la planeación estándar era de 21,6 unidades equivalentes.',
            default => null,
        };
    }
}
