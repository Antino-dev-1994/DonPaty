<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\CostAccounting\Application\Data\OpenCostPeriodData;
use App\Modules\CostAccounting\Application\Data\UtilityCostData;
use App\Modules\CostAccounting\Application\OpenCostPeriod;
use App\Modules\CostAccounting\Domain\Models\CostPeriod;
use App\Modules\CostAccounting\Domain\Enums\UtilityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DonPatyCostPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        if (CostPeriod::query()->where('year', $today->year)->where('month', $today->month)->exists()) {
            return;
        }

        $previousMonth = $today->copy()->subMonth();
        $actor = User::query()->where('email', DonPatyPeopleSeeder::KEVIN_EMAIL)->sole();
        app(OpenCostPeriod::class)->execute(new OpenCostPeriodData(
            year: $today->year,
            month: $today->month,
            opener: $actor,
            utilities: [
                new UtilityCostData(UtilityType::Electricity, $previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth(), $today, 0, '100', '0', null, 'DP-LUZ-NO-INCLUIDA', 0, 'Electricidad no informada; se excluye del costo inicial.'),
                new UtilityCostData(UtilityType::Gas, $previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth(), $today, 220000, '100', '0', '1', 'DP-BOMBONA-220000', 834, 'Equivale a $5.004 por lote de 6 kg por redondeo de la tarifa entera por kg.'),
                new UtilityCostData(UtilityType::Water, $previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth(), $today, 0, '100', '0', null, 'DP-AGUA-NO-INCLUIDA', 0, 'El agua se controla como materia prima; su costo no fue informado.'),
            ],
            standardLaborRatePerKg: 0,
            laborRateReason: 'La mano de obra se registra por lote mediante autorización: $5.000.',
        ));
    }
}
