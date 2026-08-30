<?php

namespace App\Modules\Identity\Application\Data;

final readonly class UserData
{
    /** @param list<string> $roleIds */
    public function __construct(
        public string $personId,
        public string $name,
        public string $email,
        public ?string $password,
        public array $roleIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            personId: $data['person_id'],
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
            roleIds: array_values($data['roles']),
        );
    }
}
