<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\SyncPackageComponents;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Presentation\Http\Requests\SyncPackageComponentsRequest;
use Illuminate\Http\RedirectResponse;

class SyncPackageComponentsController extends Controller
{
    public function __invoke(SyncPackageComponentsRequest $request, Item $item, ProductPresentation $presentation, SyncPackageComponents $action): RedirectResponse
    {
        abort_unless($presentation->item_id === $item->id, 404);
        $action->execute($presentation, $request->validated('components'));

        return back()->with('success', 'Componentes del paquete actualizados.');
    }
}
