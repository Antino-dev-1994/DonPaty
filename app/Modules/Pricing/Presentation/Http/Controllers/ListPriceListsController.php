<?php

namespace App\Modules\Pricing\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pricing\Domain\Models\PriceList;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListPriceListsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('prices.manage'), 403);
        return Inertia::render('pricing/Index', ['priceLists' => PriceList::query()->withCount('items')->orderByDesc('is_default')->orderBy('name')->get()->map(fn ($list) => [...$list->only(['id','name','is_default','is_active']), 'type' => $list->type->value, 'type_label' => $list->type->label(), 'starts_at' => $list->starts_at?->format('Y-m-d'), 'ends_at' => $list->ends_at?->format('Y-m-d'), 'items_count' => $list->items_count])]);
    }
}
