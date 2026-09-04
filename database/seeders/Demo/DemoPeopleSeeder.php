<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use App\Modules\Identity\Application\CreateUser;
use App\Modules\Identity\Application\Data\UserData;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Application\CreatePerson;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use Illuminate\Database\Seeder;

class DemoPeopleSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin.demo@donpaty.local';
    public const WORKER_EMAIL = 'panadera.demo@donpaty.local';
    public const RESIDENT_EMAIL = 'habitante.demo@donpaty.local';
    public const PASSWORD = 'DonPaty2026!';

    public function run(): void
    {
        $owner = User::query()->where('email', 'test@example.com')->sole();

        $this->createResident(
            name: 'Andrea Administradora',
            email: self::ADMIN_EMAIL,
            documentNumber: 'DEMO-1001',
            classifications: [PersonClassificationType::Resident],
            roles: ['administrator', 'resident'],
            actor: $owner,
        );
        $this->createResident(
            name: 'Patricia Panadera',
            email: self::WORKER_EMAIL,
            documentNumber: 'DEMO-1002',
            classifications: [PersonClassificationType::Resident, PersonClassificationType::Employee],
            roles: ['production', 'resident'],
            actor: $owner,
        );
        $this->createResident(
            name: 'Camila Habitante',
            email: self::RESIDENT_EMAIL,
            documentNumber: 'DEMO-1003',
            classifications: [PersonClassificationType::Resident],
            roles: ['resident'],
            actor: $owner,
        );
    }

    /** @param list<PersonClassificationType> $classifications @param list<string> $roles */
    private function createResident(
        string $name,
        string $email,
        string $documentNumber,
        array $classifications,
        array $roles,
        User $actor,
    ): void {
        $person = app(CreatePerson::class)->execute(new PersonData(
            name: $name,
            documentType: 'CC',
            documentNumber: $documentNumber,
            email: $email,
            phone: '300000'.substr($documentNumber, -4),
            notes: 'Persona creada para recorrer el ambiente demostrativo.',
            isActive: true,
            classifications: array_map(fn (PersonClassificationType $type) => $type->value, $classifications),
        ));

        $user = app(CreateUser::class)->execute(new UserData(
            personId: $person->id,
            name: $name,
            email: $email,
            password: self::PASSWORD,
            roleIds: Role::query()->whereIn('name', $roles)->pluck('id')->all(),
        ), $actor);

        $user->forceFill(['email_verified_at' => now()])->save();
    }
}
