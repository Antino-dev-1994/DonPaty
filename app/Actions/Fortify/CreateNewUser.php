<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Modules\Identity\Application\EnsureAccessControlCatalog;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\People\Application\CreatePerson;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        abort_if(User::query()->exists(), 403, 'El registro público está cerrado. Un propietario debe crear los usuarios adicionales.');

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $this->ensureAccessControlCatalog->execute();
            $person = $this->createPerson->execute(new PersonData(
                name: $input['name'],
                documentType: null,
                documentNumber: null,
                email: $input['email'],
                phone: null,
                notes: 'Propietario inicial creado durante la configuración.',
                isActive: true,
                classifications: [PersonClassificationType::Owner->value],
            ));

            $user = User::create([
                'person_id' => $person->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);
            $user->roles()->attach(Role::query()->where('name', 'owner')->sole());

            return $user;
        });
    }
}
    public function __construct(
        private readonly CreatePerson $createPerson,
        private readonly EnsureAccessControlCatalog $ensureAccessControlCatalog,
    ) {}
