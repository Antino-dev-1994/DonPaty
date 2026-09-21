<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Seeds only the master data needed before the first real operating day. */
class DonPatyOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DonPatyPeopleSeeder::class,
            DonPatyCatalogSeeder::class,
        ]);
    }
}
