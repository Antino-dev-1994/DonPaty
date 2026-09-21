<?php

namespace App\Modules\Finance\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;

final readonly class RegisterBusinessOpeningData
{
    public function __construct(
        public CarbonInterface $openedOn,
        public int $cashAmount,
        public int $nequiAmount,
        public ?string $notes,
        public User $opener,
    ) {}
}
