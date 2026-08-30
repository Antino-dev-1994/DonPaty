<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\CreateItem;
use App\Modules\Catalog\Application\Data\ItemData;
use App\Modules\Catalog\Presentation\Http\Requests\SaveItemRequest;
use Illuminate\Http\RedirectResponse;

class StoreItemController extends Controller
{
    public function __invoke(SaveItemRequest $request, CreateItem $action): RedirectResponse
    {
        $item = $action->execute(ItemData::fromArray($request->validated()));

        return to_route('catalog.items.edit', $item)->with('success', 'Artículo creado correctamente.');
    }
}
