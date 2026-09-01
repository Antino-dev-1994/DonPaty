<?php

namespace Database\Seeders;

use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(EnsureAccessControlCatalog::class)->execute();
        $this->call(CatalogReferenceSeeder::class);
        $this->call(FinanceReferenceSeeder::class);

        if (app()->environment(['local', 'testing'])) {
            $this->call(DevelopmentOwnerSeeder::class);
        }
    }
}
