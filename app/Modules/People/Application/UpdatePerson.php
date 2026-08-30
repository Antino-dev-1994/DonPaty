<?php

namespace App\Modules\People\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Support\Facades\DB;

class UpdatePerson
{
    public function __construct(
        private readonly SyncPersonClassifications $syncClassifications,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(Person $person, PersonData $data): Person
    {
        return DB::transaction(function () use ($person, $data): Person {
            $before = $person->load('classifications')->toArray();
            $person->update($data->attributes());
            $this->syncClassifications->execute($person, $data->classifications);
            $person->refresh()->load('classifications');
            $this->audit->execute('person.updated', $person, before: $before, after: $person->toArray());

            return $person;
        });
    }
}
