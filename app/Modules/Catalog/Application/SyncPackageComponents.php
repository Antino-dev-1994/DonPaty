<?php

namespace App\Modules\Catalog\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Catalog\Domain\Models\ProductPresentation;
use DomainException;
use Illuminate\Support\Facades\DB;

class SyncPackageComponents
{
    public function __construct(private readonly RecordAuditEvent $audit) {}

    /** @param list<array{presentation_id: string, quantity: string}> $components */
    public function execute(ProductPresentation $package, array $components): void
    {
        DB::transaction(function () use ($package, $components): void {
            $before = $package->load('packageComponents')->toArray();
            $componentIds = collect($components)->pluck('presentation_id');
            $presentations = ProductPresentation::query()->whereIn('id', $componentIds)->get()->keyBy('id');

            foreach ($components as $component) {
                $presentation = $presentations->get($component['presentation_id']);
                if (! $presentation || $presentation->item_id !== $package->item_id || $presentation->is($package)) {
                    throw new DomainException('Cada componente debe ser otra presentación del mismo producto.');
                }
            }

            $package->packageComponents()->delete();
            foreach ($components as $component) {
                $package->packageComponents()->create([
                    'component_presentation_id' => $component['presentation_id'],
                    'quantity' => $component['quantity'],
                ]);
            }

            $package->refresh()->load('packageComponents');
            $this->audit->execute('catalog.package_components_updated', $package, before: $before, after: $package->toArray());
        });
    }
}
