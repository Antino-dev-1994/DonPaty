<?php

namespace Database\Seeders;

use App\Modules\Pricing\Application\EnsureDefaultPriceLists;
use Illuminate\Database\Seeder;

class PricingReferenceSeeder extends Seeder
{
    public function run(): void { app(EnsureDefaultPriceLists::class)->execute(); }
}
