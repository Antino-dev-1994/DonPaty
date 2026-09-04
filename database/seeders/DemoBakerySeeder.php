<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use Database\Seeders\Demo\DemoCatalogSeeder;
use Database\Seeders\Demo\DemoCostAndHouseholdSeeder;
use Database\Seeders\Demo\DemoPeopleSeeder;
use Database\Seeders\Demo\DemoProductionSeeder;
use Database\Seeders\Demo\DemoPurchasingSeeder;
use Database\Seeders\Demo\DemoSalesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoBakerySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'lan', 'testing'])) {
            $this->command?->error('Los datos demostrativos solo pueden cargarse en local, lan o testing.');

            return;
        }

        app(EnsureAccessControlCatalog::class)->execute();
        $this->call([
            CatalogReferenceSeeder::class,
            FinanceReferenceSeeder::class,
            BusinessFinanceReferenceSeeder::class,
            HouseholdFinanceReferenceSeeder::class,
            PricingReferenceSeeder::class,
            DevelopmentOwnerSeeder::class,
        ]);

        if (User::query()->where('email', DemoPeopleSeeder::ADMIN_EMAIL)->exists()) {
            $this->command?->info('Los datos demostrativos de DonPaty ya están instalados.');

            return;
        }

        DB::transaction(function (): void {
            $this->call([
                DemoPeopleSeeder::class,
                DemoCatalogSeeder::class,
                DemoCostAndHouseholdSeeder::class,
                DemoPurchasingSeeder::class,
                DemoProductionSeeder::class,
                DemoSalesSeeder::class,
            ]);
        }, 3);
    }
}
