<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevelopmentOwnerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $person = Person::query()->firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Usuario de prueba',
                    'notes' => 'Propietario local creado por el seeder de desarrollo.',
                    'is_active' => true,
                ],
            );

            $person->classifications()->firstOrCreate(
                [
                    'classification' => PersonClassificationType::Owner,
                    'effective_to' => null,
                ],
                ['effective_from' => today()],
            );

            $user = User::query()->firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'person_id' => $person->id,
                    'name' => 'Usuario de prueba',
                    'password' => 'password',
                ],
            );

            if ($user->person_id === null) {
                $user->update(['person_id' => $person->id]);
            }
            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->roles()->syncWithoutDetaching(
                Role::query()->where('name', 'owner')->pluck('id'),
            );
        });
    }
}
