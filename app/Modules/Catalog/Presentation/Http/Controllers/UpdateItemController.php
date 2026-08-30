<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\Data\ItemData;
use App\Modules\Catalog\Application\UpdateItem;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Presentation\Http\Requests\SaveItemRequest;
use Illuminate\Http\RedirectResponse;

class UpdateItemController extends Controller
{
    public function __invoke(SaveItemRequest $request, Item $item, UpdateItem $action): RedirectResponse
    {
        $action->execute($item, ItemData::fromArray($request->validated()));

        return back()->with('success', 'Artículo actualizado correctamente.');
    }
}
