<?php

namespace App\Modules\People\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Support\Facades\DB;

class CreatePerson
{
    public function __construct(
        private readonly SyncPersonClassifications $syncClassifications,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(PersonData $data): Person
    {
        return DB::transaction(function () use ($data): Person {
            $person = Person::create($data->attributes());
            $this->syncClassifications->execute($person, $data->classifications);
            $this->audit->execute('person.created', $person, after: $person->fresh('classifications')->toArray());

            return $person;
        });
    }
}
