<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\CreateUnitConversion;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Presentation\Http\Requests\StoreUnitConversionRequest;
use Illuminate\Http\RedirectResponse;

class StoreUnitConversionController extends Controller
{
    public function __invoke(StoreUnitConversionRequest $request, CreateUnitConversion $action): RedirectResponse
    {
        $action->execute(
            Unit::query()->findOrFail($request->validated('from_unit_id')),
            Unit::query()->findOrFail($request->validated('to_unit_id')),
        );

        return back()->with('success', 'Conversión guardada correctamente.');
    }
}
