<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Inventory\Application\ConvertPackage;
use App\Modules\Inventory\Domain\Enums\PackageConversionType;
use App\Modules\Inventory\Presentation\Http\Requests\ConvertPackageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class StorePackageConversionController extends Controller
{
    public function __invoke(ConvertPackageRequest $request, ConvertPackage $action): RedirectResponse
    {
        $data = $request->validated();
        $action->execute(
            ProductPresentation::query()->findOrFail($data['package_presentation_id']),
            PackageConversionType::from($data['conversion_type']),
            (string) $data['quantity'],
            $request->user(),
            effectiveAt: Carbon::parse($data['effective_at'], config('regional.display_timezone'))->utc(),
        );

        return back()->with('success', 'Conversión de paquete completada.');
    }
}
