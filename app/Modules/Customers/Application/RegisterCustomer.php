<?php

namespace App\Modules\Customers\Application;

use App\Modules\Audit\Application\RecordAuditEvent;
use App\Modules\Customers\Application\Data\CustomerData;
use App\Modules\Customers\Domain\Models\CustomerProfile;
use App\Modules\People\Application\CreatePerson;
use App\Modules\People\Application\Data\PersonData;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use Illuminate\Support\Facades\DB;

class RegisterCustomer
{
    public function __construct(private readonly CreatePerson $createPerson, private readonly RecordAuditEvent $audit) {}
    public function execute(CustomerData $data): CustomerProfile
    {
        return DB::transaction(function () use ($data): CustomerProfile {
            $person = $this->createPerson->execute(new PersonData($data->name, $data->documentType, $data->documentNumber, $data->email, $data->phone, $data->notes, $data->isActive, [PersonClassificationType::Customer->value]));
            $customer = CustomerProfile::create(['person_id' => $person->id, 'default_price_list_id' => $data->defaultPriceListId, 'credit_limit' => $data->creditLimit, 'default_payment_term_days' => $data->defaultPaymentTermDays, 'delivery_notes' => $data->deliveryNotes, 'is_active' => $data->isActive]);
            $this->audit->execute('customers.created', $customer, after: $customer->load(['person', 'defaultPriceList'])->toArray());
            return $customer;
        });
    }
}
