<?php

namespace App\Modules\Finance\Application\Data;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class JournalEntryData
{
    /** @param list<JournalLineData> $lines */
    public function __construct(
        public CarbonInterface $effectiveAt,
        public string $description,
        public User $poster,
        public array $lines,
        public ?Model $source = null,
    ) {}
}
