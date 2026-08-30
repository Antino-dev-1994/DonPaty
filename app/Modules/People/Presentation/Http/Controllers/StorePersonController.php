<?php

namespace App\Modules\People\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\People\Application\CreatePerson;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Presentation\Http\Requests\StorePersonRequest;
use Illuminate\Http\RedirectResponse;

class StorePersonController extends Controller
{
    public function __invoke(StorePersonRequest $request, CreatePerson $action): RedirectResponse
    {
        $person = $action->execute(PersonData::fromArray($request->validated()));

        return to_route('people.edit', $person)->with('success', 'Persona creada correctamente.');
    }
}
