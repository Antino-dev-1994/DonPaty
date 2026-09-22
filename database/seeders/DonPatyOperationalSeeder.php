<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Seeds the confirmed master data and the first real operating day. */
class DonPatyOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DonPatyPeopleSeeder::class,
            DonPatyCatalogSeeder::class,
            DonPatyRealOperationSeeder::class,
        ]);
    }
}
