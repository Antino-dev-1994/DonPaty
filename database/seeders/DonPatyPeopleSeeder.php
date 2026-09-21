<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Database\Seeder;

class DonPatyPeopleSeeder extends Seeder
{
    public const KEVIN_EMAIL = 'kevin.patino@donpaty.local';
    public const MARIA_EMAIL = 'maria.penaloza@donpaty.local';
    public const INITIAL_PASSWORD = 'DonPaty2026!';

    public function run(): void
    {
        $this->createUser('Kevin Andres Patiño Grimaldos', self::KEVIN_EMAIL, 'DP-ADMIN-001', ['administrator']);
        $this->createUser('Maria Paola Peñaloza', self::MARIA_EMAIL, 'DP-OPER-001', ['operations_manager']);
    }

    /** @param list<string> $roles */
    private function createUser(string $name, string $email, string $documentNumber, array $roles): void
    {
        $person = Person::query()->firstOrCreate(['email' => $email], [
            'name' => $name, 'document_type' => 'CC', 'document_number' => $documentNumber,
            'phone' => null, 'notes' => 'Usuario operativo inicial de DonPaty.', 'is_active' => true,
        ]);
        $person->classifications()->firstOrCreate([
            'classification' => PersonClassificationType::Employee, 'effective_to' => null,
        ], ['effective_from' => today()]);

        $user = User::query()->firstOrCreate(['email' => $email], [
            'person_id' => $person->id, 'name' => $name, 'password' => self::INITIAL_PASSWORD,
        ]);
        $user->forceFill(['person_id' => $person->id, 'name' => $name, 'email_verified_at' => now()])->save();
        $user->roles()->sync(Role::query()->whereIn('name', $roles)->pluck('id')->all());
    }
}
