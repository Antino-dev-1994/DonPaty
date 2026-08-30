<?php

namespace App\Modules\People\Application\Data;

final readonly class PersonData
{
    /** @param list<string> $classifications */
    public function __construct(
        public string $name,
        public ?string $documentType,
        public ?string $documentNumber,
        public ?string $email,
        public ?string $phone,
        public ?string $notes,
        public bool $isActive,
        public array $classifications,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            documentType: $data['document_type'] ?? null,
            documentNumber: $data['document_number'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            notes: $data['notes'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            classifications: array_values($data['classifications']),
        );
    }

    public function attributes(): array
    {
        return [
            'name' => $this->name,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
        ];
    }
}
