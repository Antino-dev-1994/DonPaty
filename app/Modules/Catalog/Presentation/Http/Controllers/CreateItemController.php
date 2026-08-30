<?php

namespace App\Modules\Catalog\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Enums\ItemType;
use App\Modules\Catalog\Domain\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreateItemController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('catalog.manage'), 403);

        return Inertia::render('catalog/items/Create', $this->options());
    }

    private function options(): array
    {
        return [
            'units' => Unit::query()->where('is_active', true)->orderBy('dimension')->orderBy('name')->get(['id', 'code', 'name', 'dimension']),
            'types' => collect(ItemType::cases())->map(fn ($type) => ['value' => $type->value, 'label' => $type->label()]),
        ];
    }
}
