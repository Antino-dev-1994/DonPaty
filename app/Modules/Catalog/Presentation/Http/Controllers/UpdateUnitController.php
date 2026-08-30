<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\Data\UnitData;
use App\Modules\Catalog\Application\UpdateUnit;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Presentation\Http\Requests\SaveUnitRequest;
use Illuminate\Http\RedirectResponse;

class UpdateUnitController extends Controller
{
    public function __invoke(SaveUnitRequest $request, Unit $unit, UpdateUnit $action): RedirectResponse
    {
        $action->execute($unit, UnitData::fromArray($request->validated()));

        return back()->with('success', 'Unidad actualizada correctamente.');
    }
}
