<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Enums\UnitDimension;
use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Catalog\Domain\Models\UnitConversion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListUnitsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('catalog.view'), 403);

        return Inertia::render('catalog/units/Index', [
            'units' => Unit::query()->orderBy('dimension')->orderBy('scale_to_base')->get()->map(fn (Unit $unit) => [
                ...$unit->only(['id', 'code', 'name', 'scale_to_base', 'precision', 'is_active']),
                'dimension' => $unit->dimension->value,
                'dimension_label' => $unit->dimension->label(),
            ]),
            'conversions' => UnitConversion::query()->with(['fromUnit:id,code,name', 'toUnit:id,code,name'])->orderByDesc('created_at')->get(),
            'dimensions' => collect(UnitDimension::cases())->map(fn ($dimension) => ['value' => $dimension->value, 'label' => $dimension->label()]),
            'canManage' => $request->user()->hasPermission('catalog.manage'),
        ]);
    }
}
