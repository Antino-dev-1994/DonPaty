<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\Data\PresentationData;
use App\Modules\Catalog\Application\UpdatePresentation;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Catalog\Presentation\Http\Requests\SavePresentationRequest;
use Illuminate\Http\RedirectResponse;

class UpdatePresentationController extends Controller
{
    public function __invoke(SavePresentationRequest $request, Item $item, ProductPresentation $presentation, UpdatePresentation $action): RedirectResponse
    {
        abort_unless($presentation->item_id === $item->id, 404);
        $action->execute($presentation, PresentationData::fromArray($request->validated()));

        return back()->with('success', 'Presentación actualizada correctamente.');
    }
}
