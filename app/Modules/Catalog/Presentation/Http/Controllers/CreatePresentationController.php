<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\Item;
use App\Modules\Catalog\Domain\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatePresentationController extends Controller
{
    public function __invoke(Request $request, Item $item): Response
    {
        abort_unless($request->user()->hasPermission('catalog.manage'), 403);
        $item->load('baseUnit');

        return Inertia::render('catalog/presentations/Create', [
            'item' => $item->only(['id', 'code', 'name', 'base_unit_id']),
            'units' => Unit::query()->where('is_active', true)->where('dimension', $item->baseUnit->dimension)->orderBy('scale_to_base')->get(['id', 'code', 'name']),
        ]);
    }
}
