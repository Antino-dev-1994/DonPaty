<?php

namespace App\Modules\People\Application;

use App\Modules\People\Domain\Models\Person;
use Illuminate\Support\Carbon;

class SyncPersonClassifications
{
    /** @param list<string> $classifications */
    public function execute(Person $person, array $classifications): void
    {
        $today = Carbon::today();
        $active = $person->classifications()->whereNull('effective_to')->get();

        foreach ($active as $classification) {
            if (! in_array($classification->classification->value, $classifications, true)) {
                $classification->update(['effective_to' => $today]);
            }
        }

        foreach ($classifications as $classification) {
            if (! $active->contains(fn ($current) => $current->classification->value === $classification)) {
                $person->classifications()->create([
                    'classification' => $classification,
                    'effective_from' => $today,
                ]);
            }
        }
    }
}
