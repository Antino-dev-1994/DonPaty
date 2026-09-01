<?php

namespace App\Modules\Purchasing\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\People\Application\CreatePerson;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\Purchasing\Application\Data\SupplierData;
use App\Modules\Purchasing\Domain\Models\SupplierProfile;
use Illuminate\Support\Facades\DB;

class RegisterSupplier
{
    public function __construct(
        private readonly CreatePerson $createPerson,
        private readonly RecordAuditEvent $audit,
    ) {}

    public function execute(SupplierData $data): SupplierProfile
    {
        return DB::transaction(function () use ($data): SupplierProfile {
            $person = $this->createPerson->execute(new PersonData(
                name: $data->name, documentType: $data->documentType, documentNumber: $data->documentNumber,
                email: $data->email, phone: $data->phone, notes: $data->notes,
                isActive: $data->isActive, classifications: [PersonClassificationType::Supplier->value],
            ));
            $supplier = SupplierProfile::create([
                'person_id' => $person->id, 'trade_name' => $data->tradeName,
                'tax_identifier' => $data->taxIdentifier, 'default_payment_term_days' => $data->defaultPaymentTermDays,
                'notes' => $data->notes, 'is_active' => $data->isActive,
            ]);
            $this->audit->execute('purchasing.supplier_created', $supplier, after: $supplier->load('person')->toArray());

            return $supplier;
        });
    }
}
