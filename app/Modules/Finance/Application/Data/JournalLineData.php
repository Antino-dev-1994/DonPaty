<?php

namespace App\Modules\Finance\Application\Data;

final readonly class JournalLineData
{
    public function __construct(
        public string $accountId,
        public int $debit,
        public int $credit,
        public ?string $personId = null,
        public ?string $description = null,
    ) {}
}
