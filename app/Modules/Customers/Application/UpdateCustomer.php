<?php

namespace App\Modules\Customers\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Customers\Application\Data\CustomerData;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\People\Application\SyncPersonClassifications;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use Illuminate\Support\Facades\DB;

class UpdateCustomer
{
    public function __construct(private readonly SyncPersonClassifications $classifications, private readonly RecordAuditEvent $audit) {}
    public function execute(CustomerProfile $customer, CustomerData $data): CustomerProfile
    {
        return DB::transaction(function () use ($customer, $data): CustomerProfile {
            $before = $customer->load('person')->toArray();
            $customer->person->update(['name' => $data->name, 'document_type' => $data->documentType, 'document_number' => $data->documentNumber, 'email' => $data->email, 'phone' => $data->phone, 'notes' => $data->notes, 'is_active' => $data->isActive]);
            $types = $customer->person->classifications()->whereNull('effective_to')->pluck('classification')->map(fn ($type) => $type instanceof PersonClassificationType ? $type->value : $type)->all();
            $this->classifications->execute($customer->person, array_values(array_unique([...$types, PersonClassificationType::Customer->value])));
            $customer->update(['default_price_list_id' => $data->defaultPriceListId, 'credit_limit' => $data->creditLimit, 'default_payment_term_days' => $data->defaultPaymentTermDays, 'delivery_notes' => $data->deliveryNotes, 'is_active' => $data->isActive]);
            $this->audit->execute('customers.updated', $customer, before: $before, after: $customer->fresh(['person', 'defaultPriceList'])->toArray());
            return $customer;
        });
    }
}
