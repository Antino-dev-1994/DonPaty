<?php

namespace App\Modules\Inventory\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use App\Modules\Inventory\Domain\Models\PackageConversion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackageConversionsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->hasPermission('packages.convert'), 403);

        return Inertia::render('inventory/packages/Index', [
            'packages' => ProductPresentation::query()
                ->with(['item:id,name', 'inventoryBalance', 'packageComponents.componentPresentation.inventoryBalance'])
                ->whereHas('packageComponents')->where('is_active', true)->get()->map(fn (ProductPresentation $package) => [
                    'id' => $package->id,
                    'name' => $package->item->name.' · '.$package->name,
                    'sku' => $package->sku,
                    'physical_quantity' => $package->inventoryBalance?->physical_quantity ?? '0.000000',
                    'components' => $package->packageComponents->map(fn ($component) => [
                        'name' => $component->componentPresentation->name,
                        'quantity' => $component->quantity,
                        'physical_quantity' => $component->componentPresentation->inventoryBalance?->physical_quantity ?? '0.000000',
                    ]),
                ]),
            'conversions' => PackageConversion::query()->with(['packagePresentation:id,name,sku', 'creator:id,name'])->latest()->limit(30)->get()->map(fn (PackageConversion $conversion) => [
                'id' => $conversion->id,
                'document_number' => $conversion->document_number,
                'type' => $conversion->conversion_type->label(),
                'package' => $conversion->packagePresentation->name,
                'quantity' => $conversion->package_quantity,
                'total_cost' => $conversion->total_cost,
                'creator' => $conversion->creator->name,
                'created_at' => $conversion->created_at->timezone(config('regional.display_timezone'))->format('Y-m-d H:i'),
            ]),
        ]);
    }
}
