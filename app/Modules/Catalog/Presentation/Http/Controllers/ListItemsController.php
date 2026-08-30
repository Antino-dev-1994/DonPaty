<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Enums\ItemType;
use App\Modules\Catalog\Domain\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListItemsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('catalog.view'), 403);
        $search = trim((string) $request->string('search'));
        $type = $request->string('type')->toString();

        return Inertia::render('catalog/items/Index', [
            'items' => Item::query()
                ->with('baseUnit:id,code')
                ->withCount('presentations')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
                }))
                ->when($type !== '', fn ($query) => $query->where('type', $type))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString()
                ->through(fn (Item $item) => [
                    ...$item->only(['id', 'code', 'name', 'minimum_stock', 'allow_negative_stock', 'is_active']),
                    'type' => $item->type->value,
                    'type_label' => $item->type->label(),
                    'base_unit' => $item->baseUnit->code,
                    'presentations_count' => $item->presentations_count,
                ]),
            'filters' => ['search' => $search, 'type' => $type],
            'types' => collect(ItemType::cases())->map(fn ($itemType) => ['value' => $itemType->value, 'label' => $itemType->label()]),
            'canManage' => $request->user()->hasPermission('catalog.manage'),
        ]);
    }
}
