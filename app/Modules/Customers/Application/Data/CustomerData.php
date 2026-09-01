<?php

namespace App\Modules\Customers\Application\Data;

final readonly class CustomerData
{
    public function __construct(public string $name, public ?string $documentType, public ?string $documentNumber, public ?string $email, public ?string $phone, public ?string $defaultPriceListId, public int $creditLimit, public int $defaultPaymentTermDays, public ?string $deliveryNotes, public ?string $notes, public bool $isActive) {}
    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['document_type'] ?? null, $data['document_number'] ?? null, $data['email'] ?? null, $data['phone'] ?? null, $data['default_price_list_id'] ?? null, (int) $data['credit_limit'], (int) $data['default_payment_term_days'], $data['delivery_notes'] ?? null, $data['notes'] ?? null, (bool) $data['is_active']);
    }
}
