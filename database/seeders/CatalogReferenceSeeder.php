<?php

namespace Database\Seeders;

use App\Modules\Catalog\Application\EnsureCatalogReferenceData;
use Illuminate\Database\Seeder;

class CatalogReferenceSeeder extends Seeder
{
    public function run(): void
    {
        app(EnsureCatalogReferenceData::class)->execute();
    }
}
