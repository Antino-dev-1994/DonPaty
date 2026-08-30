<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\CreateUnit;
use App\Modules\Catalog\Application\Data\UnitData;
use App\Modules\Catalog\Presentation\Http\Requests\SaveUnitRequest;
use Illuminate\Http\RedirectResponse;

class StoreUnitController extends Controller
{
    public function __invoke(SaveUnitRequest $request, CreateUnit $action): RedirectResponse
    {
        $action->execute(UnitData::fromArray($request->validated()));

        return back()->with('success', 'Unidad creada correctamente.');
    }
}
