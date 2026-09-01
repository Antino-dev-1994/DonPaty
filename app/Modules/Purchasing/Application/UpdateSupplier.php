<?php

namespace App\Modules\Purchasing\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\People\Application\SyncPersonClassifications;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use Illuminate\Support\Facades\DB;

class UpdateSupplier
{
    public function __construct(
        private readonly SyncPersonClassifications $syncClassifications,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(SupplierProfile $supplier, SupplierData $data): SupplierProfile
    {
        return DB::transaction(function () use ($supplier, $data): SupplierProfile {
            $before = $supplier->load('person')->toArray();
            $supplier->person->update([
                'name' => $data->name, 'document_type' => $data->documentType, 'document_number' => $data->documentNumber,
                'email' => $data->email, 'phone' => $data->phone, 'notes' => $data->notes, 'is_active' => $data->isActive,
            ]);
            $activeTypes = $supplier->person->classifications()->whereNull('effective_to')->pluck('classification')
                ->map(fn ($type) => $type instanceof PersonClassificationType ? $type->value : $type)->all();
            $this->syncClassifications->execute($supplier->person, array_values(array_unique([...$activeTypes, PersonClassificationType::Supplier->value])));
            $supplier->update([
                'trade_name' => $data->tradeName, 'tax_identifier' => $data->taxIdentifier,
                'default_payment_term_days' => $data->defaultPaymentTermDays, 'notes' => $data->notes, 'is_active' => $data->isActive,
            ]);
            $this->audit->execute('purchasing.supplier_updated', $supplier, before: $before, after: $supplier->fresh('person')->toArray());

            return $supplier;
        });
    }
}
