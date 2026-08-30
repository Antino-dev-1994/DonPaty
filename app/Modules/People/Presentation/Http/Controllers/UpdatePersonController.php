<?php

namespace App\Modules\People\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Application\UpdatePerson;
use App\Modules\People\Domain\Models\Person;
use App\Modules\People\Presentation\Http\Requests\UpdatePersonRequest;
use Illuminate\Http\RedirectResponse;

class UpdatePersonController extends Controller
{
    public function __invoke(UpdatePersonRequest $request, Person $person, UpdatePerson $action): RedirectResponse
    {
        $action->execute($person, PersonData::fromArray($request->validated()));

        return back()->with('success', 'Persona actualizada correctamente.');
    }
}
