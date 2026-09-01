<?php

namespace App\Modules\Purchasing\Application\Data;

final readonly class SupplierData
{
    public function __construct(
        public string $name,
        public ?string $tradeName,
        public ?string $documentType,
        public ?string $documentNumber,
        public ?string $taxIdentifier,
        public ?string $email,
        public ?string $phone,
        public int $defaultPaymentTermDays,
        public ?string $notes,
        public bool $isActive,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'], tradeName: $data['trade_name'] ?? null,
            documentType: $data['document_type'] ?? null, documentNumber: $data['document_number'] ?? null,
            taxIdentifier: $data['tax_identifier'] ?? null, email: $data['email'] ?? null,
            phone: $data['phone'] ?? null, defaultPaymentTermDays: (int) $data['default_payment_term_days'],
            notes: $data['notes'] ?? null, isActive: (bool) $data['is_active'],
        );
    }
}
