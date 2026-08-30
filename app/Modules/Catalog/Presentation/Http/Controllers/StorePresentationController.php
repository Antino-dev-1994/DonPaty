<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\CreatePresentation;
use App\Modules\Catalog\Application\Data\PresentationData;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Presentation\Http\Requests\SavePresentationRequest;
use Illuminate\Http\RedirectResponse;

class StorePresentationController extends Controller
{
    public function __invoke(SavePresentationRequest $request, Item $item, CreatePresentation $action): RedirectResponse
    {
        $presentation = $action->execute($item, PresentationData::fromArray($request->validated()));

        return to_route('catalog.presentations.edit', [$item, $presentation])->with('success', 'Presentación creada correctamente.');
    }
}
